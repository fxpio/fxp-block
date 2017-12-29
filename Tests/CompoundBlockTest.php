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

use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockView;
use Fxp\Component\Block\DataMapperInterface;
use Fxp\Component\Block\Extension\Core\DataMapper\PropertyPathMapper;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\ResolvedBlockTypeInterface;
use Fxp\Component\Block\Tests\Fixtures\DataTransformer\FixedDataTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CompoundBlockTest extends AbstractBlockTest
{
    protected function createBlock()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();

        return $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->getBlock();
    }

    public function testCloneChildren()
    {
        $child = $this->getBuilder('child')->getBlock();
        $this->block->add($child);

        $clone = clone $this->block;

        $this->assertNotSame($this->block, $clone);
        $this->assertNotSame($child, $clone['child']);
        $this->assertNotSame($this->block['child'], $clone['child']);
    }

    public function testNotEmptyIfChildNotEmpty()
    {
        $child = $this->getMockBlock();
        $child->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue(false));
        $child->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($this->block));

        $this->block->setData(null);
        $this->block->add($child);

        $this->assertFalse($this->block->isEmpty());
    }

    public function testAdd()
    {
        $child = $this->getBuilder('foo')->getBlock();
        $this->block->add($child);

        $this->assertTrue($this->block->has('foo'));
        $this->assertSame($this->block, $child->getParent());
        $this->assertSame(array('foo' => $child), $this->block->all());
    }

    public function testAddUsingType()
    {
        $child = $this->getBuilder('foo')->getBlock();

        $this->factory->expects($this->once())
            ->method('create')
            ->with(TextType::class, null, array(
                'bar' => 'baz',
                'auto_initialize' => false,
            ))
            ->will($this->returnValue($child));

        $this->block->add(null, TextType::class, array('bar' => 'baz'));

        $this->assertTrue($this->block->has('foo'));
        $this->assertSame($this->block, $child->getParent());
        $this->assertSame(array('foo' => $child), $this->block->all());
    }

    public function testAddUsingNameAndType()
    {
        $child = $this->getBuilder('foo')->getBlock();

        $this->factory->expects($this->once())
            ->method('createNamed')
            ->with('foo', TextType::class, null, array(
                'bar' => 'baz',
                'auto_initialize' => false,
            ))
            ->will($this->returnValue($child));

        $this->block->add('foo', TextType::class, array('bar' => 'baz'));

        $this->assertTrue($this->block->has('foo'));
        $this->assertSame($this->block, $child->getParent());
        $this->assertSame(array('foo' => $child), $this->block->all());
    }

    public function testAddUsingIntegerNameAndType()
    {
        $child = $this->getBuilder(0)->getBlock();

        $this->factory->expects($this->once())
            ->method('createNamed')
            ->with('0', TextType::class, null, array(
                'bar' => 'baz',
                'auto_initialize' => false,
            ))
            ->will($this->returnValue($child));

        // in order to make casting unnecessary
        $this->block->add(0, TextType::class, array('bar' => 'baz'));

        $this->assertTrue($this->block->has(0));
        $this->assertTrue($child->hasParent());
        $this->assertSame($this->block, $child->getParent());
        $this->assertFalse($child->isRoot());
        $this->assertTrue($this->block->isRoot());
        $this->assertSame(array(0 => $child), $this->block->all());
    }

    public function testAddUsingNameButNoType()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();
        $this->block = $this->getBuilder('name', null, '\stdClass')
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->getBlock();

        $child = $this->getBuilder('foo')->getBlock();

        $this->factory->expects($this->once())
            ->method('createForProperty')
            ->with('\stdClass', 'foo')
            ->will($this->returnValue($child));

        $this->block->add('foo');

        $this->assertTrue($this->block->has('foo'));
        $this->assertSame($this->block, $child->getParent());
        $this->assertSame(array('foo' => $child), $this->block->all());
    }

    public function testAddUsingNameButNoTypeAndOptions()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();
        $this->block = $this->getBuilder('name', null, '\stdClass')
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->getBlock();

        $child = $this->getBuilder('foo')->getBlock();

        $this->factory->expects($this->once())
            ->method('createForProperty')
            ->with('\stdClass', 'foo', null, array(
                'bar' => 'baz',
                'auto_initialize' => false,
            ))
            ->will($this->returnValue($child));

        $this->block->add('foo', null, array('bar' => 'baz'));

        $this->assertTrue($this->block->has('foo'));
        $this->assertSame($this->block, $child->getParent());
        $this->assertSame(array('foo' => $child), $this->block->all());
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\LogicException
     */
    public function testAddWithoutCompound()
    {
        $builder = $this->getBuilder();
        $builder->setCompound(false);
        $block = $builder->getBlock();
        $block->add('foo');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testAddInvalidChild()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();
        $builder = $this->getBuilder();
        $builder->setCompound(true);
        $builder->setDataMapper($dataMapper);
        $block = $builder->getBlock();
        $block->add(array());
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testAddInvalidChildType()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();
        $builder = $this->getBuilder();
        $builder->setCompound(true);
        $builder->setDataMapper($dataMapper);
        $block = $builder->getBlock();
        $block->add('foo', array());
    }

    public function testRemove()
    {
        $child = $this->getBuilder('foo')->getBlock();
        $this->block->add($child);
        $this->block->remove('foo');

        $this->assertNull($child->getParent());
        $this->assertCount(0, $this->block);
    }

    public function testRemoveIgnoresUnknownName()
    {
        $this->assertInstanceOf(BlockInterface::class, $this->block->remove('notexisting'));
    }

    public function testArrayAccess()
    {
        $child = $this->getBuilder('foo')->getBlock();

        $this->block[] = $child;

        $this->assertTrue(isset($this->block['foo']));
        $this->assertSame($child, $this->block['foo']);

        unset($this->block['foo']);

        $this->assertFalse(isset($this->block['foo']));
    }

    public function testCountable()
    {
        $this->block->add($this->getBuilder('foo')->getBlock());
        $this->block->add($this->getBuilder('bar')->getBlock());

        $this->assertCount(2, $this->block);
    }

    public function testIterator()
    {
        $this->block->add($this->getBuilder('foo')->getBlock());
        $this->block->add($this->getBuilder('bar')->getBlock());

        $this->assertSame($this->block->all(), iterator_to_array($this->block));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\RuntimeException
     */
    public function testInitializeBlockWithParent()
    {
        $child = $this->getBuilder('foo')->getBlock();
        $this->block->add($child);

        $child->initialize();
    }

    public function testAddMapsViewDataToViewIfInitialized()
    {
        $test = $this;
        /* @var DataMapperInterface $mapper */
        $mapper = $this->getDataMapper();
        /* @var BlockInterface $block */
        $block = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($mapper)
            ->addViewTransformer(new FixedDataTransformer(array(
                '' => '',
                'foo' => 'bar',
            )))
            ->setData('foo')
            ->getBlock();

        $child = $this->getBuilder()->getBlock();
        /* @var \PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper->expects($this->once())
            ->method('mapDataToViews')
            ->with('bar', $this->isInstanceOf('\RecursiveIteratorIterator'))
            ->will($this->returnCallback(function ($data, \RecursiveIteratorIterator $iterator) use ($child, $test) {
                $test->assertInstanceOf('Fxp\Component\Block\Util\InheritDataAwareIterator', $iterator->getInnerIterator());
                $test->assertSame(array($child), iterator_to_array($iterator));
            }));

        $block->initialize();
        $block->add($child);
    }

    public function testAddDoesNotMapViewDataToViewIfNotInitialized()
    {
        /* @var DataMapperInterface $mapper */
        $mapper = $this->getDataMapper();
        $block = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($mapper)
            ->getBlock();

        $child = $this->getBuilder()->getBlock();
        /* @var \PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper->expects($this->never())
            ->method('mapDataToViews');

        $block->add($child);
    }

    public function testAddDoesNotMapViewDataToBlockIfInheritData()
    {
        /* @var DataMapperInterface $mapper */
        $mapper = $this->getDataMapper();
        $block = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($mapper)
            ->setInheritData(true)
            ->getBlock();

        $child = $this->getBuilder()->getBlock();
        /* @var \PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper->expects($this->never())
            ->method('mapDataToViews');

        $block->initialize();
        $block->add($child);
    }

    public function testSetDataSupportsDynamicAdditionAndRemovalOfChildren()
    {
        $block = $this->getBuilder()
            ->setCompound(true)
            // We test using PropertyPathMapper on purpose. The traversal logic
            // is currently contained in InheritDataAwareIterator, but even
            // if that changes, this test should still function.
            ->setDataMapper(new PropertyPathMapper())
            ->getBlock();

        $child = $this->getMockBlock('child');
        $childToBeRemoved = $this->getMockBlock('removed');
        $childToBeAdded = $this->getMockBlock('added');

        $child->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($block));
        $childToBeRemoved->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($block));
        $childToBeAdded->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($block));

        $block->add($child);
        $block->add($childToBeRemoved);

        $child->expects($this->once())
            ->method('setData')
            ->will($this->returnCallback(function () use ($block, $childToBeAdded) {
                $block->remove('removed');
                $block->add($childToBeAdded);
            }));

        $childToBeRemoved->expects($this->never())
            ->method('setData');

        // once when it it is created, once when it is added
        $childToBeAdded->expects($this->exactly(2))
            ->method('setData');

        // pass NULL to all children
        $block->setData(array());
    }

    public function testSetDataMapsViewDataToChildren()
    {
        $test = $this;
        $mapper = $this->getDataMapper();
        $block = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($mapper)
            ->addViewTransformer(new FixedDataTransformer(array(
                    '' => '',
                    'foo' => 'bar',
                )))
            ->getBlock();

        $block->add($child1 = $this->getBuilder('firstName')->getBlock());
        $block->add($child2 = $this->getBuilder('lastName')->getBlock());

        $mapper->expects($this->once())
            ->method('mapDataToViews')
            ->with('bar', $this->isInstanceOf('\RecursiveIteratorIterator'))
            ->will($this->returnCallback(function ($data, \RecursiveIteratorIterator $iterator) use ($child1, $child2, $test) {
                $test->assertInstanceOf('Fxp\Component\Block\Util\InheritDataAwareIterator', $iterator->getInnerIterator());
                $test->assertSame(array('firstName' => $child1, 'lastName' => $child2), iterator_to_array($iterator));
            }));

        $block->setData('foo');
    }

    /**
     * Basic cases are covered in SimpleBlockTest.
     */
    public function testCreateViewWithChildren()
    {
        /* @var ResolvedBlockTypeInterface|\PHPUnit_Framework_MockObject_MockObject $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $options = array('a' => 'Foo', 'b' => 'Bar');
        $field1 = $this->getMockBlock('foo');
        $field2 = $this->getMockBlock('bar');
        $view = new BlockView();
        $field1View = new BlockView();
        $field2View = new BlockView();

        $this->block = $this->getBuilder('block', null, null, $options)
            ->setCompound(true)
            ->setDataMapper($this->getDataMapper())
            ->setType($type)
            ->getBlock();
        $field1->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($this->block));
        $field2->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($this->block));
        $this->block->add($field1);
        $this->block->add($field2);

        $test = $this;

        $assertChildViewsEqual = function (array $childViews) use ($test) {
            return function (BlockView $view) use ($test, $childViews) {
                /* @var TestCase $test */
                $test->assertSame($childViews, $view->children);
            };
        };

        // First create the view
        $type->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($view));

        // Then build it for the block itself
        $type->expects($this->once())
            ->method('buildView')
            ->with($view, $this->block, $options)
            ->will($this->returnCallback($assertChildViewsEqual(array())));

        // Then add the first child form
        $field1->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($field1View));

        // Then the second child form
        $field2->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($field2View));

        // Again build the view for the form itself. This time the child views
        // exist.
        $type->expects($this->once())
            ->method('finishView')
            ->with($view, $this->block, $options)
            ->will($this->returnCallback($assertChildViewsEqual(array('foo' => $field1View, 'bar' => $field2View))));

        $this->assertSame($view, $this->block->createView());
    }
}
