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

use Fxp\Component\Block\BlockFactoryInterface;
use Fxp\Component\Block\ResolvedBlockType;
use Fxp\Component\Block\Tests\Fixtures\Extension\FooExtension;
use Fxp\Component\Block\Tests\Fixtures\Type\FooSubType;
use Fxp\Component\Block\Tests\Fixtures\Type\FooType;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ResolvedBlockTypeTest extends TestCase
{
    /**
     * @expectedException \Fxp\Component\Block\Exception\UnexpectedTypeException
     */
    public function testWrongExtensions()
    {
        new ResolvedBlockType(new FooType(), ['wrong_extension']);
    }

    public function testBasicOperations()
    {
        $parentType = new FooSubType();
        $type = new FooType();
        $rType = new ResolvedBlockType($type, [new FooExtension()], new ResolvedBlockType($parentType));

        $this->assertEquals($type->getBlockPrefix(), $rType->getBlockPrefix());
        $this->assertInstanceOf('Fxp\Component\Block\ResolvedBlockTypeInterface', $rType->getParent());
        $this->assertEquals($type, $rType->getInnerType());

        $exts = $rType->getTypeExtensions();
        $this->assertInternalType('array', $exts);
        $this->assertCount(1, $exts);

        $options = $rType->getOptionsResolver();
        $this->assertInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver', $options);
    }

    public function testBuildBlockAndBuildView()
    {
        $type = new FooType();
        $parentType = new FooSubType();
        $rType = new ResolvedBlockType($type, [new FooExtension()], new ResolvedBlockType($parentType));

        /* @var BlockFactoryInterface $factory */
        $factory = $this->getMockBuilder('Fxp\Component\Block\BlockFactoryInterface')->getMock();
        $builder = $rType->createBuilder($factory, 'name');

        $this->assertInstanceOf('Fxp\Component\Block\BlockBuilderInterface', $builder);
        $this->assertEquals($rType, $builder->getType());

        $rType->buildBlock($builder, $builder->getOptions());
        $rType->finishBlock($builder, $builder->getOptions());

        $block = $builder->getBlock();
        $view = $rType->createView($block);
        $this->assertInstanceOf('Fxp\Component\Block\BlockView', $view);

        $rType->buildView($view, $block, $block->getOptions());
        $rType->finishView($view, $block, $block->getOptions());
    }

    public function testAddChildAndRemoveChild()
    {
        $type = new FooType();
        $parentType = new FooSubType();
        $rType = new ResolvedBlockType($type, [new FooExtension()], new ResolvedBlockType($parentType));

        /* @var BlockFactoryInterface $factory */
        $factory = $this->getMockBuilder('Fxp\Component\Block\BlockFactoryInterface')->getMock();
        $block1 = $rType->createBuilder($factory, 'name1')->getBlock();
        $block2 = $rType->createBuilder($factory, 'name2')->getBlock();

        $this->assertInstanceOf('Fxp\Component\Block\BlockBuilderInterface', $block1->getConfig());
        $this->assertInstanceOf('Fxp\Component\Block\BlockBuilderInterface', $block2->getConfig());

        $rType->addParent($block1, $block2, $block1->getOptions());
        $rType->addChild($block2, $block1, $block2->getOptions());

        $rType->removeChild($block2, $block1, $block2->getOptions());
        $rType->removeParent($block1, $block2, $block1->getOptions());

        $view1 = $rType->createView($block1);
        $view2 = $rType->createView($block2);

        $this->assertInstanceOf('Fxp\Component\Block\BlockView', $view1);
        $this->assertInstanceOf('Fxp\Component\Block\BlockView', $view2);

        $rType->buildView($view1, $block1, $block1->getOptions());
        $rType->finishView($view1, $block1, $block1->getOptions());
        $rType->buildView($view2, $block2, $block2->getOptions());
        $rType->finishView($view2, $block2, $block2->getOptions());
    }

    public function testAbstractType()
    {
        /* @var \Fxp\Component\Block\AbstractType $type */
        $type = $this->getMockForAbstractClass('Fxp\Component\Block\AbstractType');
        $this->assertEquals('Fxp\Component\Block\Extension\Core\Type\BlockType', $type->getParent());
    }
}
