<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block;

/**
 * Interface for extensions which provide types, type extensions and a guesser.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockExtensionInterface
{
    /**
     * Returns a type by name.
     *
     * @param string $name The name of the type
     *
     * @return BlockTypeInterface The type
     *
     * @throws Exception\InvalidArgumentException if the given type is not supported by this extension
     */
    public function getType($name);

    /**
     * Returns whether the given type is supported.
     *
     * @param string $name The name of the type
     *
     * @return Boolean Whether the type is supported by this extension
     */
    public function hasType($name);

    /**
     * Returns the extensions for the given type.
     *
     * @param string $name The name of the type
     *
     * @return BlockTypeExtensionInterface[] An array of extensions as BlockTypeExtensionInterface instances
     */
    public function getTypeExtensions($name);

    /**
     * Returns whether this extension provides type extensions for the given type.
     *
     * @param string $name The name of the type
     *
     * @return Boolean Whether the given type has extensions
     */
    public function hasTypeExtensions($name);

    /**
     * Returns the type guesser provided by this extension.
     *
     * @return BlockTypeGuesserInterface|null The type guesser
     */
    public function getTypeGuesser();
}
