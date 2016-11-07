<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Doctrine\DataTransformer;

use Sonatra\Component\Block\Exception\UnexpectedTypeException;
use Sonatra\Component\Block\DataTransformerInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CollectionToArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforms a collection into an array.
     *
     * @param Collection $collection A collection of entities
     *
     * @return mixed An array of entities
     *
     * @throws UnexpectedTypeException When unexpected type
     */
    public function transform($collection)
    {
        if (null === $collection) {
            return array();
        }

        if (!$collection instanceof Collection) {
            throw new UnexpectedTypeException($collection, 'Doctrine\Common\Collections\Collection');
        }

        return $collection->toArray();
    }
}
