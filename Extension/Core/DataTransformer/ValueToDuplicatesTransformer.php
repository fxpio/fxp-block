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
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ValueToDuplicatesTransformer implements DataTransformerInterface
{
    private $keys;

    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * Duplicates the given value through the array.
     *
     * @param mixed $value The value
     *
     * @return array The array
     */
    public function transform($value)
    {
        $result = array();

        foreach ($this->keys as $key) {
            $result[$key] = $value;
        }

        return $result;
    }
}
