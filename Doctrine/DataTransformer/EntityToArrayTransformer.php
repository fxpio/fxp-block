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

use Fxp\Component\Block\DataTransformerInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class EntityToArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforms a entity into an array.
     *
     * @param object $entity A entity
     *
     * @return mixed An array of entity
     */
    public function transform($entity)
    {
        if (null === $entity) {
            return [];
        }

        return [$entity];
    }
}
