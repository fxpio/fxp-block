<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Fixtures;

use Sonatra\Component\Block\AbstractExtension;
use Sonatra\Component\Block\Tests\Fixtures\Extension\FooExtension;
use Sonatra\Component\Block\Tests\Fixtures\Type\FooType;

/**
 * Test for extensions which provide types and type extensions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class TestExtension extends AbstractExtension
{
    protected function loadTypes()
    {
        return array(
            new FooType(),
        );
    }

    protected function loadTypeExtensions()
    {
        return array(
            new FooExtension(),
        );
    }
}
