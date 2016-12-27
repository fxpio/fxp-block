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
     * @param \Twig_Node_Expression $type    The block type
     * @param \Twig_Node_Expression $options The block options
     * @param array                 $loop    The list of loop variable name and list variable name
     * @param int                   $lineno  The lineno
     * @param string                $tag     The tag name
     */
    public function __construct(\Twig_Node_Expression $type,
                                \Twig_Node_Expression $options,
                                array $loop,
                                $lineno,
                                $tag = null)
    {
        parent::__construct(BlockUtil::createUniqueName(), new \Twig_Node(array()), $lineno, $tag);

        $this->setAttribute('type', $type);
        $this->setAttribute('options', $options);
        $this->setAttribute('loop', $loop);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $type = $this->getAttribute('type');
        list($loopKey, $loopId) = $this->buildLoopVariables();

        $compiler
            ->write('public function block_')->raw($name)->raw('($context, array $blocks = array()')->raw($loopKey)->raw(')')->raw("\n")
            ->write('{')->raw("\n")
            ->indent()
            ->addDebugInfo($this)
            ->write('$')->raw($name)->raw(' = ')
        ;

        // checks if the type is an block builder, block, or block view
        if ($type instanceof \Twig_Node_Expression_Name) {
            $compiler
                ->raw('(')
                ->subcompile($type)
                ->raw(' instanceof \Sonatra\Component\Block\BlockBuilderInterface || ')
                ->subcompile($type)
                ->raw(' instanceof \Sonatra\Component\Block\BlockInterface || ')
                ->subcompile($type)
                ->raw(' instanceof \Sonatra\Component\Block\BlockView) ? ')
                ->subcompile($type)
                ->raw(' : ')
            ;
        }

        // create the block
        $compiler
            ->raw('$this->env->getExtension(\'Sonatra\Component\Block\Twig\Extension\BlockExtension\')->createNamed(')
            ->subcompile($type)
            ->raw(', ')
        ;
        $this->compileBlockOptions($compiler, $loopId);
        $compiler->raw(');')->raw("\n");

        if ($type instanceof \Twig_Node_Expression_Name) {
            $compiler
                ->write('if ($')->raw($name)->raw(' instanceof \Sonatra\Component\Block\BlockBuilderInterface) {')->raw("\n")
                ->indent()
                ->write('$')->raw($name)->raw(' = $')->raw($name)->raw('->getBlock();')->raw("\n")
                ->outdent()
                ->write('}')->raw("\n")
            ;
        }

        // list of children
        $compiler
            ->write('$')->raw($name)->raw('Children = array();')->raw("\n")
        ;

        if ($this->hasNode('sblocks')) {
            $compiler->subcompile($this->getNode('sblocks'));
        }

        $compiler
            ->raw("\n")
            ->write('return array($')->raw($name)->raw(', $')->raw($name)->raw('Children);')->raw("\n")
            ->outdent()
            ->write('}')->raw("\n")->raw("\n")
        ;
    }

    /**
     * Compile the block options.
     *
     * @param \Twig_Compiler $compiler The compiler
     * @param string|null    $loopId   The loop id
     */
    private function compileBlockOptions(\Twig_Compiler $compiler, $loopId = null)
    {
        $options = $this->getAttribute('options');

        if (null !== $loopId) {
            $compiler
                ->raw('array_merge(')
                ->subcompile($options)
                ->raw(', array(\'id\' => ')->string($loopId.'__')->raw('.$key)')
                ->raw(')')
            ;
        } else {
            $compiler->subcompile($options);
        }
    }

    /**
     * Build the loop key and loop id variables.
     *
     * @return array
     */
    private function buildLoopVariables()
    {
        /* @var \Twig_Node_Expression_Array $options */
        $options = $this->getAttribute('options');
        $loop = $this->getAttribute('loop');
        $loopKey = '';
        $loopId = null;
        $hasId = false;

        if (!empty($loop)) {
            foreach ($options->getKeyValuePairs() as $val) {
                /* @var \Twig_Node_Expression_Constant $key */
                $key = $val['key'];
                $value = $val['value'];

                if ('id' === $key->getAttribute('value')) {
                    $hasId = true;

                    if ($value instanceof \Twig_Node_Expression_Constant) {
                        $loopKey = ', $key';
                        $loopId = $value->getAttribute('value');
                        break;
                    }
                }
            }

            if (!$hasId) {
                $loopKey = ', $key';
                $loopId = $this->getAttribute('name');
            }
        }

        return array($loopKey, $loopId);
    }
}
