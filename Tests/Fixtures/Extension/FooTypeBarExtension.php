<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Fixtures\Extension;

use Sonatra\Component\Block\AbstractTypeExtension;
use Sonatra\Component\Block\BlockBuilderInterface;
use Sonatra\Component\Block\Tests\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FooTypeBarExtension extends AbstractTypeExtension
{
    public function buildForm(BlockBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('bar', 'x');
    }

    public function getAllowedOptionValues()
    {
        return array(
            'a_or_b' => array('c'),
        );
    }

    public function getExtendedType()
    {
        return FooType::class;
    }
}
