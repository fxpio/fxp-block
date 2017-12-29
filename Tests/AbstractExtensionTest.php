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

use Fxp\Component\Block\BlockExtensionInterface;
use Fxp\Component\Block\Tests\Fixtures\TestExpectedExtension;
use Fxp\Component\Block\Tests\Fixtures\TestExtension;
use Fxp\Component\Block\Tests\Fixtures\Type\FooType;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AbstractExtensionTest extends TestCase
{
    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidArgumentException
     */
    public function testGetUnexistingType()
    {
        /* @var BlockExtensionInterface $ext */
        $ext = $this->getMockForAbstractClass('Fxp\Component\Block\AbstractExtension');
        $ext->getType('unexisting_type');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testInitLoadTypeException()
    {
        $ext = new TestExpectedExtension();
        $ext->getType('unexisting_type');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testInitLoadTypeExtensionException()
    {
        $ext = new TestExpectedExtension();
        $ext->getTypeExtensions('unexisting_type');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testInitLoadTypeGuesserException()
    {
        $ext = new TestExpectedExtension();
        $ext->getTypeGuesser();
    }

    public function testGetEmptyTypeExtension()
    {
        /* @var BlockExtensionInterface $ext */
        $ext = $this->getMockForAbstractClass('Fxp\Component\Block\AbstractExtension');
        $typeExts = $ext->getTypeExtensions('unexisting_type_extension');

        $this->assertInternalType('array', $typeExts);
        $this->assertCount(0, $typeExts);
    }

    public function testGetType()
    {
        $ext = new TestExtension();
        $type = $ext->getType(FooType::class);

        $this->assertInstanceOf('Fxp\Component\Block\BlockTypeInterface', $type);
    }

    public function testHasType()
    {
        $ext = new TestExtension();

        $this->assertTrue($ext->hasType(FooType::class));
    }

    public function testGetTypeExtensions()
    {
        $ext = new TestExtension();
        $typeExts = $ext->getTypeExtensions(FooType::class);

        $this->assertInternalType('array', $typeExts);
        $this->assertCount(1, $typeExts);
        $this->assertInstanceOf('Fxp\Component\Block\BlockTypeExtensionInterface', $typeExts[0]);
    }

    public function testHasTypeExtensions()
    {
        $ext = new TestExtension();

        $this->assertTrue($ext->hasTypeExtensions(FooType::class));
    }

    public function testGetTypeGuesser()
    {
        $ext = new TestExtension();
        $guesser = $ext->getTypeGuesser();

        $this->assertNull($guesser);
    }
}
