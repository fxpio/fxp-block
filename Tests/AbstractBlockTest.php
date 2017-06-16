<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Block\BlockBuilder;
use Sonatra\Component\Block\BlockFactoryInterface;
use Sonatra\Component\Block\BlockInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractBlockTest extends TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var BlockFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var BlockInterface
     */
    protected $block;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->factory = $this->getMockBuilder('Sonatra\Component\Block\BlockFactoryInterface')->getMock();
        $this->block = $this->createBlock();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->block = null;
    }

    /**
     * @return BlockInterface
     */
    abstract protected function createBlock();

    /**
     * @param string                   $name
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $dataClass
     * @param array                    $options
     *
     * @return BlockBuilder
     */
    protected function getBuilder($name = 'name', EventDispatcherInterface $dispatcher = null, $dataClass = null, array $options = array())
    {
        return new BlockBuilder($name, $dataClass, $dispatcher ?: $this->dispatcher, $this->factory, $options);
    }

    /**
     * @param string $name
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockBlock($name = 'name')
    {
        $block = $this->getMockBuilder('Sonatra\Component\Block\BlockInterface')->getMock();
        $config = $this->getMockBuilder('Sonatra\Component\Block\BlockConfigInterface')->getMock();

        $block->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));
        $block->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($config));

        return $block;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDataMapper()
    {
        return $this->getMockBuilder('Sonatra\Component\Block\DataMapperInterface')->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDataTransformer()
    {
        return $this->getMockBuilder('Sonatra\Component\Block\DataTransformerInterface')->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBlockValidator()
    {
        return $this->getMockBuilder('Sonatra\Component\Block\BlockValidatorInterface')->getMock();
    }
}
