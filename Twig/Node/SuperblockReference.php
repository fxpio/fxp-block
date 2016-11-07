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
 * Represents a sblock call node.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SuperblockReference extends \Twig_Node implements \Twig_NodeOutputInterface
{
    /**
     * Constructor.
     *
     * @param array                 $name
     * @param \Twig_Node_Expression $variables
     * @param int                   $lineno
     * @param string                $tag
     */
    public function __construct($name, \Twig_Node_Expression $variables, $lineno, $tag = null)
    {
        $attr = array('name' => $name, 'variables' => $variables, 'is_root' => true, 'is_closure' => false);

        parent::__construct(array(), $attr, $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $parentName = $this->getAttribute('parent_name');

        // closure block
        if ($this->getAttribute('is_closure')) {
            $compiler
                ->addDebugInfo($this)
                ->write(sprintf('$%s = ', $name))
                ->raw('$this->env->getExtension(\'Sonatra\Component\Block\Twig\Extension\BlockExtension\')->createNamed(')
                ->raw('"Sonatra\Component\Block\Extension\Core\Type\ClosureType"')
                ->raw(', ')
                ->raw(sprintf('array("data" => function ($blockView) use ($context, $blocks) {$this->block_%s(array_merge($context, array(\'closure\' => $blockView)), $blocks);}', $name))
                ->raw(sprintf(', "block_name" => "%s", "label" => "")', $name))
                ->raw(');')
                ->raw("\n")
                ->write(sprintf('$%sChildren[] = array(\'parent\' => $%s, \'child\' => $%s);', $parentName, $parentName, $name))
                ->raw("\n")
            ;

        // master block
        } elseif ($this->getAttribute('is_root')) {
            $compiler
                ->addDebugInfo($this)
                // create block
                ->write(sprintf('list($%s, $%sChildren) = $this->block_%s($context, $blocks);', $name, $name, $name))

                // inject children in parents
                ->raw("\n")
                ->write(sprintf('foreach ($%sChildren as $index => $cConfig) {', $name))
                ->raw("\n")
                ->indent()
                ->write(sprintf('$cConfig[\'parent\']->add($cConfig[\'child\']);'))
                ->raw("\n")
                ->outdent()
                ->write('}')
                ->raw("\n")
                ->write(sprintf('$%s = $%s ', $name, $name))
                ->raw(sprintf('instanceof \Sonatra\Component\Block\BlockView ? $%s : $%s->createView();', $name, $name))
                ->raw("\n")
                // render
                ->write('echo $this->env->getExtension(\'Sonatra\Component\Block\Twig\Extension\BlockExtension\')->renderer->searchAndRenderBlock(')
                ->raw("\n")
                ->indent()
                ->write(sprintf('$%s', $name))
                // renderer prefix
                ->raw(',')
                ->raw("\n")
                ->write('"widget"')
                ->raw(', ')
                // variables
                ->raw("\n")
                ->write('')
                ->subcompile($this->getAttribute('variables'))
                ->raw(")\n")
                ->outdent()
                ->write(";\n")
            ;

        // child block
        } else {
            $compiler
                ->addDebugInfo($this)
                ->write(sprintf('list($%s, $%sChildren) = $this->block_%s($context, $blocks);', $name, $name, $name))
                ->raw("\n")
                ->write(sprintf('$%sChildren[] = array(\'parent\' => $%s, \'child\' => $%s);', $parentName, $parentName, $name))
                ->raw("\n")
                ->write(sprintf('$%sChildren = array_merge($%sChildren, $%sChildren);', $parentName, $parentName, $name))
                ->raw("\n")
            ;
        }
    }
}
