<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Extension\Core\EventListener;

use Fxp\Component\Block\BlockBuilder;
use Fxp\Component\Block\BlockEvent;
use Fxp\Component\Block\BlockEvents;
use Fxp\Component\Block\BlockFactoryInterface;
use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\DataMapperInterface;
use Fxp\Component\Block\Extension\Core\EventListener\ResizeBlockListener;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\ResolvedBlockTypeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ResizeBlockListenerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;
    /**
     * @var BlockInterface
     */
    protected $block;

    protected function setUp()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();

        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        $this->factory = $this->getMockBuilder('Fxp\Component\Block\BlockFactoryInterface')->getMock();
        $this->block = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->getBlock();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->block = null;
    }

    protected function getBuilder($name = 'name')
    {
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        /* @var BlockFactoryInterface $factory */
        $factory = $this->factory;
        /* @var ResolvedBlockTypeInterface $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $builder = new BlockBuilder($name, null, $dispatcher, $factory);
        $builder->setType($type);

        return $builder;
    }

    protected function getBlock($name = 'name')
    {
        return $this->getBuilder($name)->getBlock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDataMapper()
    {
        return $this->getMockBuilder('Fxp\Component\Block\DataMapperInterface')->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockBlock()
    {
        return $this->getMockBuilder('Fxp\Component\Block\Test\BlockInterface')->getMock();
    }

    public function testGetSubscriber()
    {
        $listener = new ResizeBlockListener(TextType::class, array());

        $this->assertEquals(array(BlockEvents::PRE_SET_DATA => 'preSetData'), $listener->getSubscribedEvents());
    }

    public function testPreSetDataResizesBlock()
    {
        $this->block->add($this->getBlock('0'));
        $this->block->add($this->getBlock('1'));

        $this->factory->expects($this->at(0))
            ->method('createNamed')
            ->with(1, TextType::class, null, array('property_path' => '[1]', 'auto_initialize' => false))
            ->will($this->returnValue($this->getBlock('1')));
        $this->factory->expects($this->at(1))
            ->method('createNamed')
            ->with(2, TextType::class, null, array('property_path' => '[2]', 'auto_initialize' => false))
            ->will($this->returnValue($this->getBlock('2')));

        $data = array(1 => 'string', 2 => 'string');
        $event = new BlockEvent($this->block, $data);
        $listener = new ResizeBlockListener(TextType::class, array());
        $listener->preSetData($event);

        $this->assertFalse($this->block->has('0'));
        $this->assertTrue($this->block->has('1'));
        $this->assertTrue($this->block->has('2'));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testPreSetDataRequiresArrayOrTraversable()
    {
        $data = 'no array or traversable';
        $event = new BlockEvent($this->block, $data);
        $listener = new ResizeBlockListener(TextType::class, array());
        $listener->preSetData($event);
    }

    public function testPreSetDataDealsWithNullData()
    {
        $this->factory->expects($this->never())->method('createNamed');

        $data = null;
        $event = new BlockEvent($this->block, $data);
        $listener = new ResizeBlockListener(TextType::class, array());
        $listener->preSetData($event);
    }
}
