<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block;

use Fxp\Component\Block\Exception\InvalidArgumentException;

/**
 * A block extension with preloaded types, type exceptions and type guessers.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PreloadedExtension implements BlockExtensionInterface
{
    /**
     * @var BlockTypeInterface[]
     */
    private $types = [];

    /**
     * @var array[BlockTypeExtensionInterface[]]
     */
    private $typeExtensions = [];

    /**
     * @var BlockTypeGuesserInterface
     */
    private $typeGuesser;

    /**
     * Creates a new preloaded extension.
     *
     * @param BlockTypeInterface[]            $types          The types that the extension should support
     * @param BlockTypeExtensionInterface[][] $typeExtensions The type extensions that the extension should support
     * @param BlockTypeGuesserInterface|null  $typeGuesser    The guesser that the extension should support
     */
    public function __construct(array $types, array $typeExtensions, BlockTypeGuesserInterface $typeGuesser = null)
    {
        $this->typeExtensions = $typeExtensions;
        $this->typeGuesser = $typeGuesser;

        foreach ($types as $type) {
            $this->types[get_class($type)] = $type;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!isset($this->types[$name])) {
            throw new InvalidArgumentException(sprintf('The type "%s" can not be loaded by this extension', $name));
        }

        return $this->types[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        return isset($this->types[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        return isset($this->typeExtensions[$name])
            ? $this->typeExtensions[$name]
            : [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasTypeExtensions($name)
    {
        return !empty($this->typeExtensions[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        return $this->typeGuesser;
    }
}
