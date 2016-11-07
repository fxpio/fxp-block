<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Fixtures\Type;

use Sonatra\Component\Block\AbstractType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FooType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        // return null
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'foo';
    }
}
