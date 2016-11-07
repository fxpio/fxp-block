<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Tests\Fixtures\DataTransformer;

use Sonatra\Component\Block\DataTransformerInterface;
use Sonatra\Component\Block\Exception\RuntimeException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FixedDataTransformer implements DataTransformerInterface
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function transform($value)
    {
        if (!array_key_exists($value, $this->mapping)) {
            throw new RuntimeException(sprintf('No mapping for value "%s"', $value));
        }

        return $this->mapping[$value];
    }
}
