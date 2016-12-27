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
     * @param array                 $name      The block name
     * @param \Twig_Node_Expression $variables The twig variables
     * @param array                 $loop      The list of loop variable name and list variable name
     * @param int                   $lineno    The lineno
     * @param string                $tag       The tag name
     */
    public function __construct($name, \Twig_Node_Expression $variables, array $loop, $lineno, $tag = null)
    {
        $attr = array('name' => $name, 'variables' => $variables, 'loop' => $loop, 'is_root' => true, 'is_closure' => false);

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

        if ($this->getAttribute('is_closure')) {
            $this->compileClosureBlock($compiler, $parentName, $name);
        } elseif (!empty($this->getAttribute('loop'))) {
            $this->compileLoopBlock($compiler);
        } elseif ($this->getAttribute('is_root')) {
            $this->compileMasterBlock($compiler);
        } else {
            $this->compileChildBlock($compiler);
        }
    }

    /**
     * Compile the closure block.
     *
     * @param \Twig_Compiler $compiler   The compiler
     * @param string         $parentName The name of parent block
     * @param string         $name       The name of block
     */
    private function compileClosureBlock(\Twig_Compiler $compiler, $parentName, $name)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$')->raw($name)->raw(' = ')
            ->raw('$this->env->getExtension(\'Sonatra\Component\Block\Twig\Extension\BlockExtension\')->createNamed(')
            ->raw('\'Sonatra\Component\Block\Extension\Core\Type\ClosureType\'')
            ->raw(', ')
            ->raw('array("data" => function ($blockView) use ($context, $blocks) { $this->block_')->raw($name)->raw('(array_merge($context, array(\'closure\' => $blockView)), $blocks); }')
            ->raw(', "block_name" => ')->string($name)->raw(', "label" => "")')
            ->raw(');')->raw("\n")
            ->write('$')->raw($parentName)->raw('Children[] = array(\'parent\' => $')->raw($parentName)->raw(', \'child\' => $')->raw($name)->raw(');')->raw("\n")
        ;
    }

    /**
     * Compile the loop block.
     *
     * @param \Twig_Compiler $compiler The compiler
     */
    private function compileLoopBlock(\Twig_Compiler $compiler)
    {
        $loop = $this->getAttribute('loop');

        $compiler
            ->write('foreach($this->getContext($context, ')->string($loop[1])->raw(') as $key => $value) {')->raw("\n")
            ->indent()
            ->write('$context[')->string($loop[0])->raw('] = $value;')->raw("\n")
        ;
        $this->compileChildBlock($compiler, '$key');
        $compiler
            ->outdent()
            ->write("}")->raw("\n")
            ->write('unset($context[')->string($loop[0])->raw(']);')->raw("\n")
        ;
    }

    /**
     * Compile the master block.
     *
     * @param \Twig_Compiler $compiler The compiler
     */
    private function compileMasterBlock(\Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');

        $compiler
            ->addDebugInfo($this)
            // create block
            ->write('list($')->raw($name)->raw(', $')->raw($name)->raw('Children) = $this->block_')->raw($name)->raw('($context, $blocks);')
            // inject children in parents
            ->write('foreach ($')->raw($name)->raw('Children as $index => $cConfig) {')->raw("\n")
            ->indent()
            ->write('$cConfig[\'parent\']->add($cConfig[\'child\']);')->raw("\n")
            ->outdent()
            ->write('}')->raw("\n")
            ->write('$')->raw($name)->raw(' = $')->raw($name)->raw(' ')
            ->raw('instanceof \Sonatra\Component\Block\BlockView ? $')->raw($name)->raw(' : $')->raw($name)->raw('->createView();')->raw("\n")
            // render
            ->write('echo $this->env->getExtension(\'Sonatra\Component\Block\Twig\Extension\BlockExtension\')->renderer->searchAndRenderBlock(')->raw("\n")
            ->indent()
            ->write('$')->raw($name)
            // renderer prefix
            ->raw(',')->raw("\n")
            ->write('"widget"')
            ->raw(', ')->raw("\n")
            // variables
            ->write('')
            ->subcompile($this->getAttribute('variables'))->raw(')')->raw("\n")
            ->outdent()->write(';')->raw("\n")
        ;
    }

    /**
     * Compile the child block.
     *
     * @param \Twig_Compiler $compiler The compiler
     * @param string|null    $key      The loop key
     */
    private function compileChildBlock(\Twig_Compiler $compiler, $key = null)
    {
        $name = $this->getAttribute('name');
        $parentName = $this->getAttribute('parent_name');
        $key = null !== $key ? ', '.$key : '';

        $compiler
            ->addDebugInfo($this)
            ->write('list($')->raw($name)->raw(', $')->raw($name)->raw('Children) = $this->block_')->raw($name)->raw('($context, $blocks')->raw($key)->raw(');')->raw("\n")
            ->write('$')->raw($parentName)->raw('Children[] = array(\'parent\' => $')->raw($parentName)->raw(', \'child\' => $')->raw($name)->raw(');')->raw("\n")
            ->write('$')->raw($parentName)->raw('Children = array_merge($')->raw($parentName)->raw('Children, $')->raw($name)->raw('Children);')->raw("\n")
        ;
    }
}
