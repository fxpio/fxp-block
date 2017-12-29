<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Doctrine\DataTransformer;

use Doctrine\Common\Collections\Collection;
use Fxp\Component\Block\DataTransformerInterface;
use Fxp\Component\Block\Exception\UnexpectedTypeException;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
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
