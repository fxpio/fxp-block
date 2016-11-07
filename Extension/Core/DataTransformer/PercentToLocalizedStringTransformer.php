<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Extension\Core\DataTransformer;

use Sonatra\Component\Block\Exception\TransformationFailedException;

/**
 * Transforms between a number type and a localized number with grouping
 * (each thousand) and comma separators and percent symbol.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PercentToLocalizedStringTransformer extends NumberToLocalizedStringTransformer
{
    /**
     * Transforms a number type into localized percent.
     *
     * @param int|float $value Number value
     *
     * @return string Localized value
     *
     * @throws TransformationFailedException if the given value is not numeric
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!is_numeric($value)) {
            throw new TransformationFailedException('Expected a numeric.');
        }

        $formatter = $this->getNumberFormatter(\NumberFormatter::PERCENT);
        $value = $formatter->format($value);

        return $value;
    }
}
