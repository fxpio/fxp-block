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
 * A wrapper for a block type and its extensions.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface ResolvedBlockTypeInterface extends BlockTypeCommonInterface
{
    /**
     * Returns the prefix of the template block name for this type.
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix();

    /**
     * Returns the parent type.
     *
     * @return ResolvedBlockTypeInterface The parent type or null
     */
    public function getParent();

    /**
     * Returns the wrapped block type.
     *
     * @return BlockTypeInterface The wrapped block type
     */
    public function getInnerType();

    /**
     * Returns the extensions of the wrapped block type.
     *
     * @return BlockTypeExtensionInterface[] An array of {@link BlockTypeExtensionInterface} instances
     */
    public function getTypeExtensions();

    /**
     * Creates a new block builder for this type.
     *
     * @param BlockFactoryInterface $factory The block factory
     * @param string                $name    The name for the builder
     * @param array                 $options The builder options
     *
     * @return BlockBuilderInterface The created block builder
     */
    public function createBuilder(BlockFactoryInterface $factory, $name, array $options = []);

    /**
     * Creates a new block view for a block of this type.
     *
     * @param BlockInterface $block  The block to create a block view for
     * @param BlockView      $parent The parent block view or null
     *
     * @return BlockView The created block view
     */
    public function createView(BlockInterface $block, BlockView $parent = null);

    /**
     * Returns the configured options resolver used for this type.
     *
     * @return \Symfony\Component\OptionsResolver\OptionsResolver The options resolver
     */
    public function getOptionsResolver();
}
