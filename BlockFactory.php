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

use Fxp\Component\Block\Exception\UnexpectedTypeException;
use Fxp\Component\Block\Extension\Core\Type\BlockType;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\Util\BlockUtil;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockFactory implements BlockFactoryInterface
{
    /**
     * @var BlockRegistryInterface
     */
    protected $registry;

    /**
     * @var ResolvedBlockTypeFactoryInterface
     */
    protected $resolvedTypeFactory;

    /**
     * Constructor.
     *
     * @param BlockRegistryInterface            $registry
     * @param ResolvedBlockTypeFactoryInterface $resolvedTypeFactory
     */
    public function __construct(BlockRegistryInterface $registry, ResolvedBlockTypeFactoryInterface $resolvedTypeFactory)
    {
        $this->registry = $registry;
        $this->resolvedTypeFactory = $resolvedTypeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create($type = BlockType::class, $data = null, array $options = [])
    {
        return $this->createBuilder($type, $data, $options)->getBlock();
    }

    /**
     * {@inheritdoc}
     */
    public function createNamed($name, $type = BlockType::class, $data = null, array $options = [])
    {
        return $this->createNamedBuilder($name, $type, $data, $options)->getBlock();
    }

    /**
     * {@inheritdoc}
     */
    public function createForProperty($class, $property, $data = null, array $options = [])
    {
        return $this->createBuilderForProperty($class, $property, $data, $options)->getBlock();
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilder($type = BlockType::class, $data = null, array $options = [])
    {
        $name = array_key_exists('block_name', $options) ? $options['block_name'] : BlockUtil::createUniqueName();
        $name = array_key_exists('id', $options) ? $options['id'] : $name;

        return $this->createNamedBuilder($name, $type, $data, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createNamedBuilder($name, $type = BlockType::class, $data = null, array $options = [])
    {
        if (null !== $data && !array_key_exists('data', $options)) {
            $options['data'] = $data;
        }

        if (!array_key_exists('id', $options)) {
            $options['id'] = array_key_exists('block_name', $options) ? $options['block_name'] : $name;
        }

        if (!is_string($type)) {
            throw new UnexpectedTypeException($type, 'string');
        }

        $type = $this->registry->getType($type);

        $builder = $type->createBuilder($this, $name, $options);

        // Explicitly call buildBlock() in order to be able to override either
        // createBuilder() or buildBlock() in the resolved block type
        $type->buildBlock($builder, $builder->getOptions());
        $type->finishBlock($builder, $builder->getOptions());

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilderForProperty($class, $property, $data = null, array $options = [])
    {
        if (null === $guesser = $this->registry->getTypeGuesser()) {
            return $this->createNamedBuilder($property, TextType::class, $data, $options);
        }

        $typeGuess = $guesser->guessType($class, $property);
        $type = $typeGuess ? $typeGuess->getType() : TextType::class;

        // user options may override guessed options
        if ($typeGuess) {
            $options = array_merge($typeGuess->getOptions(), $options);
        }

        return $this->createNamedBuilder($property, $type, $data, $options);
    }
}
