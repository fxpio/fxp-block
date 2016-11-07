<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\Core\Type;

use Sonatra\Component\Block\AbstractType;
use Sonatra\Component\Block\BlockBuilderInterface;
use Sonatra\Component\Block\Extension\Core\DataTransformer\PercentToLocalizedStringTransformer;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PercentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder->resetViewTransformers();

        $builder->addViewTransformer(
            new PercentToLocalizedStringTransformer(
                $options['precision'],
                $options['grouping'],
                $options['rounding_mode'],
                $options['locale']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return NumberType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'percent';
    }
}
