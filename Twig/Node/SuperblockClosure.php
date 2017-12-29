<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Twig\Node;

use Fxp\Component\Block\Util\BlockUtil;

/**
 * Represents a sblock closure node.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SuperblockClosure extends \Twig_Node_Block
{
    /**
     * Constructor.
     *
     * @param \Twig_Node $body
     * @param int        $lineno
     * @param string     $tag
     */
    public function __construct(\Twig_Node $body, $lineno, $tag = null)
    {
        $name = sprintf('twig_%s', BlockUtil::createUniqueName());

        parent::__construct($name, $body, $lineno, $tag);
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
            ->write(sprintf("public function block_%s(\$context, array \$blocks = array())\n", $this->getAttribute('name')), "{\n")
            ->indent()
            ->subcompile($this->getNode('body'))
            ->raw("\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
}
