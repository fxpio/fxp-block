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

use Fxp\Component\Block\Exception\ClassNotInstantiableException;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class BlockEvents
{
    /**
     * The BlockEvents::PRE_SET_DATA event is dispatched at the beginning of the Block::setData() method.
     *
     * It can be used to:
     *  - Modify the data given during pre-population;
     *  - Modify a block depending on the pre-populated data (adding or removing fields dynamically).
     * The event listener method receives a Fxp\Component\Block\BlockEvent instance.
     */
    const PRE_SET_DATA = 'block.pre_set_data';

    /**
     * The BlockEvents::POST_SET_DATA event is dispatched at the end of the Block::setData() method.
     *
     * This event is mostly here for reading data after having pre-populated the block.
     * The event listener method receives a Fxp\Component\Block\BlockEvent instance.
     */
    const POST_SET_DATA = 'block.post_set_data';

    public function __construct()
    {
        throw new ClassNotInstantiableException(__CLASS__);
    }
}
