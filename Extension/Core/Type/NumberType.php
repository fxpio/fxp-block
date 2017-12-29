<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\Core\Type;

use Fxp\Component\Block\AbstractType;
use Fxp\Component\Block\BlockBuilderInterface;
use Fxp\Component\Block\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class NumberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new NumberToLocalizedStringTransformer(
                $options['precision'],
                $options['grouping'],
                $options['rounding_mode'],
                $options['locale']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // default precision is locale specific (usually around 3)
            'precision' => null,
            'grouping' => false,
            'locale' => \Locale::getDefault(),
            // Integer cast rounds towards 0, so do the same when displaying fractions
            'rounding_mode' => NumberToLocalizedStringTransformer::ROUND_HALF_EVEN,
            'compound' => false,
        ]);

        $resolver->setAllowedValues('rounding_mode', [
            NumberToLocalizedStringTransformer::ROUND_CEILING,
            NumberToLocalizedStringTransformer::ROUND_DOWN,
            NumberToLocalizedStringTransformer::ROUND_FLOOR,
            NumberToLocalizedStringTransformer::ROUND_HALF_DOWN,
            NumberToLocalizedStringTransformer::ROUND_HALF_EVEN,
            NumberToLocalizedStringTransformer::ROUND_HALF_UP,
            NumberToLocalizedStringTransformer::ROUND_UP,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FieldType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'number';
    }
}
