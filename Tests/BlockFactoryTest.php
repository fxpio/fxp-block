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
use Sonatra\Component\Block\BlockFactory;
use Sonatra\Component\Block\BlockFactoryInterface;
use Sonatra\Component\Block\BlockRegistryInterface;
use Sonatra\Component\Block\BlockTypeGuesserChain;
use Sonatra\Component\Block\Extension\Core\Type\PasswordType;
use Sonatra\Component\Block\Extension\Core\Type\TextType;
use Sonatra\Component\Block\Guess\Guess;
use Sonatra\Component\Block\Guess\TypeGuess;
use Sonatra\Component\Block\ResolvedBlockTypeFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
        $this->resolvedTypeFactory = $this->getMockBuilder('Sonatra\Component\Block\ResolvedBlockTypeFactoryInterface')->getMock();
        $this->guesser1 = $this->getMockBuilder('Sonatra\Component\Block\BlockTypeGuesserInterface')->getMock();
        $this->guesser2 = $this->getMockBuilder('Sonatra\Component\Block\BlockTypeGuesserInterface')->getMock();
        $this->registry = $this->getMockBuilder('Sonatra\Component\Block\BlockRegistryInterface')->getMock();
        $this->builder = $this->getMockBuilder('Sonatra\Component\Block\Test\BlockBuilderInterface')->getMock();

        /* @var BlockRegistryInterface $registry */
        $registry = $this->registry;
        /* @var ResolvedBlockTypeFactoryInterface $resolvedTypeFactory */
        $resolvedTypeFactory = $this->resolvedTypeFactory;

        $this->factory = new BlockFactory($registry, $resolvedTypeFactory);

        $this->registry->expects($this->any())
            ->method('getTypeGuesser')
            ->will($this->returnValue(new BlockTypeGuesserChain(array(
                $this->guesser1,
                $this->guesser2,
            ))));
    }

    protected function tearDown()
    {
        $this->factory = null;
    }

    public function testCreateNamedBuilderWithTypeName()
    {
        $options = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3');
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
     * @expectedException \Sonatra\Component\Block\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "string",
     */
    public function testCreateNamedBuilderWithResolvedTypeInstance()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedType = $this->getMockResolvedType();

        $this->factory->createNamedBuilder('name', $resolvedType, null, $options);
    }

    public function testCreateNamedBuilderFillsDataOption()
    {
        $givenOptions = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($givenOptions, array('data' => 'DATA', 'id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3', 'data' => 'DATA');
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
        $options = array('a' => '1', 'b' => '2', 'data' => 'CUSTOM');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3', 'data' => 'CUSTOM');
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
     * @expectedException \Sonatra\Component\Block\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "string", "stdClass" given
     */
    public function testCreateNamedBuilderThrowsUnderstandableException()
    {
        $this->factory->createNamedBuilder('name', new \stdClass());
    }

    public function testCreateUsesTypeNameIfTypeGivenAsString()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedOptions = array('a' => '2', 'b' => '3');
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
     * @expectedException \Sonatra\Component\Block\Exception\UnexpectedTypeException
     * \@expectedExceptionMessage Expected argument of type "string",
     */
    public function testCreateUsesTypeNameIfTypeGivenAsObject()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedType = $this->getMockResolvedType();

        $this->assertSame('BLOCK', $this->factory->create($resolvedType, null, $options));
    }

    public function testCreateNamed()
    {
        $options = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3');
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
        $registry = $this->getMockBuilder('Sonatra\Component\Block\BlockRegistryInterface')->getMock();
        $factory = $this->getMockBuilder('Sonatra\Component\Block\BlockFactory')
            ->setMethods(array('createNamedBuilder'))
            ->setConstructorArgs(array($registry, $this->resolvedTypeFactory))
            ->getMock();

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', TextType::class, null, array())
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
                        array('attr' => array('data-maxlength' => 10)),
                        Guess::MEDIUM_CONFIDENCE
                    )));

        $this->guesser2->expects($this->once())
            ->method('guessType')
            ->with('Application\Author', 'firstName')
            ->will($this->returnValue(new TypeGuess(
                        PasswordType::class,
                        array('attr' => array('data-maxlength' => 7)),
                        Guess::HIGH_CONFIDENCE
                    )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', PasswordType::class, null, array('attr' => array('data-maxlength' => 7)))
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

        $factory = $this->getMockFactory(array('createNamedBuilder'));

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
                'Sonatra\Component\Block\Extension\Core\Type\TextType',
                array('attr' => array('data-maxlength' => 10)),
                Guess::MEDIUM_CONFIDENCE
            )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', TextType::class, null, array('attr' => array('data-maxlength' => 11)))
            ->will($this->returnValue('builderInstance'));

        /* @var BlockFactoryInterface $factory */
        $this->builder = $factory->createBuilderForProperty(
            'Application\Author',
            'firstName',
            null,
            array('attr' => array('data-maxlength' => 11))
        );

        $this->assertEquals('builderInstance', $this->builder);
    }

    public function testCreateForPropertyWithoutTypeGuesser()
    {
        $expectedOptions = array('id' => 'firstName');
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('Sonatra\Component\Block\Extension\Core\Type\TextType')
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
        return $this->getMockBuilder('Sonatra\Component\Block\ResolvedBlockTypeInterface')->getMock();
    }

    private function getMockFactory(array $methods = array())
    {
        return $this->getMockBuilder('Sonatra\Component\Block\BlockFactory')
            ->setMethods($methods)
            ->setConstructorArgs(array($this->registry, $this->resolvedTypeFactory))
            ->getMock();
    }
}
