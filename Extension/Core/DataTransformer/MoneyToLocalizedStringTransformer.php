<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\Core\DataTransformer;

use Fxp\Component\Block\Exception\TransformationFailedException;
use Fxp\Component\Block\Exception\UnexpectedTypeException;

/**
 * Transforms between a number type and a localized number with grouping
 * (each thousand) and comma separators and money symbol.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MoneyToLocalizedStringTransformer extends NumberToLocalizedStringTransformer
{
    protected $currency;
    protected $divisor;

    /**
     * Constructor.
     *
     * @param int    $precision
     * @param bool   $grouping
     * @param int    $roundingMode
     * @param string $locale
     * @param string $currency
     * @param int    $divisor
     */
    public function __construct($precision = null, $grouping = null, $roundingMode = null, $locale = null, $currency = null, $divisor = null)
    {
        parent::__construct($precision, $grouping, $roundingMode, $locale);
        $this->currency = $currency;

        if (null === $divisor) {
            $divisor = 1;
        }

        $this->divisor = $divisor;
    }

    /**
     * Transforms a number type into localized money.
     *
     * @param int|float $value Number value
     *
     * @return string Localized value
     *
     * @throws UnexpectedTypeException       if the given value is not numeric
     * @throws TransformationFailedException if the value can not be transformed
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!is_numeric($value)) {
            throw new UnexpectedTypeException($value, 'numeric');
        }

        $formatter = $this->getNumberFormatter(\NumberFormatter::CURRENCY);
        $value /= $this->divisor;

        if (null !== $this->currency) {
            $value = $formatter->formatCurrency($value, $this->currency);
        } else {
            $value = $formatter->format($value);
        }

        if (0 !== $formatter->getErrorCode()) {
            throw new TransformationFailedException($formatter->getErrorMessage());
        }

        return $value;
    }
}
