<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Block\Twig\TokenParser;

use Fxp\Component\Block\Twig\Node\Superblock;
use Fxp\Component\Block\Twig\Node\SuperblockClosure;
use Fxp\Component\Block\Twig\Node\SuperblockReference;

/**
 * Token Parser for the 'sblock' tag.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SuperblockTokenParser extends BaseSuperblockTokenParser
{
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
}
