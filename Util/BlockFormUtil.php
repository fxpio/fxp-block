<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Util;

use Sonatra\Component\Block\BlockInterface;
use Sonatra\Component\Block\BlockView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockFormUtil
{
    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Get the parent form.
     *
     * @param BlockInterface $block
     *
     * @return FormInterface|null
     */
    public static function getParentForm(BlockInterface $block)
    {
        $form = $block->getForm();

        if (null !== $form) {
            return $form;
        }

        return null !== $block->getParent()
            ? static::getParentForm($block->getParent())
            : null;
    }

    /**
     * Create form view.
     *
     * @param BlockView      $view
     * @param BlockInterface $block
     *
     * @return FormView
     */
    public static function createFormView(BlockView $view, BlockInterface $block)
    {
        $parentForm = static::getParentFormView($view);

        if (null !== $parentForm && isset($parentForm->vars['form']->children[$block->getName()])) {
            return $parentForm->vars['form']->children[$block->getName()];
        }

        return $block->getForm()->createView($parentForm);
    }

    /**
     * Get the parent form view.
     *
     * @param BlockView $view
     *
     * @return FormView|null
     */
    public static function getParentFormView(BlockView $view)
    {
        if (isset($view->vars['block_form']) && null !== $view->vars['block_form']) {
            return $view->vars['block_form'];
        }

        return null !== $view->parent
            ? static::getParentFormView($view->parent)
            : null;
    }
}
