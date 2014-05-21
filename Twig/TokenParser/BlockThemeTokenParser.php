<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Twig\TokenParser;

use Sonatra\Bundle\BlockBundle\Twig\Node\BlockThemeNode;

/**
 * Token Parser for the 'block_theme' tag.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockThemeTokenParser extends \Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param \Twig_Token $token A Twig_Token instance
     *
     * @return \Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $block = $this->parser->getExpressionParser()->parseExpression();

        if ($this->parser->getStream()->test(\Twig_Token::NAME_TYPE, 'with')) {
            $this->parser->getStream()->next();
            $resources = $this->parser->getExpressionParser()->parseExpression();

        } else {
            $resources = new \Twig_Node_Expression_Array(array(), $stream->getCurrent()->getLine());

            do {
                $resources->addElement($this->parser->getExpressionParser()->parseExpression());
            } while (!$stream->test(\Twig_Token::BLOCK_END_TYPE));
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new BlockThemeNode($block, $resources, $lineno, $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'block_theme';
    }
}
