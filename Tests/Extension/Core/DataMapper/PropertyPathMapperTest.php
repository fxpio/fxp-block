<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Extension\Core\DataMapper;

use Sonatra\Component\Block\Block;
use Sonatra\Component\Block\BlockConfigBuilder;
use Sonatra\Component\Block\BlockConfigInterface;
use Sonatra\Component\Block\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PropertyPathMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyPathMapper
     */
    private $mapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $propertyAccessor;

    protected function setUp()
    {
        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        $this->propertyAccessor = $this->getMockBuilder('Symfony\Component\PropertyAccess\PropertyAccessorInterface')->getMock();

        /* @var PropertyAccessorInterface $propertyAccessor */
        $propertyAccessor = $this->propertyAccessor;

        $this->mapper = new PropertyPathMapper($propertyAccessor);
    }

    protected function tearDown()
    {
        $this->mapper = null;
        $this->dispatcher = null;
        $this->propertyAccessor = null;
    }

    /**
     * @param $path
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPropertyPath($path)
    {
        return $this->getMockBuilder('Symfony\Component\PropertyAccess\PropertyPath')
            ->setConstructorArgs(array($path))
            ->setMethods(array('getValue', 'setValue'))
            ->getMock();
    }

    /**
     * @param BlockConfigInterface $config
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getBlock(BlockConfigInterface $config)
    {
        $block = $this->getMockBuilder('Sonatra\Component\Block\Block')
            ->setConstructorArgs(array($config))
            ->setMethods(null)
            ->getMock();

        return $block;
    }

    public function testMapDataToViewsPassesObject()
    {
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        $car = new \stdClass();
        $engine = new \stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->any())
            ->method('getValue')
            ->with($car, $propertyPath)
            ->will($this->returnValue($engine));

        $config = new BlockConfigBuilder('name', '\stdClass', $dispatcher);
        $config->setPropertyPath($propertyPath);
        $block = $this->getBlock($config);

        $this->mapper->mapDataToViews($car, array($block));

        /* @var Block $block */
        $this->assertSame($engine, $block->getData());
    }

    public function testMapDataToViewsIgnoresEmptyPropertyPath()
    {
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        $car = new \stdClass();

        $config = new BlockConfigBuilder(null, '\stdClass', $dispatcher);
        $block = $this->getBlock($config);

        /* @var Block $block */
        $this->assertNull($block->getPropertyPath());

        $this->mapper->mapDataToViews($car, array($block));

        $this->assertNull($block->getData());
    }

    public function testMapDataToViewsIgnoresUnmapped()
    {
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        $car = new \stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('getValue');

        $config = new BlockConfigBuilder('name', '\stdClass', $dispatcher);
        $config->setMapped(false);
        $config->setPropertyPath($propertyPath);
        $block = $this->getBlock($config);

        $this->mapper->mapDataToViews($car, array($block));

        /* @var Block $block */
        $this->assertNull($block->getData());
    }

    public function testMapDataToViewsSetsDefaultDataIfPassedDataIsNull()
    {
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        $default = new \stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('getValue');

        $config = new BlockConfigBuilder('name', '\stdClass', $dispatcher);
        $config->setPropertyPath($propertyPath);
        $config->setData($default);

        $block = $this->getMockBuilder('Sonatra\Component\Block\Block')
            ->setConstructorArgs(array($config))
            ->setMethods(array('setData'))
            ->getMock();

        $block->expects($this->once())
            ->method('setData')
            ->with($default);

        $this->mapper->mapDataToViews(null, array($block));
    }

    public function testMapDataToViewsSetsDefaultDataIfPassedDataIsEmptyArray()
    {
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        $default = new \stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('getValue');

        $config = new BlockConfigBuilder('name', '\stdClass', $dispatcher);
        $config->setPropertyPath($propertyPath);
        $config->setData($default);

        $block = $this->getMockBuilder('Sonatra\Component\Block\Block')
            ->setConstructorArgs(array($config))
            ->setMethods(array('setData'))
            ->getMock();

        $block->expects($this->once())
            ->method('setData')
            ->with($default);

        $this->mapper->mapDataToViews(array(), array($block));
    }

    public function testMapDataToViewsSetsDefaultDataIfPassedDataIsString()
    {
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        $default = new \stdClass();
        $propertyPath = $this->getPropertyPath('engine');

        $this->propertyAccessor->expects($this->never())
            ->method('getValue');

        $config = new BlockConfigBuilder('name', '\stdClass', $dispatcher);
        $config->setPropertyPath($propertyPath);
        $config->setData($default);

        $block = $this->getMockBuilder('Sonatra\Component\Block\Block')
            ->setConstructorArgs(array($config))
            ->setMethods(null)
            ->getMock();

        $this->mapper->mapDataToViews('DATA', array($block));
    }
}
