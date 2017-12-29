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

use Fxp\Component\Block\BlockBuilder;
use Fxp\Component\Block\BlockBuilderInterface;
use Fxp\Component\Block\BlockFactoryInterface;
use Fxp\Component\Block\DataMapperInterface;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\ResolvedBlockTypeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockBuilderTest extends TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var BlockFactoryInterface
     */
    protected $factory;

    /**
     * @var BlockBuilderInterface
     */
    protected $builder;

    protected function setUp()
    {
        /* @var EventDispatcherInterface $dispatcher */
        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        /* @var BlockFactoryInterface $factory */
        $this->factory = $this->getMockBuilder('Fxp\Component\Block\BlockFactoryInterface')->getMock();

        $this->builder = new BlockBuilder('name', null, $this->dispatcher, $this->factory);
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->builder = null;
    }

    /**
     * Changing the name is not allowed, otherwise the name and property path
     * are not synchronized anymore.
     *
     * @see BlockType::buildBlock
     */
    public function testNoSetName()
    {
        $this->assertFalse(method_exists($this->builder, 'setName'));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testAddNameNoStringAndNoInteger()
    {
        $this->builder->add(true);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testAddTypeNoString()
    {
        $this->builder->add('foo', 1234);
    }

    public function testAddWithGuessFluent()
    {
        $this->builder = new BlockBuilder('name', 'stdClass', $this->dispatcher, $this->factory);
        $builder = $this->builder->add('foo');
        $this->assertSame($builder, $this->builder);
    }

    public function testAddIsFluent()
    {
        $builder = $this->builder->add('foo', TextType::class, ['bar' => 'baz']);
        $this->assertSame($builder, $this->builder);
    }

    public function testAdd()
    {
        $this->assertFalse($this->builder->has('foo'));
        $this->builder->add('foo', TextType::class);
        $this->assertTrue($this->builder->has('foo'));
    }

    public function testAll()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->factory;

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('foo', TextType::class)
            ->will($this->returnValue(new BlockBuilder('foo', null, $this->dispatcher, $this->factory)));

        $this->assertCount(0, $this->builder->all());
        $this->assertFalse($this->builder->has('foo'));

        $this->builder->add('foo', TextType::class);
        $children = $this->builder->all();

        $this->assertTrue($this->builder->has('foo'));
        $this->assertCount(1, $children);
        $this->assertArrayHasKey('foo', $children);
    }

    public function testMaintainOrderOfLazyAndExplicitChildren()
    {
        $this->builder->add('foo', TextType::class);
        $this->builder->add($this->getBlockBuilder('bar'));
        $this->builder->add('baz', TextType::class);

        $children = $this->builder->all();

        $this->assertSame(['foo', 'bar', 'baz'], array_keys($children));
    }

    public function testAddFormType()
    {
        $this->assertFalse($this->builder->has('foo'));
        $this->builder->add('foo', $this->getMockBuilder('Fxp\Component\Block\BlockTypeInterface')->getMock());
        $this->assertTrue($this->builder->has('foo'));
    }

    public function testRemove()
    {
        $this->builder->add('foo', TextType::class);
        $this->builder->remove('foo');
        $this->assertFalse($this->builder->has('foo'));
    }

    public function testRemoveUnknown()
    {
        $this->builder->remove('foo');
        $this->assertFalse($this->builder->has('foo'));
    }

    public function testRemoveAndGetForm()
    {
        $this->builder->add('foo', TextType::class);
        $this->builder->remove('foo');
        $block = $this->builder->getBlock();
        $this->assertInstanceOf('Fxp\Component\Block\Block', $block);
    }

    public function testCreateNoTypeNo()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->factory;

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('foo', TextType::class, null, [])
        ;

        $this->builder->create('foo');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidArgumentException
     * @expectedExceptionMessage The child with the name "foo" does not exist.
     */
    public function testGetUnknown()
    {
        $this->builder->get('foo');
    }

    public function testGetExplicitType()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->factory;
        $expectedType = TextType::class;
        $expectedName = 'foo';
        $expectedOptions = ['bar' => 'baz'];

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with($expectedName, $expectedType, null, $expectedOptions)
            ->will($this->returnValue($this->getBlockBuilder()));

        $this->builder->add($expectedName, $expectedType, $expectedOptions);
        $builder = $this->builder->get($expectedName);

        $this->assertNotSame($builder, $this->builder);
        $this->assertSame($builder, $this->builder->get($expectedName));
    }

    public function testGetGuessedType()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->factory;
        $expectedName = 'foo';
        $expectedOptions = ['bar' => 'baz'];

        $factory->expects($this->once())
            ->method('createBuilderForProperty')
            ->with('stdClass', $expectedName, null, $expectedOptions)
            ->will($this->returnValue($this->getBlockBuilder()));

        $this->builder = new BlockBuilder('name', 'stdClass', $this->dispatcher, $this->factory);
        $this->builder->add($expectedName, null, $expectedOptions);
        $builder = $this->builder->get($expectedName);

        $this->assertNotSame($builder, $this->builder);
        $this->assertSame($builder, $this->builder->get($expectedName));
    }

    public function testGetFormConfigErasesReferences()
    {
        $builder = new BlockBuilder('name', null, $this->dispatcher, $this->factory);
        $builder->add(new BlockBuilder('child', null, $this->dispatcher, $this->factory));

        $config = $builder->getBlockConfig();
        $reflClass = new \ReflectionClass($config);
        $children = $reflClass->getProperty('children');
        $unresolvedChildren = $reflClass->getProperty('unresolvedChildren');

        $children->setAccessible(true);
        $unresolvedChildren->setAccessible(true);

        $this->assertEmpty($children->getValue($config));
        $this->assertEmpty($unresolvedChildren->getValue($config));
    }

    public function testAddChildWithoutName()
    {
        $this->builder->add(null, TextType::class);
        $this->assertEquals(1, $this->builder->count());
        $keyBlocks = array_keys($this->builder->all());
        $this->assertNotNull($keyBlocks[0]);
    }

    public function testGetBlockFactory()
    {
        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryInterface', $this->builder->getBlockFactory());
    }

    public function testGetIterator()
    {
        /* @var BlockBuilder $builder */
        $builder = $this->builder;
        $this->assertInstanceOf('ArrayIterator', $builder->getIterator());
    }

    public function testGetCount()
    {
        $this->assertEquals(0, $this->builder->count());
    }

    public function testGetBlock()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getMockBuilder('Fxp\Component\Block\DataMapperInterface')->getMock();
        /* @var ResolvedBlockTypeInterface $blockType */
        $blockType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();

        $builder = new BlockBuilder('name', null, $this->dispatcher, $this->factory);
        $child = new BlockBuilder('child', null, $this->dispatcher, $this->factory);

        $builder->setCompound(true);
        $builder->setDataMapper($dataMapper);
        $builder->setType($blockType);
        $child->setType($blockType);

        $builder->add($child);
        $block = $builder->getBlock();

        $this->assertInstanceOf('Fxp\Component\Block\Block', $block);
        $this->assertEquals(1, $block->count());
    }

    public function testGetBlockWithAutoInitialize()
    {
        /* @var BlockBuilder $builder */
        $builder = $this->builder;
        $builder->setAutoInitialize(true);
        $block = $builder->getBlock();

        $this->assertTrue($block->getConfig()->getAutoInitialize());
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testAddTypeAfterGetBlock()
    {
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->add('foo', TextType::class);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testCreateTypeAfterGetBlock()
    {
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->create('foo', TextType::class);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testGetTypeAfterGetBlock()
    {
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->get('foo');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testRemoveTypeAfterGetBlock()
    {
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->remove('foo');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testHasTypeAfterGetBlock()
    {
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->has('foo');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testAllTypeAfterGetBlock()
    {
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->all();
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testCountTypeAfterGetBlock()
    {
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->count();
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testGetBlockTypeAfterGetBlock()
    {
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->getBlock();
    }

    /**
     * @€@expectedException \Fxp\Component\Block\Exception\BadMethodCallException
     */
    public function testGetIteratorTypeAfterGetBlock()
    {
        /* @var BlockBuilder $config */
        $config = $this->builder->getBlockConfig();
        $config->getIterator();
    }

    private function getBlockBuilder($name = 'name')
    {
        $mock = $this->getMockBuilder('Fxp\Component\Block\BlockBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        return $mock;
    }
}
