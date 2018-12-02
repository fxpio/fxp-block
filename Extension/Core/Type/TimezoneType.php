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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TimezoneType extends AbstractType
{
    /**
     * Stores the available timezone choices.
     *
     * @var array
     */
    private static $timezones;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => static::getFlippedTimezones(),
            'choice_translation_domain' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'timezone';
    }

    /**
     * Returns the timezone choices.
     *
     * The choices are generated from the ICU function
     * \DateTimeZone::listIdentifiers(). They are cached during a single request,
     * so multiple timezone fields on the same page don't lead to unnecessary
     * overhead.
     *
     * @return array The timezone choices
     */
    private static function getFlippedTimezones()
    {
        if (null === static::$timezones) {
            static::$timezones = [];

            foreach (\DateTimeZone::listIdentifiers() as $timezone) {
                $parts = explode('/', $timezone);

                if (\count($parts) > 2) {
                    $region = $parts[0];
                } elseif (\count($parts) > 1) {
                    $region = $parts[0];
                } else {
                    $region = 'Other';
                }

                static::$timezones[$region][str_replace('_', ' ', $timezone)] = $timezone;
            }
        }

        return static::$timezones;
    }
}
