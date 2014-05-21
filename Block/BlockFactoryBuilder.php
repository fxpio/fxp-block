<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block;

/**
 * The default implementation of BlockFactoryBuilderInterface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockFactoryBuilder implements BlockFactoryBuilderInterface
{
    /**
     * @var ResolvedBlockTypeFactoryInterface
     */
    private $resolvedTypeFactory;

    /**
     * @var array
     */
    private $extensions = array();

    /**
     * @var array
     */
    private $types = array();

    /**
     * @var array
     */
    private $typeExtensions = array();

    /**
     * @var array
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
        $this->types[$type->getName()] = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTypes(array $types)
    {
        /* @var BlockTypeInterface $type */
        foreach ($types as $type) {
            $this->types[$type->getName()] = $type;
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
