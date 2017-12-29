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

use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockView;
use Fxp\Component\Block\ResolvedBlockTypeInterface;
use Symfony\Component\Form\FormView;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockUtil
{
    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Returns whether the given data is empty.
     *
     * This logic is reused multiple times throughout the processing of
     * a block and needs to be consistent. PHP's keyword `empty` cannot
     * be used as it also considers 0 and "0" to be empty.
     *
     * @param mixed $data
     *
     * @return bool
     */
    public static function isEmpty($data)
    {
        // Should not do a check for array() === $data!!!
        // This method is used in occurrences where arrays are
        // not considered to be empty, ever.
        return null === $data || '' === $data;
    }

    /**
     * Create a unique block name.
     * Uses the open ssl random function if presents, otherwise the uniqid function.
     *
     * @param string $prefix
     *
     * @return string
     */
    public static function createUniqueName($prefix = 'block')
    {
        return $prefix.(function_exists('openssl_random_pseudo_bytes')
            ? bin2hex(openssl_random_pseudo_bytes(5))
            : uniqid());
    }

    /**
     * Creates the block id.
     *
     * @param BlockInterface $block
     *
     * @return string
     */
    public static function createBlockId(BlockInterface $block)
    {
        $id = '_'.$block->getName();

        if ($block->getParent() && $block->getOption('chained_block')) {
            $id = static::createBlockId($block->getParent()).$id;
        }

        return ltrim($id, '_0123456789');
    }

    /**
     * Check if block is a specific type.
     *
     * @param BlockInterface  $block The block
     * @param string|string[] $types The class name of types
     *
     * @return bool
     */
    public static function isBlockType(BlockInterface $block, $types)
    {
        return static::isType((array) $types, $block->getConfig()->getType());
    }

    /**
     * Add attribute of view.
     *
     * @param BlockView|FormView $view  The block or form view
     * @param string             $name  The attribute name
     * @param string|int|array   $value The attribute value
     * @param string             $key   The array key of attribute in view vars
     */
    public static function addAttribute($view, $name, $value, $key = 'attr')
    {
        if ($view instanceof BlockView || $view instanceof FormView) {
            $attr = isset($view->vars[$key]) ? $view->vars[$key] : [];

            if (static::isEmpty($value)) {
                unset($attr[$name]);
            } else {
                $attr[$name] = is_array($value) ? json_encode($value) : $value;
            }

            $view->vars[$key] = $attr;
        }
    }

    /**
     * Add class attribute of view.
     *
     * @param BlockView|FormView $view    The block or form view
     * @param string             $class   The css classname of class attr
     * @param bool               $prepend Check if the classname must be added at start or at end
     * @param string             $key     The array key of attribute in view vars
     */
    public static function addAttributeClass($view, $class, $prepend = false, $key = 'attr')
    {
        if ($view instanceof BlockView || $view instanceof FormView) {
            $attr = isset($view->vars[$key]) ? $view->vars[$key] : [];
            $attrClass = isset($attr['class']) ? $attr['class'] : '';
            $attrClass = $prepend ? trim($class.' '.$attrClass) : trim($attrClass.' '.$class);

            static::addAttribute($view, 'class', $attrClass, $key);
        }
    }

    /**
     * Check if the parent type of the current type is allowed.
     *
     * @param string[]                   $types The class name of types
     * @param ResolvedBlockTypeInterface $rType The resolved block type
     *
     * @return bool
     */
    protected static function isType(array $types, ResolvedBlockTypeInterface $rType = null)
    {
        if (null === $rType) {
            return false;
        } elseif (!in_array(get_class($rType->getInnerType()), $types)) {
            return static::isType($types, $rType->getParent());
        }

        return true;
    }
}
