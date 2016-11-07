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
 * The central registry of the Block component.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockRegistryInterface
{
    /**
     * Returns a block type by name.
     *
     * This methods registers the type extensions block and the block extensions.
     *
     * @param string $name The name of the type
     *
     * @return ResolvedBlockTypeInterface The type
     *
     * @throws Exception\InvalidArgumentException If the type can not be retrieved block any extension
     */
    public function getType($name);

    /**
     * Returns whether the given block type is supported.
     *
     * @param string $name The name of the type
     *
     * @return bool Whether the type is supported
     */
    public function hasType($name);

    /**
     * Returns the guesser responsible for guessing types.
     *
     * @return BlockTypeGuesserInterface
     */
    public function getTypeGuesser();

    /**
     * Returns the extensions loaded by the framework.
     *
     * @return BlockExtensionInterface[]
     */
    public function getExtensions();
}
