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

use Symfony\Component\Form\FormInterface;

/**
 * A block group bundling multiple block views.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockInterface extends \ArrayAccess, \Traversable, \Countable
{
    /**
     * Sets the parent block.
     *
     * @param BlockInterface $parent The parent block
     *
     * @return BlockInterface The block instance
     */
    public function setParent(BlockInterface $parent = null);

    /**
     * Returns the parent block.
     *
     * @return BlockInterface The parent block
     */
    public function getParent();

    /**
     * Returns whether the block has a parent.
     *
     * @return bool
     */
    public function hasParent();

    /**
     * Sets the value for an option.
     *
     * @param string $name  The name of the option
     * @param string $value The value of the option
     *
     * @return BlockInterface The block instance
     */
    public function setOption($name, $value);

    /**
     * Sets the options.
     *
     * @param array $options The options
     *
     * @return BlockInterface The block instance
     */
    public function setOptions(array $options);

    /**
     * Returns all options of this Block instance.
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

    /**
     * Sets the value for an attribute.
     *
     * @param string $name  The name of the attribute
     * @param string $value The value of the attribute
     *
     * @return BlockInterface The block instance
     */
    public function setAttribute($name, $value);

    /**
     * Sets the attributes.
     *
     * @param array $attributes The attributes
     *
     * @return BlockInterface The block instance
     */
    public function setAttributes(array $attributes);

    /**
     * Returns all attributes of this Block instance.
     *
     * @return array The passed attributes
     */
    public function getAttributes();

    /**
     * Returns whether a specific attribute exists.
     *
     * @param string $name The attribute name,
     *
     * @return bool Whether the attribute exists
     */
    public function hasAttribute($name);

    /**
     * Returns the value of a specific attribute.
     *
     * @param string $name    The attribute name
     * @param mixed  $default The value returned if the attribute does not exist
     *
     * @return mixed The attribute value
     */
    public function getAttribute($name, $default = null);

    /**
     * Adds a child to the block.
     *
     * @param BlockInterface|string|int $child   The BlockInterface instance or the name of the child
     * @param string|null               $type    The child's type, if a name was passed
     * @param array                     $options The child's options, if a name was passed
     *
     * @return BlockInterface The block instance
     *
     * @throws Exception\UnexpectedTypeException If $child or $type has an unexpected type
     */
    public function add($child, $type = null, array $options = array());

    /**
     * Returns the child with the given name.
     *
     * @param string $name The name of the child
     *
     * @return BlockInterface The child block
     */
    public function get($name);

    /**
     * Returns whether a child with the given name exists.
     *
     * @param string $name The name of the child
     *
     * @return bool
     */
    public function has($name);

    /**
     * Removes a child block the block.
     *
     * @param string $name The name of the child to remove
     *
     * @return BlockInterface The block instance
     */
    public function remove($name);

    /**
     * Returns all children in this group.
     *
     * @return array An array of BlockInterface instances
     */
    public function all();

    /**
     * Updates the field with default data.
     *
     * @param mixed $modelData The data formatted as expected for the underlying object
     *
     * @return BlockInterface The block instance
     */
    public function setData($modelData);

    /**
     * Returns the data in the format needed for the underlying object.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Returns the normalized data of the field.
     *
     * @return mixed When the field is not bound, the default data is returned.
     *               When the field is bound, the normalized bound data is
     *               returned if the field is valid, null otherwise
     */
    public function getNormData();

    /**
     * Returns the data transformed by the value transformer.
     *
     * @return string
     */
    public function getViewData();

    /**
     * Sets the data class of the block.
     *
     * @param string $dataClass The data class of the block in application format
     *
     * @return BlockInterface The child block
     */
    public function setDataClass($dataClass);

    /**
     * Returns the class of the block data or null if the data is scalar or an array.
     *
     * @return string The data class or null
     */
    public function getDataClass();

    /**
     * Returns the block's configuration.
     *
     * @return BlockConfigInterface The configuration
     */
    public function getConfig();

    /**
     * Returns the name by which the block is identified in blocks.
     *
     * @return string The name of the block
     */
    public function getName();

    /**
     * Returns the property path that the block is mapped to.
     *
     * @return \Symfony\Component\PropertyAccess\PropertyPathInterface The property path
     */
    public function getPropertyPath();

    /**
     * Returns whether the block is empty.
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Initializes the block tree.
     *
     * Should be called on the root block after constructing the tree.
     *
     * @return BlockInterface The block instance
     */
    public function initialize();

    /**
     * Returns the root of the block tree.
     *
     * @return BlockInterface The root of the tree
     */
    public function getRoot();

    /**
     * Returns whether the field is the root of the block tree.
     *
     * @return bool
     */
    public function isRoot();

    /**
     * Returns the form.
     *
     * @param FormInterface $form
     *
     * @return BlockInterface The block instance
     */
    public function setForm(FormInterface $form);

    /**
     * Returns the form.
     *
     * @return FormInterface
     */
    public function getForm();

    /**
     * Creates a block.
     *
     * @param BlockView $parent The parent block
     *
     * @return BlockView The block
     */
    public function createView(BlockView $parent = null);
}
