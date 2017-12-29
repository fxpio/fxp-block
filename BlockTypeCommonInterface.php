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
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface BlockTypeCommonInterface
{
    /**
     * Builds the block.
     *
     * This method is called for each type in the hierarchy starting block the
     * top most type. Type extensions can further modify the block.
     *
     * @param BlockBuilderInterface $builder The block builder
     * @param array                 $options The options used for the configuration
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options);

    /**
     * Finishes the block.
     *
     * This method is called for each type in the hierarchy ending block the
     * top most type. Type extensions can further modify the block.
     *
     * @param BlockBuilderInterface $builder The block builder
     * @param array                 $options The options used for the configuration
     */
    public function finishBlock(BlockBuilderInterface $builder, array $options);

    /**
     * Action when the block is added to parent block.
     *
     * @param BlockInterface $parent  The child block
     * @param BlockInterface $block   The block
     * @param array          $options The options used for the configuration
     */
    public function addParent(BlockInterface $parent, BlockInterface $block, array $options);

    /**
     * Action when the block is removed to parent block.
     *
     * @param BlockInterface $parent  The child block
     * @param BlockInterface $block   The block
     * @param array          $options TThe options used for the configuration
     */
    public function removeParent(BlockInterface $parent, BlockInterface $block, array $options);

    /**
     * Action when the block adds a child.
     *
     * @param BlockInterface $child   The child block
     * @param BlockInterface $block   The block
     * @param array          $options The options used for the configuration
     */
    public function addChild(BlockInterface $child, BlockInterface $block, array $options);

    /**
     * Action when the block removes a child.
     *
     * @param BlockInterface $child   The child block
     * @param BlockInterface $block   The block
     * @param array          $options The options used for the configuration
     */
    public function removeChild(BlockInterface $child, BlockInterface $block, array $options);

    /**
     * Builds the block view.
     *
     * This method is called for each type in the hierarchy starting block the
     * top most type. Type extensions can further modify the block view.
     *
     * A block view of a block is built before the blocks of the child blocks are built.
     * This means that you cannot access child blocks views in this method. If you need
     * to do so, move your logic to {@link finishView()} instead.
     *
     * @param BlockView      $view    The block view
     * @param BlockInterface $block   The block corresponding to the block view
     * @param array          $options The options used for the configuration
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options);

    /**
     * Finishes the block view.
     *
     * This method gets called for each type in the hierarchy starting block the
     * top most type. Type extensions can further modify the block view.
     *
     * When this method is called, blocks of the block's children have already
     * been built and finished and can be accessed. You should only implement
     * such logic in this method that actually accesses child blocks views. For everything
     * else you are recommended to implement {@link buildBlock()} instead.
     *
     * @param BlockView      $view    The block view
     * @param BlockInterface $block   The block corresponding to the block view
     * @param array          $options The options used for the configuration
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options);
}
