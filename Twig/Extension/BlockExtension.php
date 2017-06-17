<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Twig\Extension;

use Sonatra\Component\Block\BlockFactoryInterface;
use Sonatra\Component\Block\BlockInterface;
use Sonatra\Component\Block\BlockRegistryInterface;
use Sonatra\Component\Block\BlockTypeInterface;
use Sonatra\Component\Block\BlockView;
use Sonatra\Component\Block\Twig\Block\TwigRendererInterface;
use Sonatra\Component\Block\Twig\TokenParser\BlockThemeTokenParser;
use Sonatra\Component\Block\Twig\TokenParser\SuperblockTokenParser;
use Sonatra\Component\Block\Util\BlockUtil;

/**
 * BlockExtension extends Twig with block capabilities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockExtension extends \Twig_Extension
{
    /**
     * This property is public so that it can be accessed directly from compiled
     * templates without having to call a getter, which slightly decreases performance.
     *
     * @var \Sonatra\Component\Block\BlockRendererInterface
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
                                array $aliases = array())
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
        $tokens = array(
            // {% block_theme form "SomeBundle::widgets.twig" %}
            new BlockThemeTokenParser(),
            // {% sblock 'checkbox', {data: true, label: "My checkbox" with {my_var: "the twig variable"} :%}
            new SuperblockTokenParser($this->aliases),
        );

        return $tokens;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $functions = array(
            new \Twig_SimpleFunction('block_widget', null, array('node_class' => 'Sonatra\Component\Block\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_component', null, array('node_class' => 'Sonatra\Component\Block\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_label', null, array('node_class' => 'Sonatra\Component\Block\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_row', null, array('node_class' => 'Sonatra\Component\Block\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_twig_render', array($this, 'renderTwigBlock'), array('is_safe' => array('html'))),
        );

        return $functions;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('block_humanize', array($this, 'humanize')),
            new \Twig_SimpleFilter('raw_closure', array($this, 'rawClosure')),
            new \Twig_SimpleFilter('block_formatter', array($this, 'formatter'), array('is_safe' => array('html'))),
        );
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
     * @return \Sonatra\Component\Block\BlockInterface
     */
    public function createNamed($type, array $options = array())
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
    public function renderTwigBlock($resource, $blockName, array $options = array())
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
    public function formatter($value, $type, array $options = array(), array $variables = array())
    {
        $options = array_replace(array('wrapped' => false, 'inherit_data' => false), $options, array('data' => $value));
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
    protected function getBlockName(array $options = array())
    {
        return isset($options['block_name']) ? $options['block_name'] : (isset($options['id']) ? $options['id'] : BlockUtil::createUniqueName());
    }
}
