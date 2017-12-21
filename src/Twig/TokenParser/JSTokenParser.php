<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Twig\TokenParser;

use Twig_TokenParser;
use Twig_Token;
use Networking\InitCmsBundle\Twig\Node\JSNode;

/**
 * Class JSTokenParser
 * @package Networking\InitCmsBundle\Twig\TokenParser
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class JSTokenParser extends Twig_TokenParser
{

    /**
     * Parses a token and returns a node.
     *
     * @param \Twig_Token $token A \Twig_Token instance
     *
     * @return \Twig_NodeInterface A \Twig_NodeInterface instance
     */
    public function parse(\Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $value = $this->parser->getExpressionParser()->parseExpression();
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new JSNode($value, $token->getLine(), $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     */
    public function getTag()
    {
        return 'jsblock';
    }
}
