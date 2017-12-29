<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Twig\Extension;

use Fxp\Component\Block\BlockFactoryInterface;
use Fxp\Component\Block\BlockInterface;
use Fxp\Component\Block\BlockRegistryInterface;
use Fxp\Component\Block\BlockTypeInterface;
use Fxp\Component\Block\BlockView;
use Fxp\Component\Block\Twig\Block\TwigRendererInterface;
use Fxp\Component\Block\Twig\TokenParser\BlockThemeTokenParser;
use Fxp\Component\Block\Twig\TokenParser\SuperblockTokenParser;
use Fxp\Component\Block\Util\BlockUtil;

/**
 * BlockExtension extends Twig with block capabilities.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockExtension extends \Twig_Extension
{
    /**
     * This property is public so that it can be accessed directly from compiled
     * templates without having to call a getter, which slightly decreases performance.
     *
     * @var \Fxp\Component\Block\BlockRendererInterface
     */
    public $renderer;

    /**
     * @var BlockFactoryInterface
     */
    protected $factory;

    /**
     * @var BlockRegistryInterface
     */
    protected $registry;

    /**
     * @var array
     */
    protected $aliases;

    /**
     * @var array
     */
    protected $types;

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * Constructor.
     *
     * @param \Twig_Environment      $environment
     * @param TwigRendererInterface  $renderer
     * @param BlockFactoryInterface  $factory
     * @param BlockRegistryInterface $registry
     * @param array                  $aliases
     */
    public function __construct(\Twig_Environment $environment, TwigRendererInterface $renderer,
                                BlockFactoryInterface $factory, BlockRegistryInterface $registry,
                                array $aliases = [])
    {
        $this->environment = $environment;
        $this->renderer = $renderer;
        $this->factory = $factory;
        $this->registry = $registry;
        $this->aliases = $aliases;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        $tokens = [
            // {% block_theme form "SomeBundle::widgets.twig" %}
            new BlockThemeTokenParser(),
            // {% sblock 'checkbox', {data: true, label: "My checkbox" with {my_var: "the twig variable"} :%}
            new SuperblockTokenParser($this->aliases),
        ];

        return $tokens;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $functions = [
            new \Twig_Function('block_widget', null, ['node_class' => 'Fxp\Component\Block\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => ['html']]),
            new \Twig_Function('block_component', null, ['node_class' => 'Fxp\Component\Block\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => ['html']]),
            new \Twig_Function('block_label', null, ['node_class' => 'Fxp\Component\Block\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => ['html']]),
            new \Twig_Function('block_row', null, ['node_class' => 'Fxp\Component\Block\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => ['html']]),
            new \Twig_Function('block_twig_render', [$this, 'renderTwigBlock'], ['is_safe' => ['html']]),
        ];

        return $functions;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_Filter('block_humanize', [$this, 'humanize']),
            new \Twig_Filter('raw_closure', [$this, 'rawClosure']),
            new \Twig_Filter('block_formatter', [$this, 'formatter'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Get block factory.
     *
     * @return BlockFactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Create block named with the 'block_name' options.
     *
     * @param string|BlockTypeInterface|BlockInterface $type
     * @param array                                    $options
     *
     * @return \Fxp\Component\Block\BlockInterface
     */
    public function createNamed($type, array $options = [])
    {
        if ($type instanceof BlockInterface) {
            return $type;
        }

        $name = $this->getBlockName($options);

        return $this->factory->createNamed($name, $type, null, $options);
    }

    /**
     * Render the block of twig resource.
     *
     * @param string $resource
     * @param string $blockName
     * @param array  $options
     *
     * @return string
     */
    public function renderTwigBlock($resource, $blockName, array $options = [])
    {
        if (null !== $this->environment) {
            /* @var \Twig_Template $template */
            $template = $this->environment->loadTemplate($resource);

            return $template->renderBlock($blockName, $options);
        }

        return '';
    }

    /**
     * Render closure value.
     *
     * @param string|\Closure $value
     * @param BlockView       $view
     *
     * @return string
     */
    public function rawClosure($value, BlockView $view)
    {
        if ($value instanceof \Closure) {
            $value = $value($view);
        }

        return $value;
    }

    /**
     * Format the value.
     *
     * @param mixed  $value     The value to format
     * @param string $type      The formatter type
     * @param array  $options   The block options
     * @param array  $variables The template variables
     *
     * @return string
     */
    public function formatter($value, $type, array $options = [], array $variables = [])
    {
        $options = array_replace(['wrapped' => false, 'inherit_data' => false], $options, ['data' => $value]);
        $type = isset($this->aliases[$type]) ? $this->aliases[$type] : $type;

        if (isset($options['entry_type']) && isset($this->aliases[$options['entry_type']])) {
            $options['entry_type'] = $this->aliases[$options['entry_type']];
        }

        /* @var BlockInterface $block */
        $block = $this->createNamed($type, $options);

        return $this->renderer->searchAndRenderBlock($block->createView(), 'widget', $variables);
    }

    /**
     * Makes a technical name human readable.
     *
     * @param string $text The text to humanize
     *
     * @return string The humanized text
     */
    public function humanize($text)
    {
        return $this->renderer->humanize($text);
    }

    /**
     * Get the block name.
     *
     * @param array $options
     *
     * @return string|null
     */
    protected function getBlockName(array $options = [])
    {
        return isset($options['block_name']) ? $options['block_name'] : (isset($options['id']) ? $options['id'] : BlockUtil::createUniqueName());
    }
}
