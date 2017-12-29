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

use Fxp\Component\Block\BlockFactory;
use Fxp\Component\Block\BlockFactoryInterface;
use Fxp\Component\Block\BlockRegistryInterface;
use Fxp\Component\Block\BlockTypeGuesserChain;
use Fxp\Component\Block\Extension\Core\Type\PasswordType;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\Guess\Guess;
use Fxp\Component\Block\Guess\TypeGuess;
use Fxp\Component\Block\ResolvedBlockTypeFactoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockFactoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser2;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resolvedTypeFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var BlockFactoryInterface
     */
    protected $factory;

    protected function setUp()
    {
        $this->resolvedTypeFactory = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeFactoryInterface')->getMock();
        $this->guesser1 = $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock();
        $this->guesser2 = $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock();
        $this->registry = $this->getMockBuilder('Fxp\Component\Block\BlockRegistryInterface')->getMock();
        $this->builder = $this->getMockBuilder('Fxp\Component\Block\Test\BlockBuilderInterface')->getMock();

        /* @var BlockRegistryInterface $registry */
        $registry = $this->registry;
        /* @var ResolvedBlockTypeFactoryInterface $resolvedTypeFactory */
        $resolvedTypeFactory = $this->resolvedTypeFactory;

        $this->factory = new BlockFactory($registry, $resolvedTypeFactory);

        $this->registry->expects($this->any())
            ->method('getTypeGuesser')
            ->will($this->returnValue(new BlockTypeGuesserChain([
                $this->guesser1,
                $this->guesser2,
            ])));
    }

    protected function tearDown()
    {
        $this->factory = null;
    }

    public function testCreateNamedBuilderWithTypeName()
    {
        $options = ['a' => '1', 'b' => '2'];
        $expectedOptions = array_merge($options, ['id' => 'name']);
        $resolvedOptions = ['a' => '2', 'b' => '3'];
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', 'type', null, $options));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "string",
     */
    public function testCreateNamedBuilderWithResolvedTypeInstance()
    {
        $options = ['a' => '1', 'b' => '2'];
        $resolvedType = $this->getMockResolvedType();

        $this->factory->createNamedBuilder('name', $resolvedType, null, $options);
    }

    public function testCreateNamedBuilderFillsDataOption()
    {
        $givenOptions = ['a' => '1', 'b' => '2'];
        $expectedOptions = array_merge($givenOptions, ['data' => 'DATA', 'id' => 'name']);
        $resolvedOptions = ['a' => '2', 'b' => '3', 'data' => 'DATA'];
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', 'type', 'DATA', $givenOptions));
    }

    public function testCreateNamedBuilderDoesNotOverrideExistingDataOption()
    {
        $options = ['a' => '1', 'b' => '2', 'data' => 'CUSTOM'];
        $expectedOptions = array_merge($options, ['id' => 'name']);
        $resolvedOptions = ['a' => '2', 'b' => '3', 'data' => 'CUSTOM'];
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', 'type', 'DATA', $options));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "string", "stdClass" given
     */
    public function testCreateNamedBuilderThrowsUnderstandableException()
    {
        $this->factory->createNamedBuilder('name', new \stdClass());
    }

    public function testCreateUsesTypeNameIfTypeGivenAsString()
    {
        $options = ['a' => '1', 'b' => '2'];
        $resolvedOptions = ['a' => '2', 'b' => '3'];
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('TYPE')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->builder->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue('BLOCK'));

        $this->assertSame('BLOCK', $this->factory->create('TYPE', null, $options));
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     * \@expectedExceptionMessage Expected argument of type "string",
     */
    public function testCreateUsesTypeNameIfTypeGivenAsObject()
    {
        $options = ['a' => '1', 'b' => '2'];
        $resolvedType = $this->getMockResolvedType();

        $this->assertSame('BLOCK', $this->factory->create($resolvedType, null, $options));
    }

    public function testCreateNamed()
    {
        $options = ['a' => '1', 'b' => '2'];
        $expectedOptions = array_merge($options, ['id' => 'name']);
        $resolvedOptions = ['a' => '2', 'b' => '3'];
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->builder->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue('BLOCK'));

        $this->assertSame('BLOCK', $this->factory->createNamed('name', 'type', null, $options));
    }

    public function testCreateBuilderForPropertyWithoutTypeGuesser()
    {
        $registry = $this->getMockBuilder('Fxp\Component\Block\BlockRegistryInterface')->getMock();
        $factory = $this->getMockBuilder('Fxp\Component\Block\BlockFactory')
            ->setMethods(['createNamedBuilder'])
            ->setConstructorArgs([$registry, $this->resolvedTypeFactory])
            ->getMock();

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', TextType::class, null, [])
            ->will($this->returnValue('builderInstance'));

        /* @var BlockFactoryInterface $factory */
        $this->builder = $factory->createBuilderForProperty('Application\Author', 'firstName');

        $this->assertEquals('builderInstance', $this->builder);
    }

    public function testCreateBuilderForPropertyCreatesBlockWithHighestConfidence()
    {
        $this->guesser1->expects($this->once())
            ->method('guessType')
            ->with('Application\Author', 'firstName')
            ->will($this->returnValue(new TypeGuess(
                        TextType::class,
                        ['attr' => ['data-maxlength' => 10]],
                        Guess::MEDIUM_CONFIDENCE
                    )));

        $this->guesser2->expects($this->once())
            ->method('guessType')
            ->with('Application\Author', 'firstName')
            ->will($this->returnValue(new TypeGuess(
                        PasswordType::class,
                        ['attr' => ['data-maxlength' => 7]],
                        Guess::HIGH_CONFIDENCE
                    )));

        $factory = $this->getMockFactory(['createNamedBuilder']);

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', PasswordType::class, null, ['attr' => ['data-maxlength' => 7]])
            ->will($this->returnValue('builderInstance'));

        /* @var BlockFactoryInterface $factory */
        $this->builder = $factory->createBuilderForProperty('Application\Author', 'firstName');

        $this->assertEquals('builderInstance', $this->builder);
    }

    public function testCreateBuilderCreatesTextBlockIfNoGuess()
    {
        $this->guesser1->expects($this->once())
            ->method('guessType')
            ->with('Application\Author', 'firstName')
            ->will($this->returnValue(null));

        $factory = $this->getMockFactory(['createNamedBuilder']);

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', TextType::class)
            ->will($this->returnValue('builderInstance'));

        /* @var BlockFactoryInterface $factory */
        $this->builder = $factory->createBuilderForProperty('Application\Author', 'firstName');

        $this->assertEquals('builderInstance', $this->builder);
    }

    public function testOptionsCanBeOverridden()
    {
        $this->guesser1->expects($this->once())
            ->method('guessType')
            ->with('Application\Author', 'firstName')
            ->will($this->returnValue(new TypeGuess(
                'Fxp\Component\Block\Extension\Core\Type\TextType',
                ['attr' => ['data-maxlength' => 10]],
                Guess::MEDIUM_CONFIDENCE
            )));

        $factory = $this->getMockFactory(['createNamedBuilder']);

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', TextType::class, null, ['attr' => ['data-maxlength' => 11]])
            ->will($this->returnValue('builderInstance'));

        /* @var BlockFactoryInterface $factory */
        $this->builder = $factory->createBuilderForProperty(
            'Application\Author',
            'firstName',
            null,
            ['attr' => ['data-maxlength' => 11]]
        );

        $this->assertEquals('builderInstance', $this->builder);
    }

    public function testCreateForPropertyWithoutTypeGuesser()
    {
        $expectedOptions = ['id' => 'firstName'];
        $resolvedOptions = ['a' => '2', 'b' => '3'];
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('Fxp\Component\Block\Extension\Core\Type\TextType')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'firstName', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->builder->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue('BLOCK'));

        $this->assertSame('BLOCK', $this->factory->createForProperty('Application\Author', 'firstName'));
    }

    private function getMockResolvedType()
    {
        return $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeInterface')->getMock();
    }

    private function getMockFactory(array $methods = [])
    {
        return $this->getMockBuilder('Fxp\Component\Block\BlockFactory')
            ->setMethods($methods)
            ->setConstructorArgs([$this->registry, $this->resolvedTypeFactory])
            ->getMock();
    }
}
