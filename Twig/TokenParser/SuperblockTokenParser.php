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

use Sonatra\Component\Block\Twig\Node\Superblock;
use Sonatra\Component\Block\Twig\Node\SuperblockReference;
use Sonatra\Component\Block\Twig\Node\SuperblockClosure;

/**
 * Token Parser for the 'sblock' tag.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SuperblockTokenParser extends \Twig_TokenParser
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
     * Parses a token and returns a node.
     *
     * @param \Twig_Token $token A Twig_Token instance
     *
     * @return \Twig_NodeInterface A Twig_NodeInterface instance
     *
     * @throws \Twig_Error_Syntax When error syntax
     */
    public function parse(\Twig_Token $token)
    {
        list($type, $options, $variables, $skip, $loop) = $this->parseArguments();

        $stream = $this->parser->getStream();
        $lineno = $stream->getCurrent()->getLine();

        $superblock = new Superblock($type, $options, $loop, $lineno, $this->getTag());
        $name = $superblock->getAttribute('name');
        $reference = new SuperblockReference($name, $variables, $loop, $lineno, $this->getTag());
        $reference->setAttribute('parent_name', $name);

        $this->parser->setBlock($name, $superblock);
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);

        if ($skip) {
            $this->parser->popBlockStack();
            $this->parser->popLocalScope();

            return $reference;
        }

        // body content
        $sBlocks = new \Twig_Node(array(), array(), $lineno);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $previousTwigNode = null;

        if (0 === count($body) || null !== $body->getNodeTag()) {
            $body = new \Twig_Node(array($body), array(), $lineno);
        }

        if (null === $body->getNodeTag()) {
            /* @var \Twig_Node $node */
            foreach ($body->getIterator() as $node) {
                if (!$node instanceof \Twig_Node) {
                    continue;
                }

                if ($node instanceof SuperblockReference) {
                    $this->pushClosureNode($sBlocks, $variables, $name, $previousTwigNode);
                    $previousTwigNode = null;

                    $node->setAttribute('is_root', false);
                    $node->setAttribute('parent_name', $name);
                    $sBlocks->setNode(count($sBlocks), $node);
                } elseif ($node instanceof \Twig_Node_Set) {
                    $this->pushClosureNode($sBlocks, $variables, $name, $previousTwigNode);
                    $previousTwigNode = null;

                    $sBlocks->setNode(count($sBlocks), $node);
                } elseif (!$node instanceof \Twig_Node_Text || ($node instanceof \Twig_Node_Text && '' !== trim($node->getAttribute('data')))) {
                    if (null === $previousTwigNode) {
                        $previousTwigNode = new SuperblockClosure(new \Twig_Node(array(), array(), $lineno), $node->getTemplateLine());
                    }

                    $previousTwigNode->getNode('body')->setNode(count($previousTwigNode->getNode('body')), $node);
                }
            }
        }

        $this->pushClosureNode($sBlocks, $variables, $name, $previousTwigNode);

        $superblock->setNode('sblocks', $sBlocks);

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return $reference;
    }

    /**
     * Decide block end.
     *
     * @param \Twig_Token $token
     *
     * @return bool
     */
    public function decideBlockEnd(\Twig_Token $token)
    {
        return $token->test('endsblock');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'sblock';
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
        if ($stream->look(1)->getType() === \Twig_Token::OPERATOR_TYPE
            && $stream->look(1)->getValue() === '=') {
            $options = new \Twig_Node_Expression_Array(array(), $stream->getCurrent()->getLine());

            do {
                $this->addKeyValues($stream, $options);
            } while (!$stream->test(\Twig_Token::NAME_TYPE, 'with')
                && !$stream->test(\Twig_Token::NAME_TYPE, 'sfor')
                && !$stream->test(\Twig_Token::PUNCTUATION_TYPE, ':')
                && !$stream->test(\Twig_Token::BLOCK_END_TYPE));

            // {% sblock 'checkbox' {data:true} ... :%} or {% sblock 'checkbox' ... :%}
        } elseif (!$stream->test(\Twig_Token::NAME_TYPE, 'with')
            && !$stream->test(\Twig_Token::NAME_TYPE, 'sfor')
            && !$stream->test(\Twig_Token::PUNCTUATION_TYPE, ':')
            && !$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
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

        // end schortcut
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

        preg_match('/([\w\d\_]+) in ([\w\d\_]+)/', $sfor, $matches);

        if (!isset($matches[1]) || !isset($matches[2])) {
            throw new \Twig_Error_Syntax("The sfor parameter must be the pattern: '<varialble> in <variables>'", $stream->getCurrent()->getLine(), $stream->getSourceContext()->getName());
        }

        return array($matches[1], $matches[2]);
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
}
