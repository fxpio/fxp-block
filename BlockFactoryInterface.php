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

use Sonatra\Component\Block\Extension\Core\Type\BlockType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface BlockFactoryInterface
{
    /**
     * Returns a block.
     *
     * @see createBuilder()
     *
     * @param string $type    The type of the block
     * @param mixed  $data    The initial data
     * @param array  $options The options
     *
     * @return BlockInterface The block named after the type
     *
     * @throws Exception\UnexpectedTypeException                                    if any given option is not applicable to the given type
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException if any given option is not applicable to the given type
     */
    public function create($type = BlockType::class, $data = null, array $options = array());

    /**
     * Returns a block.
     *
     * @see createNamedBuilder()
     *
     * @param string $name    The name of the block
     * @param string $type    The type of the block
     * @param mixed  $data    The initial data
     * @param array  $options The options
     *
     * @return BlockInterface The block
     *
     * @throws Exception\UnexpectedTypeException                                    if any given option is not applicable to the given type
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException if any given option is not applicable to the given type
     */
    public function createNamed($name, $type = BlockType::class, $data = null, array $options = array());

    /**
     * Returns a block for a property of a class.
     *
     * @see createBuilderForProperty()
     *
     * @param string $class    The fully qualified class name
     * @param string $property The name of the property to guess for
     * @param mixed  $data     The initial data
     * @param array  $options  The options for the builder
     *
     * @return BlockInterface The block named after the property
     *
     * @throws Exception\UnexpectedTypeException                                    if any given option is not applicable to the block type
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException if any given option is not applicable to the given type
     */
    public function createForProperty($class, $property, $data = null, array $options = array());

    /**
     * Returns a block builder.
     *
     * @param string $type    The type of the block
     * @param mixed  $data    The initial data
     * @param array  $options The options
     *
     * @return BlockBuilderInterface The block builder
     *
     * @throws Exception\UnexpectedTypeException                                    if any given option is not applicable to the given type
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException if any given option is not applicable to the given type
     */
    public function createBuilder($type = BlockType::class, $data = null, array $options = array());

    /**
     * Returns a block builder.
     *
     * @param string $name    The name of the block
     * @param string $type    The type of the block
     * @param mixed  $data    The initial data
     * @param array  $options The options
     *
     * @return BlockBuilderInterface The block builder
     *
     * @throws Exception\UnexpectedTypeException                                    if any given option is not applicable to the given type
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException if any given option is not applicable to the given type
     */
    public function createNamedBuilder($name, $type = BlockType::class, $data = null, array $options = array());

    /**
     * Returns a block builder for a property of a class.
     *
     * If any type options can be guessed, and are not provided in the options
     * argument, the guessed value is used.
     *
     * @param string $class    The fully qualified class name
     * @param string $property The name of the property to guess for
     * @param mixed  $data     The initial data
     * @param array  $options  The options for the builder
     *
     * @return BlockBuilderInterface The block builder named after the property
     *
     * @throws Exception\UnexpectedTypeException                                    if any given option is not applicable to the block type
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException if any given option is not applicable to the given type
     */
    public function createBuilderForProperty($class, $property, $data = null, array $options = array());
}
