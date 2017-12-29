<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Util;

use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\Tests\Fixtures\Type\FooType;
use Fxp\Component\Block\Util\BlockUtil;
use PHPUnit\Framework\TestCase;

/**
 * Block Util Test.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockUtilTest extends TestCase
{
    public function testIsEmpty()
    {
        $this->assertTrue(BlockUtil::isEmpty(null));
        $this->assertTrue(BlockUtil::isEmpty(''));
        $this->assertFalse(BlockUtil::isEmpty('foobar'));
    }

    public function testCreateUniqueName()
    {
        $name = BlockUtil::createUniqueName();
        $this->assertEquals(0, strpos($name, 'block'));

        $id = substr($name, 5);
        $this->assertTrue(strlen($id) >= 5);
    }

    public function testCreateBlockId()
    {
        $parentBlock = $this->getMockBuilder('Fxp\Component\Block\BlockInterface')->getMock();
        $parentBlock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $block = $this->getMockBuilder('Fxp\Component\Block\BlockInterface')->getMock();
        $block->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('bar'));
        $block->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue($parentBlock));
        $block->expects($this->any())
            ->method('getOption')
            ->with($this->equalTo('chained_block'))
            ->will($this->returnValue(true));

        /* @var BlockInterface $block */
        $this->assertEquals('foo_bar', BlockUtil::createBlockId($block));
    }

    public function testIsValidBlock()
    {
        $parentType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $parentType->expects($this->any())
            ->method('getInnerType')
            ->will($this->returnValue(new FooType()));

        $blockInnerType = $this->getMockBuilder('Fxp\Component\Block\BlockTypeInterface')->getMock();

        $blockType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $blockType->expects($this->any())
            ->method('getInnerType')
            ->will($this->returnValue($blockInnerType));
        $blockType->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue($parentType));

        $blockConfig = $this->getMockBuilder('Fxp\Component\Block\BlockBuilderInterface')->getMock();
        $blockConfig->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($blockType));

        $block = $this->getMockBuilder('Fxp\Component\Block\BlockInterface')->getMock();
        $block->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($blockConfig));

        /* @var BlockInterface $block */
        $this->assertTrue(BlockUtil::isBlockType($block, FooType::class));
        $this->assertTrue(BlockUtil::isBlockType($block, get_class($blockInnerType)));
        $this->assertTrue(BlockUtil::isBlockType($block, [FooType::class, get_class($blockInnerType)]));
        $this->assertTrue(BlockUtil::isBlockType($block, [FooType::class, 'Baz']));
        $this->assertFalse(BlockUtil::isBlockType($block, 'Baz'));
        $this->assertFalse(BlockUtil::isBlockType($block, ['Baz', 'Boo!']));
    }
}
