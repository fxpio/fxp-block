<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Fixtures;

use Fxp\Component\Block\AbstractExtension;
use Fxp\Component\Block\Tests\Fixtures\Extension\FooExtension;
use Fxp\Component\Block\Tests\Fixtures\Type\FooType;

/**
 * Test for extensions which provide types and type extensions.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TestExtension extends AbstractExtension
{
    protected function loadTypes()
    {
        return [
            new FooType(),
        ];
    }

    protected function loadTypeExtensions()
    {
        return [
            new FooExtension(),
        ];
    }
}
