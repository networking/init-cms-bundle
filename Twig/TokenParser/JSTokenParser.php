<?php
namespace Networking\InitCmsBundle\Twig\TokenParser;
/**
 * Created by JetBrains PhpStorm.
 * User: yorkie
 * Date: 18.10.12
 * Time: 11:02
 * To change this template use File | Settings | File Templates.
 */

use Twig_TokenParser;
use Twig_Token;
use Networking\InitCmsBundle\Twig\Node\JSNode;

class JSTokenParser extends Twig_TokenParser
{

    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
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
     * @param string The tag name
     */
    public function getTag()
    {
        return 'jsblock';
    }
}
