<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type;

use Sonatra\Bundle\BlockBundle\Block\AbstractType;
use Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DateTimeType extends AbstractType
{
    const DEFAULT_DATE_FORMAT = \IntlDateFormatter::MEDIUM;
    const DEFAULT_TIME_FORMAT = \IntlDateFormatter::SHORT;

    protected static $acceptedFormats = array(
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
    );

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        if (!$options['locale']) {
            $options['locale'] = \Locale::getDefault();
        }

        if (!$options['calendar']) {
            $options['calendar'] = \IntlDateFormatter::GREGORIAN;
        }

        if (!$options['date_format']) {
            $options['date_format'] = self::DEFAULT_DATE_FORMAT;
        }

        if (!$options['time_format']) {
            $options['time_format'] = self::DEFAULT_TIME_FORMAT;
        }

        if (!in_array($options['date_format'], self::$acceptedFormats, true)) {
            throw new InvalidOptionsException('The "date_format" option must be one of the IntlDateFormatter constants (FULL, LONG, MEDIUM, SHORT, NONE).');
        }

        if (!in_array($options['time_format'], self::$acceptedFormats, true)) {
            throw new InvalidOptionsException('The "time_format" option must be one of the IntlDateFormatter constants (FULL, LONG, MEDIUM, SHORT, NONE).');
        }

        $builder
            ->addViewTransformer(
                    new DateTimeToLocalizedStringTransformer(
                            $options['calendar'],
                            $options['date_format'],
                            $options['time_format'],
                            $options['timezone'],
                            $options['locale']
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'locale'      => null,
                'timezone'    => null,
                'date_format' => null,
                'time_format' => null,
                'calendar'    => null,
                'data_class'  => null,
                'compound'    => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'datetime';
    }
}
