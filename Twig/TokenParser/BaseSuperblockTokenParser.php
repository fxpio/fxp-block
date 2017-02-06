<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Block\Twig\TokenParser;

use Sonatra\Component\Block\Twig\Node\SuperblockReference;

/**
 * Token Parser for the 'sblock' tag.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class BaseSuperblockTokenParser extends \Twig_TokenParser
{
    /**
     * @var array
     */
    protected $aliases;

    /**
     * Constructor.
     *
     * @param array $aliases The aliases of block type classes
     */
    public function __construct(array $aliases = array())
    {
        $this->aliases = $aliases;
    }

    /**
     * Parse the arguments.
     *
     * @return array
     *
     * @throws \Twig_Error_Syntax
     */
    protected function parseArguments()
    {
        $stream = $this->parser->getStream();
        $options = new \Twig_Node_Expression_Array(array(), $stream->getCurrent()->getLine());
        $variables = new \Twig_Node_Expression_Array(array(), $stream->getCurrent()->getLine());
        $skip = false;
        $loop = array();
        $tagNotSupported = 'The "%s" tag does not supported. Constructs your "%s" directly in code, otherwise it is impossible to recover the form in your code.';
        $isNotSupported = null;

        // {% sblock 'checkbox' ... :%}
        $type = $this->getRealType($this->parser->getExpressionParser()->parseExpression());

        if ($stream->test(\Twig_Token::PUNCTUATION_TYPE, ',')) {
            $stream->next();
        }

        // {% sblock 'checkbox' data=true block_name='foo' label='Bar' :%}
        if ($this->isNotSpecialToken($stream)
                && $stream->look(1)->getType() === \Twig_Token::OPERATOR_TYPE
                && $stream->look(1)->getValue() === '=') {
            $options = new \Twig_Node_Expression_Array(array(), $stream->getCurrent()->getLine());

            do {
                $this->addKeyValues($stream, $options);
            } while ($this->isNotSpecialToken($stream));

            // {% sblock 'checkbox' {data:true} ... :%} or {% sblock 'checkbox' ... :%}
        } elseif ($this->isNotSpecialToken($stream)) {
            $options = $this->parser->getExpressionParser()->parseExpression();
        }

        if ($stream->test(\Twig_Token::NAME_TYPE, 'sfor')) {
            $loop = $this->getSforVariables($stream);
        }

        if ($stream->test(\Twig_Token::NAME_TYPE, 'with')) {
            $stream->next();

            // {% sblock 'checkbox', {data:true} with {foo:'bar'} :%}
            do {
                if ($stream->test(\Twig_Token::NAME_TYPE) || $stream->test(\Twig_Token::PUNCTUATION_TYPE, '{')) {
                    $variables = $this->parser->getExpressionParser()->parseExpression();
                }

                if ($stream->test(\Twig_Token::PUNCTUATION_TYPE, ',')) {
                    $stream->next();
                } elseif (!$stream->test(\Twig_Token::PUNCTUATION_TYPE, ':')
                    && !$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
                    throw new \Twig_Error_Syntax("The parameters after 'with' must be separated by commas", $stream->getCurrent()->getLine(), $stream->getSourceContext()->getName());
                }
            } while (!$stream->test(\Twig_Token::PUNCTUATION_TYPE, ':')
                && !$stream->test(\Twig_Token::BLOCK_END_TYPE));
        }

        // end shortcut
        if ($stream->test(\Twig_Token::PUNCTUATION_TYPE, ':')) {
            $stream->next();
            $skip = true;
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        if (null !== $isNotSupported) {
            foreach ($options->getIterator() as $test) {
                if ($test instanceof \Twig_Node_Expression_Constant
                    && in_array($test->getAttribute('value'), array('block_name', 'id'))) {
                    $isNotSupported = null;
                }
            }

            if (null !== $isNotSupported || $options->count() !== 2) {
                throw new \Twig_Error_Syntax(sprintf($tagNotSupported, $isNotSupported, $isNotSupported));
            }
        }

        return array($type, $options, $variables, $skip, $loop);
    }

    /**
     * Push the previous twig node on new blocks body.
     *
     * @param \Twig_Node            $blocks
     * @param \Twig_Node_Expression $variables
     * @param string                $parentName
     * @param \Twig_Node_Block      $previous
     */
    protected function pushClosureNode(\Twig_Node $blocks, \Twig_Node_Expression $variables, $parentName, \Twig_Node_Block $previous = null)
    {
        if (null === $previous) {
            return;
        }

        $name = $previous->getAttribute('name');
        $reference = new SuperblockReference($name, $variables, array(), $previous->getTemplateLine(), $previous->getNodeTag());
        $reference->setAttribute('is_closure', true);
        $reference->setAttribute('parent_name', $parentName);

        $this->parser->setBlock($name, $previous);
        $blocks->setNode(count($blocks), $reference);
    }

    protected function getRealType(\Twig_Node $type)
    {
        if ($type instanceof \Twig_Node_Expression_Constant) {
            $name = $type->getAttribute('value');

            if (isset($this->aliases[$name])) {
                $type->setAttribute('value', $this->aliases[$name]);
            }
        }

        return $type;
    }

    /**
     * Get the variables defines in the Sfor attribute.
     *
     * @param \Twig_TokenStream $stream
     *
     * @return string[] The sfor variable names
     *
     * @throws \Twig_Error_Syntax
     */
    protected function getSforVariables(\Twig_TokenStream $stream)
    {
        $forOptions = new \Twig_Node_Expression_Array(array(), $stream->getCurrent()->getLine());
        $this->addKeyValues($stream, $forOptions);
        $sfor = $forOptions->getNode(1)->getAttribute('value');

        preg_match('/([\w\d\_]+) in ([\w\d\_\.\(\)\'\"]+)/', $sfor, $matches);

        if (!isset($matches[1]) || !isset($matches[2])) {
            throw new \Twig_Error_Syntax("The sfor parameter must be the pattern: '<variable> in <variables>'", $stream->getCurrent()->getLine(), $stream->getSourceContext()->getName());
        }

        return array($matches[1], $this->getSforInVariableNode($matches[2]));
    }

    /**
     * Get the in variable of sfor.
     *
     * @param string $variable The in variable of sfor
     *
     * @return \Twig_Node
     */
    protected function getSforInVariableNode($variable)
    {
        $env = new \Twig_Environment(new \Twig_Loader_Array());
        $parser = new \Twig_Parser($env);
        $lexer = new \Twig_Lexer($env);
        $stream = $lexer->tokenize(new \Twig_Source('{{'.$variable.'}}', 'variables'));

        return $parser->parse($stream)->getNode('body')->getNode(0)->getNode('expr')->getNode('node');
    }

    /**
     * Add key with values in the array of values.
     *
     * @param \Twig_TokenStream           $stream The stream
     * @param \Twig_Node_Expression_Array $values The values
     *
     * @throws \Twig_Error_Syntax
     */
    protected function addKeyValues(\Twig_TokenStream $stream, \Twig_Node_Expression_Array $values)
    {
        if (!$stream->test(\Twig_Token::NAME_TYPE)
            && !$stream->test(\Twig_Token::STRING_TYPE)) {
            throw new \Twig_Error_Syntax(sprintf('The attribute name "%s" must be an STRING or CONSTANT', $stream->getCurrent()->getValue()), $stream->getCurrent()->getLine(), $stream->getSourceContext()->getName());
        }

        $attr = $stream->getCurrent();
        $attr = new \Twig_Node_Expression_Constant($attr->getValue(), $attr->getLine());
        $stream->next();

        if (!$stream->test(\Twig_Token::OPERATOR_TYPE, '=')) {
            throw new \Twig_Error_Syntax("The attribute must be followed by '=' operator", $stream->getCurrent()->getLine(), $stream->getSourceContext()->getName());
        }

        $stream->next();
        $values->addElement($this->parser->getExpressionParser()->parseExpression(), $attr);
    }

    /**
     * Check if the current token isn't a special token (sfor, with, :).
     *
     * @param \Twig_TokenStream $stream The stream
     *
     * @return bool
     */
    protected function isNotSpecialToken(\Twig_TokenStream $stream)
    {
        return !$stream->test(\Twig_Token::NAME_TYPE, 'with')
            && !$stream->test(\Twig_Token::NAME_TYPE, 'sfor')
            && !$stream->test(\Twig_Token::PUNCTUATION_TYPE, ':')
            && !$stream->test(\Twig_Token::BLOCK_END_TYPE);
    }
}
