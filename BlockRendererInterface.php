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
 * Renders a block into HTML.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface BlockRendererInterface
{
    /**
     * Returns the engine used by this renderer.
     *
     * @return BlockRendererEngineInterface The renderer engine
     */
    public function getEngine();

    /**
     * Sets the theme(s) to be used for rendering a block view and its children.
     *
     * @param BlockView $view   The block view to assign the theme(s) to
     * @param mixed     $themes The theme(s). The type of these themes
     *                          is open to the implementation
     */
    public function setTheme(BlockView $view, $themes);

    /**
     * Renders a named block of the block theme.
     *
     * @param BlockView $view      The block view for which to render the block
     * @param string    $blockName The name of the block
     * @param array     $variables The variables to pass to the template
     *
     * @return string The HTML markup
     */
    public function renderBlock(BlockView $view, $blockName, array $variables = array());

    /**
     * Searches and renders a block for a given name suffix.
     *
     * The block is searched by combining the block names stored in the
     * block view with the given suffix. If a block name is found, that
     * block is rendered.
     *
     * If this method is called recursively, the block search is continued
     * where a block was found before.
     *
     * @param BlockView $view            The block view for which to render the block
     * @param string    $blockNameSuffix The suffix of the block name
     * @param array     $variables       The variables to pass to the template
     *
     * @return string The HTML markup
     */
    public function searchAndRenderBlock(BlockView $view, $blockNameSuffix, array $variables = array());

    /**
     * Makes a technical name human readable.
     *
     * Sequences of underscores are replaced by single spaces. The first letter
     * of the resulting string is capitalized, while all other letters are
     * turned to lowercase.
     *
     * @param string $text The text to humanize
     *
     * @return string The humanized text
     */
    public function humanize($text);
}
