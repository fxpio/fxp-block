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

use Fxp\Component\Block\Exception\BadMethodCallException;
use Fxp\Component\Block\Exception\InvalidArgumentException;
use Fxp\Component\Block\Exception\UnexpectedTypeException;
use Fxp\Component\Block\Extension\Core\Type\TextType;
use Fxp\Component\Block\Util\BlockUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A builder for creating {@link Block} instances.
 *
 * @author Francois Pluchino
 */
class BlockBuilder extends BlockConfigBuilder implements \IteratorAggregate, BlockBuilderInterface
{
    /**
     * The block factory.
     *
     * @var BlockFactoryInterface
     */
    private $factory;

    /**
     * The children of the block builder.
     *
     * @var array
     */
    private $children = array();

    /**
     * The data of children who haven't been converted to block builders yet.
     *
     * @var array
     */
    private $unresolvedChildren = array();

    /**
     * Creates a new block builder.
     *
     * @param string                   $name
     * @param string                   $dataClass
     * @param EventDispatcherInterface $dispatcher
     * @param BlockFactoryInterface    $factory
     * @param array                    $options
     */
    public function __construct($name, $dataClass, EventDispatcherInterface $dispatcher, BlockFactoryInterface $factory, array $options = array())
    {
        parent::__construct($name, $dataClass, $dispatcher, $options);

        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockFactory()
    {
        return $this->factory;
    }

    /**
     * {@inheritdoc}
     */
    public function add($child, $type = null, array $options = array())
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        if ($child instanceof BlockBuilderInterface) {
            $this->children[$child->getName()] = $child;

            // In case an unresolved child with the same name exists
            unset($this->unresolvedChildren[$child->getName()]);

            return $this;
        }

        if (null !== $child && !is_string($child)) {
            throw new UnexpectedTypeException($child, 'string or Fxp\Component\Block\BlockBuilder');
        }

        if (null !== $type && !is_string($type) && !$type instanceof BlockTypeInterface) {
            throw new UnexpectedTypeException($type, 'string or Fxp\Component\Block\BlockTypeInterface');
        }

        if (null === $child) {
            $child = BlockUtil::createUniqueName('');
        }

        // Add to "children" to maintain order
        $this->children[$child] = null;
        $this->unresolvedChildren[$child] = array(
            'type' => $type,
            'options' => $options,
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create($name, $type = null, array $options = array())
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        if (null === $type && null === $this->getDataClass()) {
            $type = TextType::class;
        }

        if (null !== $type) {
            return $this->factory->createNamedBuilder($name, $type, null, $options);
        }

        return $this->factory->createBuilderForProperty($this->getDataClass(), $name, null, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        if (isset($this->unresolvedChildren[$name])) {
            return $this->resolveChild($name);
        }

        if (isset($this->children[$name])) {
            return $this->children[$name];
        }

        throw new InvalidArgumentException(sprintf('The child with the name "%s" does not exist.', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        unset($this->unresolvedChildren[$name]);
        unset($this->children[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        if (isset($this->unresolvedChildren[$name])) {
            return true;
        }

        if (isset($this->children[$name])) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->resolveChildren();

        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        return count($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockConfig()
    {
        /* @var BlockBuilder $config */
        $config = parent::getBlockConfig();

        $config->children = array();
        $config->unresolvedChildren = array();

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlock()
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->resolveChildren();

        $block = new Block($this->getBlockConfig());

        /* @var BlockBuilderInterface $child */
        foreach ($this->children as $child) {
            $child->setAutoInitialize(false);
            $block->add($child->getBlock());
        }

        if ($this->getAutoInitialize()) {
            // Automatically initialize the block if it is configured so
            $block->initialize();
        }

        return $block;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        return new \ArrayIterator($this->all());
    }

    /**
     * Converts an unresolved child into a {@link BlockBuilder} instance.
     *
     * @param string $name The name of the unresolved child
     *
     * @return BlockBuilder The created instance
     */
    private function resolveChild($name)
    {
        $info = $this->unresolvedChildren[$name];
        $child = $this->create($name, $info['type'], $info['options']);
        $this->children[$name] = $child;
        unset($this->unresolvedChildren[$name]);

        return $child;
    }

    /**
     * Converts all unresolved children into {@link BlockBuilder} instances.
     */
    private function resolveChildren()
    {
        foreach ($this->unresolvedChildren as $name => $info) {
            $this->children[$name] = $this->create($name, $info['type'], $info['options']);
        }

        $this->unresolvedChildren = array();
    }
}
