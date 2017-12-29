<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Tests\Fixtures\Extension;

use Fxp\Component\Block\AbstractTypeExtension;
use Fxp\Component\Block\BlockBuilderInterface;
use Fxp\Component\Block\Tests\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
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
