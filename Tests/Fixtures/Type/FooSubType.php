<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Fixtures\Type;

use Fxp\Component\Block\AbstractType;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FooSubType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FooType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'foo_sub_type';
    }
}
