<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Twig\Block;

use Fxp\Component\Block\AbstractRendererEngine;
use Fxp\Component\Block\BlockView;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TwigRendererEngine extends AbstractRendererEngine implements TwigRendererEngineInterface
{
    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * @var \Twig_Template
     */
    private $template;

    /**
     * Creates a new renderer engine.
     *
     * @param \Twig_Environment $environment   The twig environment
     * @param array             $defaultThemes The default themes. The type of these
     *                                         themes is open to the implementation
     */
    public function __construct(\Twig_Environment $environment, array $defaultThemes = [])
    {
        parent::__construct($defaultThemes);

        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function renderBlock(BlockView $view, $resource, $blockName, array $variables = [])
    {
        $cacheKey = $view->vars[self::CACHE_KEY_VAR];

        $context = $this->environment->mergeGlobals($variables);

        ob_start();

        // By contract,This method can only be called after getting the resource
        // (which is passed to the method). Getting a resource for the first time
        // (with an empty cache) is guaranteed to invoke loadResourcesFromTheme(),
        // where the property $template is initialized.

        // We do not call renderBlock here to avoid too many nested level calls
        // (XDebug limits the level to 100 by default)
        $this->template->displayBlock($blockName, $context, $this->resources[$cacheKey]);

        return ob_get_clean();
    }

    /**
     * Loads the cache with the resource for a given block name.
     *
     * This implementation eagerly loads all blocks of the themes assigned to the given block
     * and all of its ancestors blocks. This is necessary, because Twig receives the
     * list of blocks later. At that point, all blocks must already be loaded, for the
     * case that the function "block()" is used in the Twig template.
     *
     * @see getResourceForBlock()
     *
     * @param string    $cacheKey  The cache key of the block view
     * @param BlockView $view      The block view for finding the applying themes
     * @param string    $blockName The name of the block to load
     *
     * @return bool True if the resource could be loaded, false otherwise
     */
    protected function loadResourceForBlockName($cacheKey, BlockView $view, $blockName)
    {
        // The caller guarantees that $this->resources[$cacheKey][$block] is
        // not set, but it doesn't have to check whether $this->resources[$cacheKey]
        // is set. If $this->resources[$cacheKey] is set, all themes for this
        // $cacheKey are already loaded (due to the eager population, see doc comment).
        if (isset($this->resources[$cacheKey])) {
            // As said in the previous, the caller guarantees that
            // $this->resources[$cacheKey][$block] is not set. Since the themes are
            // already loaded, it can only be a non-existing block.
            $this->resources[$cacheKey][$blockName] = false;

            return false;
        }

        // Recursively try to find the block in the themes assigned to $view,
        // then of its parent block view, then of the parent block view of the parent and so on.
        // When the root block is reached in this recursion, also the default
        // themes are taken into account.

        // Check each theme whether it contains the searched block
        if (isset($this->themes[$cacheKey])) {
            for ($i = count($this->themes[$cacheKey]) - 1; $i >= 0; --$i) {
                $this->loadResourcesFromTheme($cacheKey, $this->themes[$cacheKey][$i]);
                // CONTINUE LOADING (see doc comment)
            }
        }

        // Check the default themes once we reach the root block without success
        if (!$view->parent) {
            for ($i = count($this->defaultThemes) - 1; $i >= 0; --$i) {
                $this->loadResourcesFromTheme($cacheKey, $this->defaultThemes[$i]);
                // CONTINUE LOADING (see doc comment)
            }
        }

        // Proceed with the themes of the parent view
        if ($view->parent) {
            $parentCacheKey = $view->parent->vars[self::CACHE_KEY_VAR];

            if (!isset($this->resources[$parentCacheKey])) {
                $this->loadResourceForBlockName($parentCacheKey, $view->parent, $blockName);
            }

            // EAGER CACHE POPULATION (see doc comment)
            foreach ($this->resources[$parentCacheKey] as $nestedBlockName => $resource) {
                if (!isset($this->resources[$cacheKey][$nestedBlockName])) {
                    $this->resources[$cacheKey][$nestedBlockName] = $resource;
                }
            }
        }

        // Even though we loaded the themes, it can happen that none of them
        // contains the searched block
        if (!isset($this->resources[$cacheKey][$blockName])) {
            // Cache that we didn't find anything to speed up further accesses
            $this->resources[$cacheKey][$blockName] = false;
        }

        return false !== $this->resources[$cacheKey][$blockName];
    }

    /**
     * Loads the resources for all blocks in a theme.
     *
     * @param string $cacheKey The cache key for storing the resource
     * @param mixed  $theme    The theme to load the block view. This parameter
     *                         is passed by reference, because it might be necessary
     *                         to initialize the theme first. Any changes made to
     *                         this variable will be kept and be available upon
     *                         further calls to this method using the same theme
     */
    protected function loadResourcesFromTheme($cacheKey, &$theme)
    {
        if (!$theme instanceof \Twig_Template) {
            /* @var \Twig_Template $theme */
            $theme = $this->environment->loadTemplate($theme);
        }

        if (null === $this->template) {
            // Store the first \Twig_Template instance that we find so that
            // we can call displayBlock() later on. It doesn't matter *which*
            // template we use for that, since we pass the used blocks manually
            // anyway.
            $this->template = $theme;
        }

        // Use a separate variable for the inheritance traversal, because
        // theme is a reference and we don't want to change it.
        $currentTheme = $theme;

        $context = $this->environment->mergeGlobals([]);

        // The do loop takes care of template inheritance.
        // Add blocks from all templates in the inheritance tree, but avoid
        // overriding blocks already set.
        do {
            foreach ($currentTheme->getBlocks() as $block => $blockData) {
                if (!isset($this->resources[$cacheKey][$block])) {
                    // The resource given back is the key to the bucket that
                    // contains this block.
                    $this->resources[$cacheKey][$block] = $blockData;
                }
            }
        } while (false !== $currentTheme = $currentTheme->getParent($context));
    }
}
