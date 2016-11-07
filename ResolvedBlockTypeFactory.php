<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ResolvedBlockTypeFactory implements ResolvedBlockTypeFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResolvedType(BlockTypeInterface $type, array $typeExtensions, ResolvedBlockTypeInterface $parent = null)
    {
        return new ResolvedBlockType($type, $typeExtensions, $parent);
    }
}
