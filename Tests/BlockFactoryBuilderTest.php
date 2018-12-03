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
use Fxp\Component\Block\BlockFactoryBuilder;
use Fxp\Component\Block\BlockFactoryBuilderInterface;
use Fxp\Component\Block\BlockTypeGuesserInterface;
use Fxp\Component\Block\BlockTypeInterface;
use Fxp\Component\Block\ResolvedBlockTypeFactoryInterface;
use Fxp\Component\Block\Tests\Fixtures\Extension\FooExtension;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockFactoryBuilderTest extends TestCase
{
    /**
     * @var BlockFactoryBuilderInterface
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new BlockFactoryBuilder();
    }

    protected function tearDown()
    {
        $this->builder = null;
    }

    public function testSetResolvedBlockTypeFactory()
    {
        /* @var ResolvedBlockTypeFactoryInterface $typeFactory */
        $typeFactory = $this->getMockBuilder('Fxp\Component\Block\ResolvedBlockTypeFactoryInterface')->getMock();

        $builder = $this->builder->setResolvedTypeFactory($typeFactory);

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddExtension()
    {
        /* @var BlockExtensionInterface $ext */
        $ext = $this->getMockBuilder('Fxp\Component\Block\BlockExtensionInterface')->getMock();

        $builder = $this->builder->addExtension($ext);

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddExtensions()
    {
        $exts = [
            $this->getMockBuilder('Fxp\Component\Block\BlockExtensionInterface')->getMock(),
        ];

        $builder = $this->builder->addExtensions($exts);

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddType()
    {
        /* @var BlockTypeInterface $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\BlockTypeInterface')->getMock();

        $builder = $this->builder->addType($type);

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypes()
    {
        $types = [
            $this->getMockBuilder('Fxp\Component\Block\BlockTypeInterface')->getMock(),
        ];

        $builder = $this->builder->addTypes($types);

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypeExtension()
    {
        $ext = new FooExtension();

        $builder = $this->builder->addTypeExtension($ext);

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypeExtensions()
    {
        $exts = [
            new FooExtension(),
        ];

        $builder = $this->builder->addTypeExtensions($exts);

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypeGuesser()
    {
        /* @var BlockTypeGuesserInterface $guesser */
        $guesser = $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock();

        $builder = $this->builder->addTypeGuesser($guesser);

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypeGuessers()
    {
        $guessers = [
            $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock(),
        ];

        $builder = $this->builder->addTypeGuessers($guessers);

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testGetBlockFactory()
    {
        /* @var BlockTypeInterface $type */
        $type = $this->getMockBuilder('Fxp\Component\Block\BlockTypeInterface')->getMock();
        $this->builder->addType($type);

        $of = $this->builder->getBlockFactory();

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactory', $of);

        /* @var BlockTypeGuesserInterface $guesser */
        $guesser = $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock();
        /* @var BlockTypeGuesserInterface $guesser2 */
        $guesser2 = $this->getMockBuilder('Fxp\Component\Block\BlockTypeGuesserInterface')->getMock();
        $this->builder->addTypeGuesser($guesser);
        $this->builder->addTypeGuesser($guesser2);

        $of = $this->builder->getBlockFactory();

        $this->assertInstanceOf('Fxp\Component\Block\BlockFactory', $of);
    }
}
