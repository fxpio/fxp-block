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

use Fxp\Component\Block\DataTransformerInterface;

/**
 * Transforms between a password type into mask symbol.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PasswordTransformer implements DataTransformerInterface
{
    protected $mask;
    protected $maskLength;
    protected $maskSymbol;

    /**
     * Constructor.
     *
     * @param bool   $mask
     * @param int    $maskLength
     * @param string $maskSymbol
     */
    public function __construct($mask = true, $maskLength = 6, $maskSymbol = '*')
    {
        $this->mask = $mask;
        $this->maskLength = $maskLength;
        $this->maskSymbol = $maskSymbol;
    }

    /**
     * Transforms a password type into mask symbol.
     *
     * @param string $value
     *
     * @return string
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!$this->mask) {
            return $value;
        }

        return str_repeat($this->maskSymbol, $this->maskLength);
    }
}
