<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license inBlockation, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests;

use Fxp\Component\Block\BlockRegistry;
use Fxp\Component\Block\BlockTypeGuesserChain;
use Fxp\Component\Block\BlockTypeGuesserInterface;
use Fxp\Component\Block\ResolvedBlockTypeFactoryInterface;
use Fxp\Component\Block\Tests\Fixtures\Extension\FooTypeBarExtension;
use Fxp\Component\Block\Tests\Fixtures\Extension\FooTypeBazExtension;
use Fxp\Component\Block\Tests\Fixtures\TestCustomExtension;
use Fxp\Component\Block\Tests\Fixtures\Type\FooSubType;
use Fxp\Component\Block\Tests\Fixtures\Type\FooType;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockRegistryTest extends TestCase
{
    /**
     * @var BlockRegistry
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resolvedTypeFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser2;

    /**
     * @var TestCustomExtension
     */
    private $extension1;

    /**
     * @var TestCustomExtension
     */
    private $extension2;

    protected function setUp()
    {
        $this->resolvedTypeFactory = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeFactory')->getMock();
        $this->guesser1 = $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock();
        $this->guesser2 = $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock();

        /* @var ResolvedBlockTypeFactoryInterface $rtf */
        $rtf = $this->resolvedTypeFactory;
        /* @var BlockTypeGuesserInterface $guesser1 */
        $guesser1 = $this->guesser1;
        /* @var BlockTypeGuesserInterface $guesser2 */
        $guesser2 = $this->guesser2;

        $this->extension1 = new TestCustomExtension($guesser1);
        $this->extension2 = new TestCustomExtension($guesser2);
        $this->registry = new BlockRegistry(array(
            $this->extension1,
            $this->extension2,
        ), $rtf);
    }

    public function testGetTypeFromExtension()
    {
        $type = new FooType();
        $resolvedType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();

        $this->extension2->addType($type);

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type)
            ->will($this->returnValue($resolvedType));

        $resolvedType = $this->registry->getType(FooType::class);

        $this->assertSame($resolvedType, $this->registry->getType(FooType::class));
    }

    public function testGetTypeWithTypeExtensions()
    {
        $type = new FooType();
        $ext1 = new FooTypeBarExtension();
        $ext2 = new FooTypeBazExtension();
        $resolvedType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();

        $this->extension2->addType($type);
        $this->extension1->addTypeExtension($ext1);
        $this->extension2->addTypeExtension($ext2);

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type, array($ext1, $ext2))
            ->will($this->returnValue($resolvedType));

        $this->assertSame($resolvedType, $this->registry->getType(FooType::class));
    }

    public function testGetTypeConnectsParent()
    {
        $parentType = new FooType();
        $type = new FooSubType();
        $parentResolvedType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
        $resolvedType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();

        $this->extension1->addType($parentType);
        $this->extension2->addType($type);

        $this->resolvedTypeFactory->expects($this->at(0))
            ->method('createResolvedType')
            ->with($parentType)
            ->will($this->returnValue($parentResolvedType));

        $this->resolvedTypeFactory->expects($this->at(1))
            ->method('createResolvedType')
            ->with($type, array(), $parentResolvedType)
            ->will($this->returnValue($resolvedType));

        $this->assertSame($resolvedType, $this->registry->getType(FooSubType::class));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testGetTypeThrowsExceptionIfParentNotFound()
    {
        $type = new FooSubType();

        $this->extension1->addType($type);

        $this->registry->getType($type);
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidArgumentException
     */
    public function testGetTypeThrowsExceptionIfTypeNotFound()
    {
        $this->registry->getType('bar');
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testGetTypeThrowsExceptionIfNoString()
    {
        $this->registry->getType(array());
    }

    public function testHasTypeAfterLoadingFromExtension()
    {
        $type = new FooType();
        $resolvedType = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type)
            ->will($this->returnValue($resolvedType));

        $this->assertFalse($this->registry->hasType('foo'));

        $this->extension2->addType($type);

        $this->assertTrue($this->registry->hasType(FooType::class));
        $this->assertTrue($this->registry->hasType(FooType::class));
    }

    public function testGetTypeGuesser()
    {
        $expectedGuesser = new BlockTypeGuesserChain(array($this->guesser1, $this->guesser2));

        $this->assertEquals($expectedGuesser, $this->registry->getTypeGuesser());

        /* @var ResolvedBlockTypeFactoryInterface $rtf */
        $rtf = $this->resolvedTypeFactory;

        $registry = new BlockRegistry(
            array($this->getMockBuilder('Fxp\Component\Block\BlockExtensionInterface')->getMock()),
            $rtf);

        $this->assertNull($registry->getTypeGuesser());
    }

    public function testGetExtensions()
    {
        $expectedExtensions = array($this->extension1, $this->extension2);

        $this->assertEquals($expectedExtensions, $this->registry->getExtensions());
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testInvalidExtensions()
    {
        /* @var ResolvedBlockTypeFactoryInterface $rtf */
        $rtf = $this->resolvedTypeFactory;

        new BlockRegistry(array(42), $rtf);
    }
}
