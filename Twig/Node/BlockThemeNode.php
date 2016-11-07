<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Twig\Node;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockThemeNode extends \Twig_Node
{
    /**
     * Constructor.
     *
     * @param \Twig_Node $block
     * @param \Twig_Node $resources
     * @param string     $lineno
     * @param string     $tag
     */
    public function __construct(\Twig_Node $block, \Twig_Node $resources, $lineno, $tag = null)
    {
        parent::__construct(array('block' => $block, 'resources' => $resources), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$this->env->getExtension(\'Sonatra\Component\Block\Twig\Extension\BlockExtension\')->renderer->setTheme(')
            ->subcompile($this->getNode('block'))
            ->raw(', ')
            ->subcompile($this->getNode('resources'))
            ->raw(");\n");
    }
}
