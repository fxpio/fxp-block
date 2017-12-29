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

/**
 * The default implementation of BlockFactoryBuilderInterface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockFactoryBuilder implements BlockFactoryBuilderInterface
{
    /**
     * @var ResolvedBlockTypeFactoryInterface
     */
    private $resolvedTypeFactory;

    /**
     * @var BlockExtensionInterface[]
     */
    private $extensions = array();

    /**
     * @var BlockTypeInterface[]
     */
    private $types = array();

    /**
     * @var BlockTypeExtensionInterface[]
     */
    private $typeExtensions = array();

    /**
     * @var BlockTypeGuesserInterface[]
     */
    private $typeGuessers = array();

    /**
     * {@inheritdoc}
     */
    public function setResolvedTypeFactory(ResolvedBlockTypeFactoryInterface $resolvedTypeFactory)
    {
        $this->resolvedTypeFactory = $resolvedTypeFactory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtension(BlockExtensionInterface $extension)
    {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtensions(array $extensions)
    {
        $this->extensions = array_merge($this->extensions, $extensions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addType(BlockTypeInterface $type)
    {
        $this->types[] = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTypes(array $types)
    {
        /* @var BlockTypeInterface $type */
        foreach ($types as $type) {
            $this->types[] = $type;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTypeExtension(BlockTypeExtensionInterface $typeExtension)
    {
        $this->typeExtensions[$typeExtension->getExtendedType()][] = $typeExtension;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTypeExtensions(array $typeExtensions)
    {
        /* @var BlockTypeExtensionInterface $typeExtension */
        foreach ($typeExtensions as $typeExtension) {
            $this->typeExtensions[$typeExtension->getExtendedType()][] = $typeExtension;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTypeGuesser(BlockTypeGuesserInterface $typeGuesser)
    {
        $this->typeGuessers[] = $typeGuesser;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTypeGuessers(array $typeGuessers)
    {
        $this->typeGuessers = array_merge($this->typeGuessers, $typeGuessers);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockFactory()
    {
        $extensions = $this->extensions;

        if (count($this->types) > 0 || count($this->typeExtensions) > 0 || count($this->typeGuessers) > 0) {
            if (count($this->typeGuessers) > 1) {
                $typeGuesser = new BlockTypeGuesserChain($this->typeGuessers);
            } else {
                $typeGuesser = isset($this->typeGuessers[0]) ? $this->typeGuessers[0] : null;
            }

            $extensions[] = new PreloadedExtension($this->types, $this->typeExtensions, $typeGuesser);
        }

        $resolvedTypeFactory = $this->resolvedTypeFactory ?: new ResolvedBlockTypeFactory();
        $registry = new BlockRegistry($extensions, $resolvedTypeFactory);

        return new BlockFactory($registry, $resolvedTypeFactory);
    }
}
