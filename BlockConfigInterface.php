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
 * The configuration of a {@link Block} object.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface BlockConfigInterface
{
    /**
     * Returns the event dispatcher used to dispatch block events.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface The dispatcher
     */
    public function getEventDispatcher();

    /**
     * Returns the name of the block used as HTTP parameter.
     *
     * @return string The block name
     */
    public function getName();

    /**
     * Returns the property path that the block should be mapped to.
     *
     * @return \Symfony\Component\PropertyAccess\PropertyPathInterface The property path
     */
    public function getPropertyPath();

    /**
     * Returns whether the block should be mapped to an element of its
     * parent's data.
     *
     * @return bool Whether the block is mapped
     */
    public function getMapped();

    /**
     * Returns whether the block should be inherit data.
     *
     * When mapping data to the children of a block, the data mapper
     * should ignore inherit data blocks and map to the children of the
     * inherit data block instead.
     *
     * @return bool Whether the block is inherit data
     */
    public function getInheritData();

    /**
     * Returns whether the block is compound.
     *
     * This property is independent of whether the block actually has
     * children. A block can be compound and have no children at all, like
     * for example an empty collection block.
     *
     * @return bool Whether the block is compound
     */
    public function getCompound();

    /**
     * Returns the block types used to construct the block.
     *
     * @return ResolvedBlockTypeInterface The block's type
     */
    public function getType();

    /**
     * Returns the block transformers of the block.
     *
     * @return array An array of {@link DataTransformerInterface} instances
     */
    public function getViewTransformers();

    /**
     * Returns the model transformers of the block.
     *
     * @return array An array of {@link DataTransformerInterface} instances
     */
    public function getModelTransformers();

    /**
     * Returns the data mapper of the block.
     *
     * @return DataMapperInterface The data mapper
     */
    public function getDataMapper();

    /**
     * Returns the data that should be returned when the block is empty.
     *
     * @return mixed The data returned if the block is empty
     */
    public function getEmptyData();

    /**
     * Returns the message that should be returned when the view value of block is empty.
     *
     * @return mixed The message returned if the view value of block is empty
     */
    public function getEmptyMessage();

    /**
     * Returns additional attributes of the block.
     *
     * @return array An array of key-value combinations
     */
    public function getAttributes();

    /**
     * Returns whether the attribute with the given name exists.
     *
     * @param string $name The attribute name
     *
     * @return bool Whether the attribute exists
     */
    public function hasAttribute($name);

    /**
     * Returns the value of the given attribute.
     *
     * @param string $name    The attribute name
     * @param mixed  $default The value returned if the attribute does not exist
     *
     * @return mixed The attribute value
     */
    public function getAttribute($name, $default = null);

    /**
     * Returns the initial data of the block.
     *
     * @return mixed The initial block data
     */
    public function getData();

    /**
     * Returns the class of the block data or null if the data is scalar or an array.
     *
     * @return string The data class or null
     */
    public function getDataClass();

    /**
     * Returns the form of the block or null if the block don't have a form.
     *
     * @return \Symfony\Component\Form\FormInterface The form or null
     */
    public function getForm();

    /**
     * Returns whether the block should be initialized upon creation.
     *
     * @return bool Returns true if the block should be initialized
     *              when created, false otherwise
     */
    public function getAutoInitialize();

    /**
     * Returns all options passed during the construction of the block.
     *
     * @return array The passed options
     */
    public function getOptions();

    /**
     * Returns whether a specific option exists.
     *
     * @param string $name The option name,
     *
     * @return bool Whether the option exists
     */
    public function hasOption($name);

    /**
     * Returns the value of a specific option.
     *
     * @param string $name    The option name
     * @param mixed  $default The value returned if the option does not exist
     *
     * @return mixed The option value
     */
    public function getOption($name, $default = null);
}
