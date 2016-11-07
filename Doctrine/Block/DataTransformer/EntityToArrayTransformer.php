<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Doctrine\Block\DataTransformer;

use Sonatra\Component\Block\DataTransformerInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
            return array();
        }

        return array($entity);
    }
}
