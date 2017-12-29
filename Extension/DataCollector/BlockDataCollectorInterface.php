<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\DataCollector;

use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockView;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

/**
 * Collects and structures information about blocks.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface BlockDataCollectorInterface extends DataCollectorInterface
{
    /**
     * Stores configuration data of the given block and its children.
     *
     * @param BlockInterface $block A root block
     */
    public function collectConfiguration(BlockInterface $block);

    /**
     * Stores the default data of the given block and its children.
     *
     * @param BlockInterface $block A root block
     */
    public function collectDefaultData(BlockInterface $block);

    /**
     * Stores the view variables of the given block view and its children.
     *
     * @param BlockView $view A root block view
     */
    public function collectViewVariables(BlockView $view);

    /**
     * Specifies that the given objects represent the same conceptual block.
     *
     * @param BlockInterface $block A block object
     * @param BlockView      $view  A view object
     */
    public function associateBlockWithView(BlockInterface $block, BlockView $view);

    /**
     * Assembles the data collected about the given block and its children as
     * a tree-like data structure.
     *
     * The result can be queried using {@link getData()}.
     *
     * Contrary to {@link buildPreliminaryBlockTree()}, a {@link BlockView}
     * object has to be passed. The tree structure of this view object will be
     * used for structuring the resulting data. That means, if a child is
     * present in the view, but not in the block, it will be present in the final
     * data array anyway.
     *
     * When {@link BlockView} instances are present in the view tree, for which
     * no corresponding {@link BlockInterface} objects can be found in the block
     * tree, only the view data will be included in the result. If a
     * corresponding {@link BlockInterface} exists otherwise, call
     * {@link associateBlockWithView()} before calling this method.
     *
     * @param BlockInterface $block A root block
     * @param BlockView      $view  A root view
     */
    public function buildFinalBlockTree(BlockInterface $block, BlockView $view);

    /**
     * Returns all collected data.
     *
     * @return array
     */
    public function getData();
}
