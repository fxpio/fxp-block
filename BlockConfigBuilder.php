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

use Sonatra\Component\Block\Exception\BadMethodCallException;
use Sonatra\Component\Block\Exception\InvalidArgumentException;
use Sonatra\Component\Block\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\ImmutableEventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * A basic block configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockConfigBuilder implements BlockConfigBuilderInterface
{
    /**
     * @var bool
     */
    protected $locked = false;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyPathInterface
     */
    private $propertyPath;

    /**
     * @var bool
     */
    private $mapped = true;

    /**
     * @var bool
     */
    private $inheritData = false;

    /**
     * @var bool
     */
    private $compound = false;

    /**
     * @var ResolvedBlockTypeInterface
     */
    private $type;

    /**
     * @var array
     */
    private $viewTransformers = array();

    /**
     * @var array
     */
    private $modelTransformers = array();

    /**
     * @var DataMapperInterface
     */
    private $dataMapper;

    /**
     * @var mixed
     */
    private $emptyData;

    /**
     * @var string
     */
    private $emptyMessage;

    /**
     * @var array
     */
    private $attributes = array();

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $dataClass;

    /**
     * @var Form
     */
    private $form;

    /**
     * @var bool
     */
    private $autoInitialize = false;

    /**
     * @var array
     */
    private $options;

    /**
     * Creates an empty form configuration.
     *
     * @param string                   $name       The block name
     * @param string                   $dataClass  The class of the block's data
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     * @param array                    $options    The block options
     *
     * @throws UnexpectedTypeException  If the name is not a string
     * @throws InvalidArgumentException If the data class is not a valid class or if
     *                                  the name contains invalid characters
     */
    public function __construct($name, $dataClass, EventDispatcherInterface $dispatcher, array $options = array())
    {
        self::validateName($name);

        if (null !== $dataClass && !class_exists($dataClass) && !interface_exists($dataClass)) {
            throw new InvalidArgumentException(sprintf('The data class "%s" is not a valid class.', $dataClass));
        }

        $this->name = $name;
        $this->dataClass = $dataClass;
        $this->dispatcher = $dispatcher;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function addEventListener($eventName, $listener, $priority = 0)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->dispatcher->addListener($eventName, $listener, $priority);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->dispatcher->addSubscriber($subscriber);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addViewTransformer(DataTransformerInterface $viewTransformer, $forcePrepend = false)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        if ($forcePrepend) {
            array_unshift($this->viewTransformers, $viewTransformer);
        } else {
            $this->viewTransformers[] = $viewTransformer;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetViewTransformers()
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->viewTransformers = array();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addModelTransformer(DataTransformerInterface $modelTransformer, $forceAppend = false)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        if ($forceAppend) {
            $this->modelTransformers[] = $modelTransformer;
        } else {
            array_unshift($this->modelTransformers, $modelTransformer);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetModelTransformers()
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->modelTransformers = array();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventDispatcher()
    {
        if ($this->locked && !$this->dispatcher instanceof ImmutableEventDispatcher) {
            $this->dispatcher = new ImmutableEventDispatcher($this->dispatcher);
        }

        return $this->dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getMapped()
    {
        return $this->mapped;
    }

    /**
     * {@inheritdoc}
     */
    public function getInheritData()
    {
        return $this->inheritData;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompound()
    {
        return $this->compound;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewTransformers()
    {
        return $this->viewTransformers;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelTransformers()
    {
        return $this->modelTransformers;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataMapper()
    {
        return $this->dataMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmptyData()
    {
        return $this->emptyData;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmptyMessage()
    {
        return $this->emptyMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        return $this->hasAttribute($name) ? $this->attributes[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataClass()
    {
        return $this->dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoInitialize()
    {
        return $this->autoInitialize;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        return $this->hasOption($name) ? $this->options[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($name, $value)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->attributes = $attributes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataMapper(DataMapperInterface $dataMapper = null)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->dataMapper = $dataMapper;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmptyData($emptyData)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->emptyData = $emptyData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmptyMessage($emptyMessage)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->emptyMessage = $emptyMessage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPropertyPath($propertyPath)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        if (null !== $propertyPath && !$propertyPath instanceof PropertyPathInterface) {
            $propertyPath = new PropertyPath($propertyPath);
        }

        $this->propertyPath = $propertyPath;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMapped($mapped)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->mapped = $mapped;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setInheritData($inheritData)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->inheritData = $inheritData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCompound($compound)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->compound = $compound;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setType(ResolvedBlockTypeInterface $type)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        if (null !== $this->getForm()) {
            $this->getForm()->setData($data);
        }

        $this->data = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataClass($dataClass)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->dataClass = $dataClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setForm(FormInterface $form)
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        $this->form = $form;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAutoInitialize($initialize)
    {
        $this->autoInitialize = (bool) $initialize;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockConfig()
    {
        if ($this->locked) {
            throw new BadMethodCallException('BlockConfigBuilder methods cannot be accessed anymore once the builder is turned into a BlockConfigInterface instance.');
        }

        // This method should be idempotent, so clone the builder
        $config = clone $this;
        $config->locked = true;

        return $config;
    }

    /**
     * Validates whether the given variable is a valid block name.
     *
     * @param string $name The tested block name
     *
     * @throws UnexpectedTypeException  If the name is not a string
     * @throws InvalidArgumentException If the name contains invalid characters
     */
    public static function validateName($name)
    {
        if (null !== $name && !is_string($name) && !is_int($name)) {
            throw new UnexpectedTypeException($name, 'string, integer or null');
        }

        if (!self::isValidName($name)) {
            throw new InvalidArgumentException(sprintf(
                'The name "%s" contains illegal characters. Names should start with a letter, digit or underscore and only contain letters, digits, numbers, underscores ("_"), hyphens ("-") and colons (":").',
                $name
            ));
        }
    }

    /**
     * Returns whether the given variable contains a valid block name.
     *
     * A name is accepted if it
     *
     *   * is empty
     *   * starts with a letter, digit or underscore
     *   * contains only letters, digits, numbers, underscores ("_"),
     *     hyphens ("-") and colons (":")
     *
     * @param string $name The tested block name
     *
     * @return bool Whether the name is valid
     */
    public static function isValidName($name)
    {
        return '' === $name || null === $name || preg_match('/^[a-zA-Z0-9_][a-zA-Z0-9_\-:]*$/D', $name);
    }
}
