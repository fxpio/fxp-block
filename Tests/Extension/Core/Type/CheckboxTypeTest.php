<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Extension\Core\Type;

use Fxp\Component\Block\Extension\Core\DataTransformer\CallbackTransformer;
use Fxp\Component\Block\Extension\Core\Type\CheckboxType;
use Fxp\Component\Block\Tests\TypeTestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CheckboxTypeTest extends TypeTestCase
{
    public function testDataIsFalseByDefault()
    {
        $block = $this->factory->create(CheckboxType::class);

        $this->assertFalse($block->getData());
        $this->assertFalse($block->getNormData());
        $this->assertEquals('', $block->getViewData());
    }

    public function testCheckedIfDataTrue()
    {
        $block = $this->factory->create(CheckboxType::class);
        $block->setData(true);
        $view = $block->createView();

        $this->assertTrue($view->vars['checked']);
        $this->assertEquals('checked', $view->vars['value']);
    }

    public function testNotCheckedIfDataFalse()
    {
        $block = $this->factory->create(CheckboxType::class);
        $block->setData(false);
        $view = $block->createView();

        $this->assertFalse($view->vars['checked']);
        $this->assertEquals('', $view->vars['value']);
    }

    public function provideCustomModelTransformerData()
    {
        return [
            ['checked', true],
            ['unchecked', false],
        ];
    }

    /**
     * @dataProvider provideCustomModelTransformerData
     */
    public function testCustomModelTransformer($data, $checked)
    {
        // present a binary status field as a checkbox
        $transformer = new CallbackTransformer(
            function ($value) {
                return 'checked' == $value;
            }
        );

        $builder = $this->factory->createBuilder(CheckboxType::class);
        $builder->addModelTransformer($transformer);
        $block = $builder->getBlock();

        $block->setData($data);
        $view = $block->createView();

        $this->assertSame($data, $block->getData());
        $this->assertSame($checked, $block->getNormData());
        $this->assertEquals($checked, $view->vars['checked']);
    }
}
