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
use Sonatra\Component\Block\Extension\Core\DataTransformer\PasswordTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(
                    new PasswordTransformer(
                            $options['mask'],
                            $options['mask_length'],
                            $options['mask_symbol']
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'mask' => true,
            'mask_length' => 6,
            'mask_symbol' => '*',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'password';
    }
}
