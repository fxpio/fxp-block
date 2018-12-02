<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Util;

use Fxp\Component\Block\BlockView;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockViewUtil
{
    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Get the parent with a specific type.
     *
     * @param BlockView $view The block view
     * @param string    $type The first parent type
     *
     * @return BlockView|null
     */
    public static function getParent(BlockView $view, $type)
    {
        $parent = null;

        if (null !== $view->parent) {
            if (static::isType($view->parent, $type)) {
                $parent = $view->parent;
            } else {
                $parent = static::getParent($view->parent, $type);
            }
        }

        return $parent;
    }

    /**
     * @param BlockView $view The block view
     * @param string    $type The name of block type defined in block prefixes of the view
     *
     * @return bool
     */
    public static function isType(BlockView $view, $type)
    {
        return isset($view->vars['block_prefixes'])
            && \is_array($view->vars['block_prefixes'])
            && \in_array($type, $view->vars['block_prefixes']);
    }
}
