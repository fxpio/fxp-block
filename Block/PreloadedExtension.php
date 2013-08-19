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

use Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException;

/**
 * A block extension with preloaded types, type exceptions and type guessers.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PreloadedExtension implements BlockExtensionInterface
{
    /**
     * @var array
     */
    private $types = array();

    /**
     * @var array
     */
    private $typeExtensions = array();

    /**
     * @var BlockTypeGuesserInterface
     */
    private $typeGuesser;

    /**
     * Creates a new preloaded extension.
     *
     * @param array                          $types          The types that the extension should support.
     * @param array                          $typeExtensions The type extensions that the extension should support.
     * @param BlockTypeGuesserInterface|null $typeGuesser    The guesser that the extension should support.
     */
    public function __construct(array $types, array $typeExtensions, BlockTypeGuesserInterface $typeGuesser = null)
    {
        $this->types = $types;
        $this->typeExtensions = $typeExtensions;
        $this->typeGuesser = $typeGuesser;
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
            : array();
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
