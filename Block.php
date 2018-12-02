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
use Fxp\Component\Block\Exception\LogicException;
use Fxp\Component\Block\Exception\RuntimeException;
use Fxp\Component\Block\Exception\TransformationFailedException;
use Fxp\Component\Block\Exception\UnexpectedTypeException;
use Fxp\Component\Block\Util\BlockUtil;
use Fxp\Component\Block\Util\InheritDataAwareIterator;
use Fxp\Component\Block\Util\OrderedHashMap;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Block implements \IteratorAggregate, BlockInterface
{
    /**
     * The block's configuration.
     *
     * @var BlockConfigInterface
     */
    protected $config;

    /**
     * The parent of this block.
     *
     * @var BlockInterface
     */
    protected $parent;

    /**
     * The options of this block.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The attributes of this block.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The children of this block.
     *
     * @var BlockInterface[] A map of BlockInterface instances
     */
    protected $children;

    /**
     * The block data in model format.
     *
     * @var mixed
     */
    protected $modelData;

    /**
     * The block data in normalized format.
     *
     * @var mixed
     */
    protected $normData;

    /**
     * The block data in view format.
     *
     * @var mixed
     */
    protected $viewData;

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * Whether the block's data has been initialized.
     *
     * When the data is initialized with its default value, that default value
     * is passed through the transformer chain in order to synchronize the
     * model, normalized and block format for the first time. This is done
     * lazily in order to save performance when {@link setData()} is called
     * manually, making the initialization with the configured default value
     * superfluous.
     *
     * @var bool
     */
    protected $defaultDataSet = false;

    /**
     * Whether setData() is currently being called.
     *
     * @var bool
     */
    protected $lockSetData = false;

    /**
     * Creates a new block based on the given configuration.
     *
     * @param BlockConfigInterface $config The block configuration
     *
     * @throws LogicException When compound block has not a data mapper
     */
    public function __construct(BlockConfigInterface $config)
    {
        if ($config->getCompound() && !$config->getDataMapper()) {
            throw new LogicException('Compound blocks need a data mapper');
        }

        if ($config->getInheritData()) {
            $this->defaultDataSet = true;
        }

        $this->config = $config;
        $this->children = new OrderedHashMap();
        $this->options = $config->getOptions();
        $this->attributes = $config->getAttributes();
        $this->form = $config->getForm();
    }

    public function __clone()
    {
        $this->children = clone $this->children;

        foreach ($this->children as $key => $child) {
            $this->children[$key] = clone $child;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->config->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyPath()
    {
        if (null !== $this->config->getPropertyPath()) {
            return $this->config->getPropertyPath();
        }

        if (null === $this->getName() || '' === $this->getName()) {
            $empty = null;

            return $empty;
        }

        $parent = $this->parent;

        while ($parent && $parent->getConfig()->getInheritData()) {
            $parent = $parent->getParent();
        }

        if ($parent && null === $parent->getConfig()->getDataClass()) {
            return new PropertyPath('['.$this->getName().']');
        }

        return new PropertyPath($this->getName());
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException When block with an empty name has a parent block
     */
    public function setParent(BlockInterface $parent = null)
    {
        if (null !== $parent && '' === $this->config->getName()) {
            throw new LogicException('A block with an empty name cannot have a parent block.');
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParent()
    {
        return null !== $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        return $this->parent ? $this->parent->getRoot() : $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRoot()
    {
        return !$this->hasParent();
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        $this->setOptions([$name => $value]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $type = $this->getConfig()->getType();
        $rOptions = $options;

        if (null !== $type) {
            $rOptions = $type->getOptionsResolver()->resolve($options);
        }

        foreach ($options as $name => $value) {
            if (array_key_exists($name, $rOptions)) {
                $this->options[$name] = $options[$name];
            }
        }

        return $this;
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
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
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
    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
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
     *
     * @throws RuntimeException
     * @throws LogicException
     */
    public function setData($modelData)
    {
        if ($this->config->getInheritData()) {
            throw new RuntimeException('You cannot change the data of a block inheriting its parent data.');
        }

        if ($this->lockSetData) {
            throw new RuntimeException('A cycle was detected. Listeners to the PRE_SET_DATA event must not call setData(). You should call setData() on the BlockEvent object instead.');
        }

        if (null !== $this->getForm() && null === $this->getForm()->getData()) {
            $this->getForm()->setData($modelData);
        }

        $this->lockSetData = true;
        $dispatcher = $this->config->getEventDispatcher();

        // Hook to change content of the data
        if ($dispatcher->hasListeners(BlockEvents::PRE_SET_DATA)) {
            $event = new BlockEvent($this, $modelData);
            $dispatcher->dispatch(BlockEvents::PRE_SET_DATA, $event);
            $modelData = $event->getData();
        }

        if (BlockUtil::isEmpty($modelData)) {
            $emptyData = $this->config->getEmptyData();

            if ($emptyData instanceof \Closure) {
                /* @var \Closure $emptyData */
                $emptyData = $emptyData($this, $modelData, $this->getOptions());
            }

            $modelData = $emptyData;
        }

        // Treat data as strings unless a value transformer exists
        if (!$this->config->getViewTransformers() && !$this->config->getModelTransformers() && is_scalar($modelData) && !\is_bool($modelData)) {
            $modelData = (string) $modelData;
        }

        // Synchronize representations - must not change the content!
        $normData = $this->modelToNorm($modelData);
        $viewData = $this->normToView($normData);

        // Validate if view data matches data class (unless empty)
        if (!BlockUtil::isEmpty($viewData)) {
            $dataClass = $this->getDataClass();

            $actualType = \is_object($viewData) ? 'an instance of class '.\get_class($viewData) : ' a(n) '.\gettype($viewData);

            if (null === $dataClass && \is_object($viewData) && !$viewData instanceof \ArrayAccess) {
                $expectedType = 'scalar, array or an instance of \ArrayAccess';

                throw new LogicException(
                        'The block\'s view data is expected to be of type '.$expectedType.', '.
                        'but is '.$actualType.'. You '.
                        'can avoid this error by setting the "data_class" option to '.
                        '"'.\get_class($viewData).'" or by adding a view transformer '.
                        'that transforms '.$actualType.' to '.$expectedType.'.'
                );
            }

            if (null !== $dataClass && !$viewData instanceof $dataClass) {
                throw new LogicException(
                        'The block\'s view data is expected to be an instance of class '.
                        $dataClass.', but is '.$actualType.'. You can avoid this error '.
                        'by setting the "data_class" option to null or by adding a view '.
                        'transformer that transforms '.$actualType.' to an instance of '.
                        $dataClass.'.'
                );
            }
        } else {
            $viewData = (string) $this->config->getEmptyMessage();
        }

        $this->modelData = $modelData;
        $this->normData = $normData;
        $this->viewData = $viewData;
        $this->defaultDataSet = true;
        $this->lockSetData = false;

        if (\count($this->children) > 0) {
            $childrenIterator = new InheritDataAwareIterator($this->children);
            $childrenIterator = new \RecursiveIteratorIterator($childrenIterator);
            $this->config->getDataMapper()->mapDataToViews($viewData, $childrenIterator);
        }

        if ($dispatcher->hasListeners(BlockEvents::POST_SET_DATA)) {
            $event = new BlockEvent($this, $modelData);
            $dispatcher->dispatch(BlockEvents::POST_SET_DATA, $event);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function getData()
    {
        if ($this->config->getInheritData()) {
            if (!$this->parent) {
                throw new RuntimeException('The block is configured to inherit its parent\'s data, but does not have a parent.');
            }

            return $this->parent->getData();
        }

        if (!$this->defaultDataSet) {
            $this->setData($this->config->getData());
        }

        return $this->modelData;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function getNormData()
    {
        if ($this->config->getInheritData()) {
            if (!$this->parent) {
                throw new RuntimeException('The block is configured to inherit its parent\'s data, but does not have a parent.');
            }

            return $this->parent->getNormData();
        }

        if (!$this->defaultDataSet) {
            $this->setData($this->config->getData());
        }

        return $this->normData;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function getViewData()
    {
        if ($this->config->getInheritData()) {
            if (!$this->parent) {
                throw new RuntimeException('The block is configured to inherit its parent\'s data, but does not have a parent.');
            }

            return $this->parent->getViewData();
        }

        if (!$this->defaultDataSet) {
            $this->setData($this->config->getData());
        }

        return $this->viewData;
    }

    /**
     * {@inheritdoc}
     *
     * Guarantee that the *_SET_DATA events have been triggered once the
     * block is initialized. This makes sure that dynamically added or
     * removed fields are already visible after initialization.
     */
    public function initialize()
    {
        if (null !== $this->parent) {
            throw new RuntimeException('Only root blocks should be initialized.');
        }

        if (!$this->defaultDataSet) {
            $this->setData($this->config->getData());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataClass()
    {
        if (null === $this->dataClass) {
            return $this->config->getDataClass();
        }

        return $this->dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        foreach ($this->children as $child) {
            if (!$child->isEmpty()) {
                return false;
            }
        }

        return BlockUtil::isEmpty($this->modelData) ||
            (($this->modelData instanceof \Countable || \is_array($this->modelData))
                && 0 === \count($this->modelData)) ||
            ($this->modelData instanceof \Traversable && 0 === iterator_count($this->modelData));
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return iterator_to_array($this->children);
    }

    /**
     * {@inheritdoc}
     *
     * If setData() is currently being called, there is no need to call
     * mapDataToViews() here, as mapDataToViews() is called at the end
     * of setData() anyway. Not doing this check leads to an endless
     * recursion when initializing the block lazily and an event listener
     * (such as ResizeBlockListener) adds fields depending on the data:
     *
     * setData() is called, the block is not initialized yet
     * add() is called by the listener (setData() is not complete, so
     * the block is still not initialized)
     * getViewData() is called
     * setData() is called since the block is not initialized yet
     * ... endless recursion ...
     */
    public function add($child, $type = null, array $options = [])
    {
        if (!$this->config->getCompound()) {
            throw new LogicException('You cannot add children to a simple block. Maybe you should set the option "compound" to true?');
        }

        $viewData = null;

        if (!$this->lockSetData && $this->defaultDataSet && !$this->config->getInheritData()) {
            $viewData = $this->getViewData();
        }

        if (!$child instanceof BlockInterface) {
            if (null !== $child && !\is_string($child) && !\is_int($child)) {
                throw new UnexpectedTypeException($child, 'string, integer or Fxp\Component\Block\BlockInterface');
            }

            if (null !== $type && !\is_string($type) && !$type instanceof BlockTypeInterface) {
                throw new UnexpectedTypeException($type, 'string or Fxp\Component\Block\BlockTypeInterface');
            }

            // Never initialize child blocks automatically
            $options['auto_initialize'] = false;

            /* @var BlockBuilder $config */
            $config = $this->config;

            if (null === $type) {
                $child = $config->getBlockFactory()->createForProperty($this->getDataClass(), $child, null, $options);
            } elseif (null === $child) {
                $child = $config->getBlockFactory()->create($type, null, $options);
            } else {
                $child = $config->getBlockFactory()->createNamed($child, $type, null, $options);
            }
        }

        $child->setParent($this);

        if (null !== $child->getConfig()->getType()) {
            $child->getConfig()->getType()->addParent($this, $child, $child->getOptions());
            $this->getConfig()->getType()->addChild($child, $this, $this->getOptions());
        }

        if ($child->getParent() === $this) {
            $this->children[$child->getName()] = $child;
        }

        if (!$this->lockSetData && $this->defaultDataSet && !$this->config->getInheritData()) {
            $childrenIterator = new InheritDataAwareIterator(new \ArrayIterator([$child]));
            $childrenIterator = new \RecursiveIteratorIterator($childrenIterator);
            $this->config->getDataMapper()->mapDataToViews($viewData, $childrenIterator);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        if (isset($this->children[$name])) {
            $child = $this->children[$name];

            unset($this->children[$name]);

            if (null !== $child->getConfig()->getType()) {
                $this->getConfig()->getType()->removeChild($child, $this, $this->getOptions());
                $child->getConfig()->getType()->removeParent($this, $child, $child->getOptions());
            }

            $child->setParent(null);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->children[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (isset($this->children[$name])) {
            return $this->children[$name];
        }

        throw new InvalidArgumentException(sprintf('Child "%s" does not exist.', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($name, $child)
    {
        $this->add($child);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function createView(BlockView $parent = null)
    {
        if (null === $parent && $this->parent) {
            $parent = $this->parent->createView();
        }

        $type = $this->config->getType();
        $options = $this->getOptions();

        // The methods createView(), buildView() and finishView() are called
        // explicitly here in order to be able to override either of them
        // in a custom resolved block type.
        $view = $type->createView($this, $parent);

        $type->buildView($view, $this, $options);

        foreach ($this->children as $name => $child) {
            $view->children[$name] = $child->createView($view);
        }

        $type->finishView($view, $this, $options);

        return $view;
    }

    /**
     * Normalizes the value if a normalization transformer is set.
     *
     * @param mixed $value The value to transform
     *
     * @return string
     *
     * @throws TransformationFailedException If the value cannot be transformed to "normalized" format
     */
    protected function modelToNorm($value)
    {
        try {
            /* @var DataTransformerInterface $transformer */
            foreach ($this->config->getModelTransformers() as $transformer) {
                $value = $transformer->transform($value);
            }
        } catch (TransformationFailedException $exception) {
            throw new TransformationFailedException(
                'Unable to transform value for property path "'.$this->getPropertyPath().'": '.$exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return $value;
    }

    /**
     * Transforms the value if a value transformer is set.
     *
     * Note:
     *
     * Scalar values should  be converted to strings to
     * facilitate differentiation between empty ("") and zero (0).
     * Only do this for simple blocks, as the resulting value in
     * compound blocks is passed to the data mapper and thus should
     * not be converted to a string before.
     *
     * @param mixed $value The value to transform
     *
     * @return string
     *
     * @throws TransformationFailedException If the value cannot be transformed to "view" format
     */
    protected function normToView($value)
    {
        try {
            /* @var DataTransformerInterface $transformer */
            foreach ($this->config->getViewTransformers() as $transformer) {
                $value = $transformer->transform($value);
            }
        } catch (TransformationFailedException $exception) {
            throw new TransformationFailedException(
                'Unable to transform value for property path "'.$this->getPropertyPath().'": '.$exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        if (!$this->config->getCompound()) {
            return null === $value || is_scalar($value) ? (string) $value : $value;
        }

        return $value;
    }
}
