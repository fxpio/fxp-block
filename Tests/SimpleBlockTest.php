<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests;

use Fxp\Component\Block\Block;
use Fxp\Component\Block\BlockConfigBuilder;
use Fxp\Component\Block\BlockEvent;
use Fxp\Component\Block\BlockEvents;
use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockView;
use Fxp\Component\Block\DataMapperInterface;
use Fxp\Component\Block\ResolvedBlockTypeInterface;
use Fxp\Component\Block\Tests\Fixtures\DataTransformer\FixedDataTransformer;
use Fxp\Component\Block\Tests\Fixtures\DataTransformer\FixedFilterListener;
use Fxp\Component\Block\Tests\Fixtures\Object\SimpleBlockTestCountable;
use Fxp\Component\Block\Tests\Fixtures\Object\SimpleBlockTestTraversable;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SimpleBlockTest extends AbstractBlockTest
{
    protected function createBlock()
    {
        return $this->getBuilder()->getBlock();
    }

    public function testDataIsInitializedToConfiguredValue()
    {
        $model = new FixedDataTransformer(array(
            'default' => 'foo',
        ));
        $view = new FixedDataTransformer(array(
            'foo' => 'bar',
        ));

        $config = new BlockConfigBuilder('name', null, $this->dispatcher);
        $config->addViewTransformer($view);
        $config->addModelTransformer($model);
        $config->setData('default');
        $block = new Block($config);

        $this->assertSame('default', $block->getData());
        $this->assertSame('foo', $block->getNormData());
        $this->assertSame('bar', $block->getViewData());
    }

    public function testDataIsInitializedFromGetData()
    {
        $mock = $this->getMockBuilder('\stdClass')
            ->setMethods(array('preSetData', 'postSetData'))
            ->getMock();
        $mock->expects($this->at(0))
            ->method('preSetData');
        $mock->expects($this->at(0))
            ->method('postSetData');

        $config = new BlockConfigBuilder('name', null, $this->dispatcher);
        $config->addEventListener(BlockEvents::PRE_SET_DATA, array($mock, 'preSetData'));
        $config->addEventListener(BlockEvents::POST_SET_DATA, array($mock, 'postSetData'));
        $block = new Block($config);

        // no call to setData() or similar where the object would be
        // initialized otherwise

        $this->assertNull($block->getData());
        $this->assertNull($block->getNormData());
        $this->assertSame('', $block->getViewData());
    }

    public function testGetRootReturnsRootOfParent()
    {
        $parent = $this->getMockBlock();
        $parent->expects($this->once())
            ->method('getRoot')
            ->will($this->returnValue('ROOT'));

        /* @var BlockInterface $parent */
        $this->block->setParent($parent);

        $this->assertEquals('ROOT', $this->block->getRoot());
    }

    public function testGetRootReturnsSelfIfNoParent()
    {
        $this->assertSame($this->block, $this->block->getRoot());
    }

    public function testEmptyIfEmptyArray()
    {
        $this->block->setData(array());

        $this->assertTrue($this->block->isEmpty());
    }

    public function testEmptyIfEmptyCountable()
    {
        $this->block = new Block(new BlockConfigBuilder('name', 'Fxp\Component\Block\Tests\Fixtures\Object\SimpleBlockTestCountable', $this->dispatcher));

        $this->block->setData(new SimpleBlockTestCountable(0));

        $this->assertTrue($this->block->isEmpty());
    }

    public function testNotEmptyIfFilledCountable()
    {
        $this->block = new Block(new BlockConfigBuilder('name', 'Fxp\Component\Block\Tests\Fixtures\Object\SimpleBlockTestCountable', $this->dispatcher));

        $this->block->setData(new SimpleBlockTestCountable(1));

        $this->assertFalse($this->block->isEmpty());
    }

    public function testEmptyIfEmptyTraversable()
    {
        $this->block = new Block(new BlockConfigBuilder('name', 'Fxp\Component\Block\Tests\Fixtures\Object\SimpleBlockTestTraversable', $this->dispatcher));

        $this->block->setData(new SimpleBlockTestTraversable(0));

        $this->assertTrue($this->block->isEmpty());
    }

    public function testNotEmptyIfFilledTraversable()
    {
        $this->block = new Block(new BlockConfigBuilder('name', 'Fxp\Component\Block\Tests\Fixtures\Object\SimpleBlockTestTraversable', $this->dispatcher));

        $this->block->setData(new SimpleBlockTestTraversable(1));

        $this->assertFalse($this->block->isEmpty());
    }

    public function testEmptyIfNull()
    {
        $this->block->setData(null);

        $this->assertTrue($this->block->isEmpty());
    }

    public function testEmptyIfEmptyString()
    {
        $this->block->setData('');

        $this->assertTrue($this->block->isEmpty());
    }

    public function testNotEmptyIfText()
    {
        $this->block->setData('foobar');

        $this->assertFalse($this->block->isEmpty());
    }

    public function testSetDataDoesNotCloneObject()
    {
        $data = new \stdClass();
        $block = $this->getBuilder('name', null, '\stdClass')->getBlock();
        $block->setData($data);

        $this->assertSame($data, $block->getData());
    }

    public function testSetDataExecutesTransformationChain()
    {
        // use real event dispatcher now
        $block = $this->getBuilder('name', new EventDispatcher())
            ->addEventSubscriber(new FixedFilterListener(array(
                'preSetData' => array(
                    'app' => 'filtered',
                ),
            )))
            ->addModelTransformer(new FixedDataTransformer(array(
                '' => '',
                'filtered' => 'norm',
            )))
            ->addViewTransformer(new FixedDataTransformer(array(
                '' => '',
                'norm' => 'client',
            )))
            ->getBlock();

        $block->setData('app');

        $this->assertEquals('filtered', $block->getData());
        $this->assertEquals('norm', $block->getNormData());
        $this->assertEquals('client', $block->getViewData());
    }

    public function testSetDataExecutesViewTransformersInOrder()
    {
        $block = $this->getBuilder()
            ->addViewTransformer(new FixedDataTransformer(array(
                '' => '',
                'first' => 'second',
            )))
            ->addViewTransformer(new FixedDataTransformer(array(
                '' => '',
                'second' => 'third',
            )))
            ->getBlock();

        $block->setData('first');

        $this->assertEquals('third', $block->getViewData());
    }

    public function testSetDataExecutesModelTransformersInReverseOrder()
    {
        $block = $this->getBuilder()
            ->addModelTransformer(new FixedDataTransformer(array(
                '' => '',
                'second' => 'third',
            )))
            ->addModelTransformer(new FixedDataTransformer(array(
                '' => '',
                'first' => 'second',
            )))
            ->getBlock();

        $block->setData('first');

        $this->assertEquals('third', $block->getNormData());
    }

    public function testSetDataConvertsScalarToStringIfNoTransformer()
    {
        $block = $this->getBuilder()->getBlock();

        $block->setData(1);

        $this->assertSame('1', $block->getData());
        $this->assertSame('1', $block->getNormData());
        $this->assertSame('1', $block->getViewData());
    }

    public function testSetDataConvertsScalarToStringIfOnlyModelTransformer()
    {
        $block = $this->getBuilder()
            ->addModelTransformer(new FixedDataTransformer(array(
                '' => '',
                1 => 23,
            )))
            ->getBlock();

        $block->setData(1);

        $this->assertSame(1, $block->getData());
        $this->assertSame(23, $block->getNormData());
        $this->assertSame('23', $block->getViewData());
    }

    public function testSetDataConvertsNullToStringIfNoTransformer()
    {
        $block = $this->getBuilder()->setCompound(false)->getBlock();

        $block->setData(null);

        $this->assertNull($block->getData());
        $this->assertNull($block->getNormData());
        $this->assertSame('', $block->getViewData());
    }

    public function testEmptyDataFromClosure()
    {
        $test = $this;
        $block = $this->getBuilder()
            ->setEmptyData(function ($block) use ($test) {
                // the block instance is passed to the closure to allow use
                // of block data when creating the empty value
                $test->assertInstanceOf('Fxp\Component\Block\BlockInterface', $block);

                return 'foo';
            })
            ->addViewTransformer(new FixedDataTransformer(array(
                '' => '',
                'foo' => 'bar',
            )))
            ->getBlock();

        $this->assertEquals('bar', $block->getViewData());
    }

    public function testEmptyMessage()
    {
        $block = $this->getBuilder()
            ->setEmptyMessage('empty message')
            ->getBlock();

        $this->assertEquals('empty message', $block->getViewData());
    }

    public function testGetNormDataForInitializeBlock()
    {
        $block = $this->getBuilder()->getBlock();

        $this->assertNull($block->getNormData());
    }

    public function testGetViewDataForInitializeBlock()
    {
        $block = $this->getBuilder()->getBlock();

        $this->assertEquals('', $block->getViewData());
    }

    public function testCreateView()
    {
        /* @var ResolvedBlockTypeInterface $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $view = $this->getMockBuilder('Fxp\Component\Block\BlockView')->getMock();
        $block = $this->getBuilder()->setType($type)->getBlock();

        /* @var \PHPUnit_Framework_MockObject_MockObject $type */
        $type->expects($this->once())
            ->method('createView')
            ->with($block)
            ->will($this->returnValue($view));

        $this->assertSame($view, $block->createView());
    }

    public function testCreateViewWithParent()
    {
        /* @var ResolvedBlockTypeInterface $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $view = $this->getMockBuilder('Fxp\Component\Block\BlockView')->getMock();
        /* @var BlockInterface $parentBlock */
        $parentBlock = $this->getMockBuilder('Fxp\Component\Block\BlockInterface')->getMock();
        $parentView = $this->getMockBuilder('Fxp\Component\Block\BlockView')->getMock();
        $block = $this->getBuilder()->setType($type)->getBlock();
        $block->setParent($parentBlock);

        /* @var \PHPUnit_Framework_MockObject_MockObject $parentBlock */
        $parentBlock->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($parentView));

        /* @var \PHPUnit_Framework_MockObject_MockObject $type */
        $type->expects($this->once())
            ->method('createView')
            ->with($block, $parentView)
            ->will($this->returnValue($view));

        $this->assertSame($view, $block->createView());
    }

    public function testCreateViewWithExplicitParent()
    {
        /* @var ResolvedBlockTypeInterface $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $view = $this->getMockBuilder('Fxp\Component\Block\BlockView')->getMock();
        /* @var BlockView $parentView */
        $parentView = $this->getMockBuilder('Fxp\Component\Block\BlockView')->getMock();
        $block = $this->getBuilder()->setType($type)->getBlock();

        /* @var \PHPUnit_Framework_MockObject_MockObject $type */
        $type->expects($this->once())
            ->method('createView')
            ->with($block, $parentView)
            ->will($this->returnValue($view));

        $this->assertSame($view, $block->createView($parentView));
    }

    public function testBlockCanHaveEmptyName()
    {
        $block = $this->getBuilder('')->getBlock();

        $this->assertEquals('', $block->getName());
    }

    public function testSetNullParentWorksWithEmptyName()
    {
        $block = $this->getBuilder('')->getBlock();
        $block->setParent(null);

        $this->assertNull($block->getParent());
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\LogicException
     * @expectedExceptionMessage A block with an empty name cannot have a parent block.
     */
    public function testBlockCannotHaveEmptyNameNotInRootLevel()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();

        $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->add($this->getBuilder(''))
            ->getBlock();
    }

    public function testGetPropertyPathReturnsConfiguredPath()
    {
        $block = $this->getBuilder()->setPropertyPath('address.street')->getBlock();

        $this->assertEquals(new PropertyPath('address.street'), $block->getPropertyPath());
    }

    public function testGetPropertyPathDefaultsToNameIfParentHasDataClass()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();
        $parent = $this->getBuilder(null, null, 'stdClass')
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->getBlock();
        $block = $this->getBuilder('name')->getBlock();
        $parent->add($block);

        $this->assertEquals(new PropertyPath('name'), $block->getPropertyPath());
    }

    public function testGetPropertyPathDefaultsToIndexedNameIfParentDataClassIsNull()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();
        $parent = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->getBlock();
        $block = $this->getBuilder('name')->getBlock();
        $parent->add($block);

        $this->assertEquals(new PropertyPath('[name]'), $block->getPropertyPath());
    }

    public function testGetPropertyPathDefaultsToNameIfFirstParentWithoutInheritDataHasDataClass()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();
        $grandParent = $this->getBuilder(null, null, 'stdClass')
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->getBlock();
        $parent = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->setInheritData(true)
            ->getBlock();
        $block = $this->getBuilder('name')->getBlock();
        $grandParent->add($parent);
        $parent->add($block);

        $this->assertEquals(new PropertyPath('name'), $block->getPropertyPath());
    }

    public function testGetPropertyPathDefaultsToIndexedNameIfDataClassOfFirstParentWithoutInheritDataIsNull()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();
        $grandParent = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->getBlock();
        $parent = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->setInheritData(true)
            ->getBlock();
        $block = $this->getBuilder('name')->getBlock();
        $grandParent->add($parent);
        $parent->add($block);

        $this->assertEquals(new PropertyPath('[name]'), $block->getPropertyPath());
    }

    public function testGetPropertyPathDefaultsIfNameIsEmpty()
    {
        $block = $this->getBuilder(null)->getBlock();

        $this->assertNull($block->getName());
        $this->assertNull($block->getPropertyPath());
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\LogicException
     */
    public function testViewDataMustNotBeObjectIfDataClassIsNull()
    {
        $config = new BlockConfigBuilder('name', null, $this->dispatcher);
        $config->addViewTransformer(new FixedDataTransformer(array(
            '' => '',
            'foo' => new \stdClass(),
        )));
        $block = new Block($config);

        $block->setData('foo');
    }

    public function testViewDataMayBeArrayAccessIfDataClassIsNull()
    {
        $arrayAccess = $this->getMockBuilder('\ArrayAccess')->getMock();
        $config = new BlockConfigBuilder('name', null, $this->dispatcher);
        $config->addViewTransformer(new FixedDataTransformer(array(
            '' => '',
            'foo' => $arrayAccess,
        )));
        $block = new Block($config);

        $block->setData('foo');

        $this->assertSame($arrayAccess, $block->getViewData());
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\LogicException
     */
    public function testViewDataMustBeObjectIfDataClassIsSet()
    {
        $config = new BlockConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->addViewTransformer(new FixedDataTransformer(array(
            '' => '',
            'foo' => array('bar' => 'baz'),
        )));
        $block = new Block($config);

        $block->setData('foo');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\RuntimeException
     */
    public function testSetDataCannotInvokeItself()
    {
        // Cycle detection to prevent endless loops
        $config = new BlockConfigBuilder('name', 'stdClass', $this->dispatcher);
        $config->addEventListener(BlockEvents::PRE_SET_DATA, function (BlockEvent $event) {
            $event->getBlock()->setData('bar');
        });
        $block = new Block($config);

        $block->setData('foo');
    }

    public function testBlockInheritsParentData()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();
        $child = $this->getBuilder('child')
            ->setInheritData(true);

        /* @var BlockInterface $parent */
        $parent = $this->getBuilder('parent')
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->setData('foo')
            ->addModelTransformer(new FixedDataTransformer(array(
                'foo' => 'norm[foo]',
            )))
            ->addViewTransformer(new FixedDataTransformer(array(
                'norm[foo]' => 'view[foo]',
            )))
            ->add($child)
            ->getBlock();

        $this->assertSame('foo', $parent->get('child')->getData());
        $this->assertSame('norm[foo]', $parent->get('child')->getNormData());
        $this->assertSame('view[foo]', $parent->get('child')->getViewData());
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\RuntimeException
     */
    public function testInheritDataDisallowsSetData()
    {
        $block = $this->getBuilder()
            ->setInheritData(true)
            ->getBlock();

        $block->setData('foo');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\RuntimeException
     */
    public function testGetDataRequiresParentToBeSetIfInheritData()
    {
        $block = $this->getBuilder()
            ->setInheritData(true)
            ->getBlock();

        $block->getData();
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\RuntimeException
     */
    public function testGetNormDataRequiresParentToBeSetIfInheritData()
    {
        $block = $this->getBuilder()
            ->setInheritData(true)
            ->getBlock();

        $block->getNormData();
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\RuntimeException
     */
    public function testGetViewDataRequiresParentToBeSetIfInheritData()
    {
        $block = $this->getBuilder()
            ->setInheritData(true)
            ->getBlock();

        $block->getViewData();
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\LogicException
     */
    public function testCreateCompoundBlockWithoutDataMapper()
    {
        $this->getBuilder()
            ->setCompound(true)
            ->getBlock();
    }

    public function testGettersAndSetters()
    {
        $block = $this->getBuilder()->getBlock();

        $this->assertFalse($block->hasAttribute('foo'));
        $this->assertSame($block, $block->setAttribute('foo', 'bar'));
        $this->assertTrue($block->hasAttribute('foo'));
        $this->assertEquals(array('foo' => 'bar'), $block->getAttributes());
        $this->assertSame($block, $block->setAttributes(array('bar' => 'foo')));
        $this->assertTrue($block->hasAttribute('foo'));
        $this->assertTrue($block->hasAttribute('bar'));
        $this->assertEquals(array('bar' => 'foo', 'foo' => 'bar'), $block->getAttributes());
        $this->assertEquals('foo', $block->getAttribute('bar'));
        $this->assertEquals('bar', $block->getAttribute('foo', 'bar'));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidArgumentException
     */
    public function testGetInvalidChild()
    {
        $block = $this->getBuilder()->getBlock();
        $block->get('foo');
    }

    public function testSetOptionsWithoutType()
    {
        $block = $this->getBuilder()->getBlock();

        $this->assertFalse($block->hasOption('foo'));
        $this->assertSame($block, $block->setOption('foo', 'bar'));
        $this->assertTrue($block->hasOption('foo'));
        $this->assertEquals(array('foo' => 'bar'), $block->getOptions());
        $this->assertSame($block, $block->setOptions(array('bar' => 'foo')));
        $this->assertTrue($block->hasOption('foo'));
        $this->assertTrue($block->hasOption('bar'));
        $this->assertEquals(array('bar' => 'foo', 'foo' => 'bar'), $block->getOptions());
        $this->assertEquals('foo', $block->getOption('bar'));
        $this->assertEquals('bar', $block->getOption('foo', 'baz'));
        $this->assertEquals('baz', $block->getOption('foobar', 'baz'));
    }

    public function testSetOptionsWithType()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->any())
            ->method('resolve')
            ->will($this->returnValue(array(
                'foo' => 'bar',
            )));

        $type = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $type->expects($this->any())
            ->method('getOptionsResolver')
            ->will($this->returnValue($resolver));

        /* @var ResolvedBlockTypeInterface $type */
        $builder = $this->getBuilder();
        $builder->setType($type);
        $block = $builder->getBlock();

        $this->assertFalse($block->hasOption('foo'));
        $this->assertSame($block, $block->setOption('foo', 'baz'));
        $this->assertTrue($block->hasOption('foo'));
        $this->assertEquals(array('foo' => 'baz'), $block->getOptions());
        $this->assertSame($block, $block->setOptions(array('bar' => 'foo')));
        $this->assertTrue($block->hasOption('foo'));
        $this->assertFalse($block->hasOption('bar'));
        $this->assertEquals(array('foo' => 'baz'), $block->getOptions());
        $this->assertNull($block->getOption('bar'));
        $this->assertEquals('foo', $block->getOption('bar', 'foo'));
        $this->assertEquals('baz', $block->getOption('foobar', 'baz'));
        $this->assertSame($block, $block->setDataClass('\stdClass'));
        $this->assertEquals('\stdClass', $block->getDataClass());
    }

    public function testSetDataWithForm()
    {
        /* @var FormInterface $form */
        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')->getMock();

        $builder = $this->getBuilder();
        $builder->setForm($form);
        $block = $builder->getBlock();

        $this->assertSame($form, $block->getForm());
        $this->assertSame($block, $block->setData('data'));
    }
}
