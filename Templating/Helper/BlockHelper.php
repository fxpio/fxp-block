<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Templating\Helper;

use Fxp\Component\Block\BlockRendererInterface;
use Fxp\Component\Block\BlockView;
use Fxp\Component\Block\Extension\Core\Type\BlockType;
use Symfony\Component\Templating\Helper\Helper;

/**
 * BlockHelper provides helpers to help display blocks.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockHelper extends Helper
{
    /**
     * @var BlockRendererInterface
     */
    private $renderer;

    /**
     * Constructor.
     *
     * @param BlockRendererInterface $renderer
     */
    public function __construct(BlockRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return BlockType::class;
    }

    /**
     * Sets a theme for a given view.
     *
     * The theme format is "<Bundle>:<Controller>".
     *
     * @param BlockView    $view   A BlockView instance
     * @param string|array $themes A theme or an array of theme
     */
    public function setTheme(BlockView $view, $themes)
    {
        $this->renderer->setTheme($view, $themes);
    }

    /**
     * Renders the HTML for a given view.
     *
     * Example usage:
     *
     *     <?php echo view['block']->widget() ?>
     *
     * You can pass options during the call:
     *
     *     <? echo view['block']->widget(array('attr' => array('class' => 'foo'))) ?>
     *
     *     <? echo view['block']->widget(array('separator' => '+++++')) ?>
     *
     * @param BlockView $view      The view for which to render the widget
     * @param array     $variables Additional variables passed to the template
     *
     * @return string The HTML markup
     */
    public function widget(BlockView $view, array $variables = array())
    {
        return $this->renderer->searchAndRenderBlock($view, 'widget', $variables);
    }

    /**
     * Renders the entire block field "row".
     *
     * @param BlockView $view      The view for which to render the row
     * @param array     $variables Additional variables passed to the template
     *
     * @return string The HTML markup
     */
    public function row(BlockView $view, array $variables = array())
    {
        return $this->renderer->searchAndRenderBlock($view, 'row', $variables);
    }

    /**
     * Renders the label of the given view.
     *
     * @param BlockView $view      The view for which to render the label
     * @param string    $label     The label
     * @param array     $variables Additional variables passed to the template
     *
     * @return string The HTML markup
     */
    public function label(BlockView $view, $label = null, array $variables = array())
    {
        if (null !== $label) {
            $variables += array('label' => $label);
        }

        return $this->renderer->searchAndRenderBlock($view, 'label', $variables);
    }

    /**
     * Renders a block of the template.
     *
     * @param BlockView $view      The view for determining the used themes
     * @param string    $blockName The name of the block to render
     * @param array     $variables The variable to pass to the template
     *
     * @return string The HTML markup
     */
    public function block(BlockView $view, $blockName, array $variables = array())
    {
        return $this->renderer->renderBlock($view, $blockName, $variables);
    }

    /**
     * Humanize the block name.
     *
     * @param string $text
     *
     * @return string
     */
    public function humanize($text)
    {
        return $this->renderer->humanize($text);
    }
}
