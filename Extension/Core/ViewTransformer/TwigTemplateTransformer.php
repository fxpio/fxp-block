<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Extension\Core\ViewTransformer;

use Fxp\Component\Block\DataTransformerInterface;

/**
 * Transforms twig template to html string.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TwigTemplateTransformer implements DataTransformerInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $blockname;

    /**
     * @var array
     */
    protected $variables;

    /**
     * Constructor.
     *
     * @param \Twig_Environment $twig     The twig environment
     * @param string            $resource The twig file
     * @param string|null       blockname  The block name of twig file
     * @param array $variables The variables of twig file
     */
    public function __construct(\Twig_Environment $twig, $resource, $blockname = null, array $variables = [])
    {
        $this->twig = $twig;
        $this->resource = $resource;
        $this->blockname = $blockname;
        $this->variables = $variables;
    }

    /**
     * Transforms a twig template to html string.
     *
     * @param mixed $value The value
     *
     * @return string The html string
     */
    public function transform($value)
    {
        /* @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($this->resource);
        $variables = array_replace($this->variables, ['data' => $value]);

        if (null !== $this->blockname) {
            $value = $template->renderBlock($this->blockname, $variables);
        } else {
            $value = $template->render($variables);
        }

        return $value;
    }
}
