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
use Fxp\Component\Block\Exception\UnexpectedTypeException;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractExtension implements BlockExtensionInterface
{
    /**
     * The types provided by this extension.
     *
     * @var BlockTypeInterface[] An array of BlockTypeInterface
     */
    private $types;

    /**
     * The type extensions provided by this extension.
     *
     * @var array An array of BlockTypeExtensionInterface
     */
    private $typeExtensions;

    /**
     * The type guesser provided by this extension.
     *
     * @var BlockTypeGuesserInterface
     */
    private $typeGuesser;

    /**
     * Whether the type guesser has been loaded.
     *
     * @var bool
     */
    private $typeGuesserLoaded = false;

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (null === $this->types) {
            $this->initTypes();
        }

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
        if (null === $this->types) {
            $this->initTypes();
        }

        return isset($this->types[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        if (null === $this->typeExtensions) {
            $this->initTypeExtensions();
        }

        return isset($this->typeExtensions[$name])
            ? $this->typeExtensions[$name]
            : [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasTypeExtensions($name)
    {
        if (null === $this->typeExtensions) {
            $this->initTypeExtensions();
        }

        return isset($this->typeExtensions[$name]) && count($this->typeExtensions[$name]) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        if (!$this->typeGuesserLoaded) {
            $this->initTypeGuesser();
        }

        return $this->typeGuesser;
    }

    /**
     * Registers the types.
     *
     * @return BlockTypeInterface[] An array of BlockTypeInterface instances
     */
    protected function loadTypes()
    {
        return [];
    }

    /**
     * Registers the type extensions.
     *
     * @return BlockTypeExtensionInterface[] An array of BlockTypeExtensionInterface instances
     */
    protected function loadTypeExtensions()
    {
        return [];
    }

    /**
     * Registers the type guesser.
     *
     * @return BlockTypeGuesserInterface|null A type guesser
     */
    protected function loadTypeGuesser()
    {
        // return null
    }

    /**
     * Initializes the types.
     *
     * @throws UnexpectedTypeException if any registered type is not an instance of BlockTypeInterface
     */
    private function initTypes()
    {
        $this->types = [];

        foreach ($this->loadTypes() as $type) {
            if (!$type instanceof BlockTypeInterface) {
                throw new UnexpectedTypeException($type, 'Fxp\Component\Block\BlockTypeInterface');
            }

            $this->types[get_class($type)] = $type;
        }
    }

    /**
     * Initializes the type extensions.
     *
     * @throws UnexpectedTypeException if any registered type extension is not
     *                                 an instance of BlockTypeExtensionInterface
     */
    private function initTypeExtensions()
    {
        $this->typeExtensions = [];

        foreach ($this->loadTypeExtensions() as $extension) {
            if (!$extension instanceof BlockTypeExtensionInterface) {
                throw new UnexpectedTypeException($extension, 'Fxp\Component\Block\BlockTypeExtensionInterface');
            }

            $type = $extension->getExtendedType();

            $this->typeExtensions[$type][] = $extension;
        }
    }

    /**
     * Initializes the type guesser.
     *
     * @throws UnexpectedTypeException if the type guesser is not an instance of BlockTypeGuesserInterface
     */
    private function initTypeGuesser()
    {
        $this->typeGuesserLoaded = true;

        $this->typeGuesser = $this->loadTypeGuesser();
        if (null !== $this->typeGuesser && !$this->typeGuesser instanceof BlockTypeGuesserInterface) {
            throw new UnexpectedTypeException($this->typeGuesser, 'Fxp\Component\Block\BlockTypeGuesserInterface');
        }
    }
}
