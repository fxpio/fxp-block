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

use Sonatra\Component\Block\Exception\UnexpectedTypeException;
use Sonatra\Component\Block\Exception\ExceptionInterface;
use Sonatra\Component\Block\Exception\InvalidArgumentException;

/**
 * The central registry of the Block component.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockRegistry implements BlockRegistryInterface
{
    /**
     * Extensions.
     *
     * @var BlockExtensionInterface[] An array of BlockExtensionInterface
     */
    protected $extensions = array();

    /**
     * @var array
     */
    protected $types = array();

    /**
     * @var BlockTypeGuesserInterface|false|null
     */
    protected $guesser = false;

    /**
     * @var ResolvedBlockTypeFactoryInterface
     */
    protected $resolvedTypeFactory;

    /**
     * Constructor.
     *
     * @param BlockExtensionInterface[]         $extensions          An array of BlockExtensionInterface
     * @param ResolvedBlockTypeFactoryInterface $resolvedTypeFactory The factory for resolved block types
     *
     * @throws UnexpectedTypeException if any extension does not implement BlockExtensionInterface
     */
    public function __construct(array $extensions, ResolvedBlockTypeFactoryInterface $resolvedTypeFactory)
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof BlockExtensionInterface) {
                throw new UnexpectedTypeException($extension, 'Sonatra\Component\Block\BlockExtensionInterface');
            }
        }

        $this->extensions = $extensions;
        $this->resolvedTypeFactory = $resolvedTypeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!is_string($name)) {
            throw new UnexpectedTypeException($name, 'string');
        }

        if (!isset($this->types[$name])) {
            /** @var BlockTypeInterface $type */
            $type = null;

            foreach ($this->extensions as $extension) {
                if ($extension->hasType($name)) {
                    $type = $extension->getType($name);
                    break;
                }
            }

            if (!$type) {
                // Support fully-qualified class names
                if (class_exists($name) && in_array('Sonatra\Component\Block\BlockTypeInterface', class_implements($name))) {
                    $type = new $name();
                } else {
                    throw new InvalidArgumentException(sprintf('Could not load type "%s"', $name));
                }
            }

            $this->types[$name] = $this->resolveType($type);
        }

        return $this->types[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        if (isset($this->types[$name])) {
            return true;
        }

        try {
            $this->getType($name);
        } catch (ExceptionInterface $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        if (false === $this->guesser) {
            $guessers = array();

            foreach ($this->extensions as $extension) {
                $guesser = $extension->getTypeGuesser();

                if ($guesser) {
                    $guessers[] = $guesser;
                }
            }

            $this->guesser = !empty($guessers) ? new BlockTypeGuesserChain($guessers) : null;
        }

        return $this->guesser;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Wraps a type into a ResolvedBlockTypeInterface implementation and connects
     * it with its parent type.
     *
     * @param BlockTypeInterface $type The type to resolve
     *
     * @return ResolvedBlockTypeInterface The resolved type
     */
    private function resolveType(BlockTypeInterface $type)
    {
        $typeExtensions = array();
        $parentType = $type->getParent();
        $fqcn = get_class($type);

        foreach ($this->extensions as $extension) {
            $typeExtensions = array_merge(
                $typeExtensions,
                $extension->getTypeExtensions($fqcn)
            );
        }

        return $this->resolvedTypeFactory->createResolvedType(
            $type,
            $typeExtensions,
            $parentType ? $this->getType($parentType) : null
        );
    }
}
