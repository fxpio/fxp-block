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

/**
 * Extracts arrays of information out of blocks.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface BlockDataExtractorInterface
{
    /**
     * Extracts the configuration data of a block.
     *
     * @param BlockInterface $block The block
     *
     * @return array Information about the block's configuration
     */
    public function extractConfiguration(BlockInterface $block);

    /**
     * Extracts the default data of a block.
     *
     * @param BlockInterface $block The block
     *
     * @return array Information about the block's default data
     */
    public function extractDefaultData(BlockInterface $block);

    /**
     * Extracts the view variables of a block.
     *
     * @param BlockView $view The block view
     *
     * @return array Information about the view's variables
     */
    public function extractViewVariables(BlockView $view);
}
