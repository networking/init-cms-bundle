<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Twig\TokenParser;

use Twig\TokenParser\AbstractTokenParser;
use Networking\InitCmsBundle\Twig\Node\JSNode;
use Twig\Token;
/**
 * Class JSTokenParser.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class JSTokenParser extends AbstractTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     *
     * @return JSNode
     */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();
        $value = $this->parser->getExpressionParser()->parseExpression();
        $stream->expect(Token::BLOCK_END_TYPE);

        return new JSNode($value, $token->getLine(), $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     */
    public function getTag(): string
    {
        return 'jsblock';
    }
}
