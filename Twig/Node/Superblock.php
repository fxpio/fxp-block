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

use Sonatra\Component\Block\Util\BlockUtil;

/**
 * Represents a sblock node.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Superblock extends \Twig_Node_Block
{
    /**
     * Constructor.
     *
     * @param \Twig_Node_Expression $type
     * @param \Twig_Node_Expression $options
     * @param int                   $lineno
     * @param string                $tag
     */
    public function __construct(\Twig_Node_Expression $type,
            \Twig_Node_Expression $options, $lineno, $tag = null)
    {
        parent::__construct(BlockUtil::createUniqueName(), new \Twig_Node(array()), $lineno, $tag);

        $this->setAttribute('type', $type);
        $this->setAttribute('options', $options);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');

        $compiler
            ->write(sprintf("public function block_%s(\$context, array \$blocks = array())\n", $this->getAttribute('name')), "{\n")
            ->indent()
            ->addDebugInfo($this)
            ->write(sprintf('$%s = ', $name))
        ;

        // checks if the type is an block builder, block, or block view
        if ($this->getAttribute('type') instanceof \Twig_Node_Expression_Name) {
            $compiler
                ->raw('(')
                ->subcompile($this->getAttribute('type'))
                ->raw(' instanceof \Sonatra\Component\Block\BlockBuilderInterface || ')
                ->subcompile($this->getAttribute('type'))
                ->raw(' instanceof \Sonatra\Component\Block\BlockInterface || ')
                ->subcompile($this->getAttribute('type'))
                ->raw(' instanceof \Sonatra\Component\Block\BlockView) ? ')
                ->subcompile($this->getAttribute('type'))
                ->raw(' : ')
            ;
        }

        // create the block
        $compiler
            ->raw('$this->env->getExtension(\'Sonatra\Component\Block\Twig\Extension\BlockExtension\')->createNamed(')
            ->subcompile($this->getAttribute('type'))
            ->raw(', ')
            ->subcompile($this->getAttribute('options'))
            ->raw(')')
            ->raw(";\n")
        ;

        if ($this->getAttribute('type') instanceof \Twig_Node_Expression_Name) {
            $compiler
                ->write(sprintf('if ($%s instanceof \Sonatra\Component\Block\BlockBuilderInterface) {', $name))
                ->raw("\n")
                ->indent()
                ->write(sprintf('$%s = $%s->getBlock();', $name, $name))
                ->raw("\n")
                ->outdent()
                ->write('}')
                ->raw("\n")
            ;
        }

        // list of children
        $compiler
            ->write(sprintf('$%sChildren = array();', $name))
            ->raw("\n")
        ;

        if ($this->hasNode('sblocks')) {
            $compiler->subcompile($this->getNode('sblocks'));
        }

        $compiler
            ->raw("\n")
            ->write(sprintf('return array($%s, $%sChildren);', $name, $name))
            ->raw("\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
}
