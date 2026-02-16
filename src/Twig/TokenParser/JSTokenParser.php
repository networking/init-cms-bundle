<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Twig\TokenParser;

use Networking\InitCmsBundle\Twig\Node\JSNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class JSTokenParser extends AbstractTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @throws \Twig\Error\SyntaxError
     */
    public function parse(Token $token): JSNode
    {
        $stream = $this->parser->getStream();
        $value = $this->parser->parseExpression();
        $stream->expect(Token::BLOCK_END_TYPE);

        return new JSNode($value, $token->getLine());
    }

    /**
     * Gets the tag name associated with this token parser.
     */
    public function getTag(): string
    {
        return 'js_block';
    }
}
