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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
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

        if (null !== $parentForm) {
            $formPath = $block->hasOption('form_path') && null !== $block->getOption('form_path')
                ? $block->getOption('form_path')
                : $block->getName();
            $formNames = explode('.', $formPath);
            $form = $parentForm->vars['form'];

            foreach ($formNames as $formName) {
                if (isset($form->children[$formName])) {
                    $form = $form->children[$formName];
                }
            }

            if ($form !== $parentForm->vars['form']) {
                return $form;
            }
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
