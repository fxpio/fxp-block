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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockConfigBuilderInterface extends BlockConfigInterface
{
    /**
     * Adds an event listener to an event on this block.
     *
     * @param string   $eventName The name of the event to listen to
     * @param callable $listener  The listener to execute
     * @param int      $priority  The priority of the listener. Listeners
     *                            with a higher priority are called before
     *                            listeners with a lower priority
     *
     * @return self The configuration object
     */
    public function addEventListener($eventName, $listener, $priority = 0);

    /**
     * Adds an event subscriber for events on this block.
     *
     * @param EventSubscriberInterface $subscriber The subscriber to attach
     *
     * @return self The configuration object
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber);

    /**
     * Appends / prepends a transformer to the view transformer chain.
     *
     * The transform method of the transformer is used to convert data block the
     * normalized to the view format.
     *
     * @param DataTransformerInterface $viewTransformer
     * @param bool                     $forcePrepend    if set to true, prepend instead of appending
     *
     * @return self The configuration object
     */
    public function addViewTransformer(DataTransformerInterface $viewTransformer, $forcePrepend = false);

    /**
     * Clears the view transformers.
     *
     * @return self The configuration object
     */
    public function resetViewTransformers();

    /**
     * Prepends / appends a transformer to the normalization transformer chain.
     *
     * The transform method of the transformer is used to convert data from the
     * model to the normalized format.
     *
     * @param DataTransformerInterface $modelTransformer
     * @param bool                     $forceAppend      if set to true, append instead of prepending
     *
     * @return self The configuration object
     */
    public function addModelTransformer(DataTransformerInterface $modelTransformer, $forceAppend = false);

    /**
     * Clears the normalization transformers.
     *
     * @return self The configuration object
     */
    public function resetModelTransformers();

    /**
     * Sets the value for an attribute.
     *
     * @param string $name  The name of the attribute
     * @param string $value The value of the attribute
     *
     * @return self The configuration object
     */
    public function setAttribute($name, $value);

    /**
     * Sets the attributes.
     *
     * @param array $attributes The attributes
     *
     * @return self The configuration object
     */
    public function setAttributes(array $attributes);

    /**
     * Sets the data mapper used by the block.
     *
     * @param DataMapperInterface $dataMapper
     *
     * @return self The configuration object
     */
    public function setDataMapper(DataMapperInterface $dataMapper = null);

    /**
     * Sets the data used for the client data when no value is bound.
     *
     * @param mixed $emptyData The empty data
     *
     * @return self The configuration object
     */
    public function setEmptyData($emptyData);

    /**
     * Sets the message used for the client view when no value is bound.
     *
     * @param mixed $emptyMessage The empty message
     *
     * @return self The configuration object
     */
    public function setEmptyMessage($emptyMessage);

    /**
     * Sets the property path that the block should be mapped to.
     *
     * @param null|string|\Symfony\Component\PropertyAccess\PropertyPathInterface $propertyPath The property path or null if the path should be set
     *                                                                                          automatically based on the block's name
     *
     * @return self The configuration object
     */
    public function setPropertyPath($propertyPath);

    /**
     * Sets whether the block should be mapped to an element of its
     * parent's data.
     *
     * @param bool $mapped Whether the block should be mapped
     *
     * @return self The configuration object
     */
    public function setMapped($mapped);

    /**
     * Sets whether the block should be inherit data.
     *
     * @param bool $inheritData Whether the block should be inherit data
     *
     * @return self The configuration object
     */
    public function setInheritData($inheritData);

    /**
     * Sets whether the block should be compound.
     *
     * @param bool $compound Whether the block should be compound
     *
     * @return self The configuration object
     *
     * @see BlockConfigInterface::getCompound()
     */
    public function setCompound($compound);

    /**
     * Set the types.
     *
     * @param ResolvedBlockTypeInterface $type The type of the block
     *
     * @return self The configuration object
     */
    public function setType(ResolvedBlockTypeInterface $type);

    /**
     * Sets the initial data of the block.
     *
     * @param mixed $data The data of the block in application format
     *
     * @return self The configuration object
     */
    public function setData($data);

    /**
     * Sets the initial data class of the block.
     *
     * @param string $dataClass The data class of the block in application format
     *
     * @return self The configuration object
     */
    public function setDataClass($dataClass);

    /**
     * Sets the initial form of the block.
     *
     * @param FormInterface $form The form of the block
     *
     * @return self The configuration object
     */
    public function setForm(FormInterface $form);

    /**
     * Sets whether the block should be initialized automatically.
     *
     * Should be set to true only for root blocks.
     *
     * @param bool $initialize True to initialize the block automatically,
     *                         false to suppress automatic initialization.
     *                         In the second case, you need to call
     *                         {@link BlockInterface::initialize()} manually
     *
     * @return self The configuration object
     */
    public function setAutoInitialize($initialize);

    /**
     * Builds and returns the block configuration.
     *
     * @return BlockConfigInterface
     */
    public function getBlockConfig();
}
