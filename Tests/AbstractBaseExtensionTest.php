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

use Sonatra\Component\Block\BlockExtensionInterface;
use Sonatra\Component\Block\BlockTypeExtensionInterface;
use Sonatra\Component\Block\Tests\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractBaseExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockExtensionInterface
     */
    protected $extension;

    protected function setUp()
    {
        throw new \LogicException('The setUp() method must be overridden');
    }

    protected function tearDown()
    {
        $this->extension = null;
    }

    public function testHasType()
    {
        $this->assertTrue($this->extension->hasType(FooType::class));
        $this->assertFalse($this->extension->hasType('bar'));
    }

    public function testHasTypeExtension()
    {
        $this->assertTrue($this->extension->hasTypeExtensions(FooType::class));
        $this->assertFalse($this->extension->hasTypeExtensions('bar'));
    }

    public function testGetType()
    {
        $type = $this->extension->getType(FooType::class);

        $this->assertInstanceOf('Sonatra\Component\Block\BlockTypeInterface', $type);
        $this->assertEquals('foo', $type->getBlockPrefix());
    }

    /**
     * @expectedException \Sonatra\Component\Block\Exception\InvalidArgumentException
     */
    public function testGetUnexistingType()
    {
        $this->extension->getType('bar');
    }

    public function testGetTypeExtension()
    {
        $exts = $this->extension->getTypeExtensions(FooType::class);

        $this->assertInternalType('array', $exts);
        $this->assertCount(1, $exts);

        /* @var BlockTypeExtensionInterface $ext */
        $ext = $exts[0];
        $this->assertInstanceOf('Sonatra\Component\Block\BlockTypeExtensionInterface', $ext);
        $this->assertEquals(FooType::class, $ext->getExtendedType());
    }

    public function testGetGuess()
    {
        $guesser = $this->extension->getTypeGuesser();

        $this->assertInstanceOf('Sonatra\Component\Block\BlockTypeGuesserInterface', $guesser);
    }
}
