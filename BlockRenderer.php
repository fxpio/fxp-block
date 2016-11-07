<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block;

use Sonatra\Component\Block\Exception\LogicException;

/**
 * Renders a block into HTML using a rendering engine.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockRenderer implements BlockRendererInterface
{
    const CACHE_KEY_VAR = 'unique_block_prefix';

    /**
     * @var BlockRendererEngineInterface
     */
    private $engine;

    /**
     * @var array
     */
    private $blockNameHierarchyMap = array();

    /**
     * @var array
     */
    private $hierarchyLevelMap = array();

    /**
     * @var array
     */
    private $variableStack = array();

    /**
     * Constructor.
     *
     * @param BlockRendererEngineInterface $engine
     */
    public function __construct(BlockRendererEngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * {@inheritdoc}
     */
    public function setTheme(BlockView $view, $themes)
    {
        $this->engine->setTheme($view, $themes);
    }

    /**
     * {@inheritdoc}
     */
    public function renderBlock(BlockView $view, $blockName, array $variables = array())
    {
        $resource = $this->engine->getResourceForBlockName($view, $blockName);

        if (!$resource) {
            throw new LogicException(sprintf('No block "%s" found while rendering the form.', $blockName));
        }

        $viewCacheKey = $view->vars[self::CACHE_KEY_VAR];

        // The variables are cached globally for a view (instead of for the
        // current suffix)
        if (!isset($this->variableStack[$viewCacheKey])) {
            $this->variableStack[$viewCacheKey] = array();

            // The default variable scope contains all view variables, merged with
            // the variables passed explicitly to the helper
            $scopeVariables = $view->vars;

            $varInit = true;
        } else {
            // Reuse the current scope and merge it with the explicitly passed variables
            $scopeVariables = end($this->variableStack[$viewCacheKey]);

            $varInit = false;
        }

        // Merge the passed with the existing attributes
        if (isset($variables['attr']) && isset($scopeVariables['attr'])) {
            $variables['attr'] = array_replace($scopeVariables['attr'], $variables['attr']);
        }

        // Merge the passed with the exist *label* attributes
        if (isset($variables['label_attr']) && isset($scopeVariables['label_attr'])) {
            $variables['label_attr'] = array_replace($scopeVariables['label_attr'], $variables['label_attr']);
        }

        // Do not use array_replace_recursive(), otherwise array variables
        // cannot be overwritten
        $variables = array_replace($scopeVariables, $variables);

        $this->variableStack[$viewCacheKey][] = $variables;

        // Do the rendering
        $html = $this->engine->renderBlock($view, $resource, $blockName, $variables);

        // Clear the stack
        array_pop($this->variableStack[$viewCacheKey]);

        if ($varInit) {
            unset($this->variableStack[$viewCacheKey]);
        }

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function searchAndRenderBlock(BlockView $view, $blockNameSuffix, array $variables = array())
    {
        $renderOnlyOnce = 'row' === $blockNameSuffix || 'widget' === $blockNameSuffix;

        if ($renderOnlyOnce && $view->isRendered()) {
            return '';
        }

        // The cache key for storing the variables and types
        $viewCacheKey = $view->vars[self::CACHE_KEY_VAR];
        $viewAndSuffixCacheKey = $viewCacheKey.$blockNameSuffix;

        // In templates, we have to deal with two kinds of block hierarchies:

        //   +---------+          +---------+
        //   | Theme B | -------> | Theme A |
        //   +---------+          +---------+

        //  block_widget -------> block_widget
        //       ^
        //       |
        //  choice_widget -----> choice_widget

        // The first kind of hierarchy is the theme hierarchy. This allows to
        // override the block "choice_widget" from Theme A in the extending
        // Theme B. This kind of inheritance needs to be supported by the
        // template engine and, for example, offers "parent()" or similar
        // functions to fall back from the custom to the parent implementation.

        // The second kind of hierarchy is the block type hierarchy. This allows
        // to implement a custom "choice_widget" block (no matter in which theme),
        // or to fallback to the block of the parent type, which would be
        // "block_widget" in this example (again, no matter in which theme).
        // If the designer wants to explicitly fallback to "block_widget" in his
        // custom "choice_widget", for example because he only wants to wrap
        // a <div> around the original implementation, he can simply call the
        // widget() function again to render the block for the parent type.

        // The second kind is implemented in the following blocks.
        if (!isset($this->blockNameHierarchyMap[$viewAndSuffixCacheKey])) {
            // INITIAL CALL
            // Calculate the hierarchy of template blocks and start on
            // the bottom level of the hierarchy (= "_<id>_<section>" block)
            $blockNameHierarchy = array();
            foreach ($view->vars['block_prefixes'] as $blockNamePrefix) {
                $blockNameHierarchy[] = $blockNamePrefix.'_'.$blockNameSuffix;
            }
            $hierarchyLevel = count($blockNameHierarchy) - 1;

            $hierarchyInit = true;
        } else {
            // RECURSIVE CALL
            // If a block recursively calls searchAndRenderBlock() again, resume rendering
            // using the parent type in the hierarchy.
            $blockNameHierarchy = $this->blockNameHierarchyMap[$viewAndSuffixCacheKey];
            $hierarchyLevel = $this->hierarchyLevelMap[$viewAndSuffixCacheKey] - 1;

            $hierarchyInit = false;
        }

        // The variables are cached globally for a block view (instead of for the
        // current suffix)
        if (!isset($this->variableStack[$viewCacheKey])) {
            // The default variable scope contains all block view variables, merged with
            // the variables passed explicitly to the helper
            $scopeVariables = $view->vars;

            $varInit = true;
        } else {
            // Reuse the current scope and merge it with the explicitly passed variables
            $scopeVariables = end($this->variableStack[$viewCacheKey]);

            $varInit = false;
        }

        // Load the resource where this block can be found
        $resource = $this->engine->getResourceForBlockNameHierarchy($view, $blockNameHierarchy, $hierarchyLevel);

        // Update the current hierarchy level to the one at which the resource was
        // found. For example, if looking for "choice_widget", but only a resource
        // is found for its parent "block_widget", then the level is updated here
        // to the parent level.
        $hierarchyLevel = $this->engine->getResourceHierarchyLevel($view, $blockNameHierarchy, $hierarchyLevel);

        // The actually existing block name in $resource
        $blockName = $blockNameHierarchy[$hierarchyLevel];

        // Escape if no resource exists for this block
        if (!$resource) {
            throw new LogicException(sprintf(
                'Unable to render the block as none of the following blocks exist: "%s".',
                implode('", "', array_reverse($blockNameHierarchy))
            ));
        }

        // Merge the passed with the existing attributes
        if (isset($variables['attr']) && isset($scopeVariables['attr'])) {
            $variables['attr'] = array_replace($scopeVariables['attr'], $variables['attr']);
        }

        // Merge the passed with the exist *label* attributes
        if (isset($variables['label_attr']) && isset($scopeVariables['label_attr'])) {
            $variables['label_attr'] = array_replace($scopeVariables['label_attr'], $variables['label_attr']);
        }

        // Do not use array_replace_recursive(), otherwise array variables
        // cannot be overwritten
        $variables = array_replace($scopeVariables, $variables);

        // In order to make recursive calls possible, we need to store the block hierarchy,
        // the current level of the hierarchy and the variables so that this method can
        // resume rendering one level higher of the hierarchy when it is called recursively.

        // We need to store these values in maps (associative arrays) because within a
        // call to widget() another call to widget() can be made, but for a different block view
        // object. These nested calls should not override each other.
        $this->blockNameHierarchyMap[$viewAndSuffixCacheKey] = $blockNameHierarchy;
        $this->hierarchyLevelMap[$viewAndSuffixCacheKey] = $hierarchyLevel;

        // We also need to store the variables for the block view so that we can render other
        // blocks for the same block using the same variables as in the outer block.
        $this->variableStack[$viewCacheKey][] = $variables;

        // Do the rendering
        $html = $this->engine->renderBlock($view, $resource, $blockName, $variables);

        // Clear the stack
        array_pop($this->variableStack[$viewCacheKey]);

        // Clear the caches if they were filled for the first time within
        // this function call
        if ($hierarchyInit) {
            unset($this->blockNameHierarchyMap[$viewAndSuffixCacheKey]);
            unset($this->hierarchyLevelMap[$viewAndSuffixCacheKey]);
        }

        if ($varInit) {
            unset($this->variableStack[$viewCacheKey]);
        }

        if ($renderOnlyOnce) {
            $view->setRendered();
        }

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function humanize($text)
    {
        return ucfirst(trim(strtolower(preg_replace(array('/([A-Z])/', '/[_\s]+/'), array('_$1', ' '), $text))));
    }
}
