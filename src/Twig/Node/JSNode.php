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
namespace Networking\InitCmsBundle\Twig\Node;

use Networking\InitCmsBundle\Twig\Extension\NetworkingHelperExtension;
use Twig\Node\Node;
use Twig\Compiler;
/**
 * Class JSNode.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class JSNode extends Node
{
    /**
     * @param Node $method
     * @param int      $lineno
     * @param null       $tag
     */
    public function __construct(Node $method, int $lineno, $tag = null)
    {
        parent::__construct(['method' => $method], [], $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Compiler A Twig_Compiler instance
     */
    public function compile(Compiler $compiler): void
    {
        $compiler
                ->addDebugInfo($this)
                ->write("print \$this->env->getExtension('".NetworkingHelperExtension::class."')->")
                ->raw($this->getNode('method')->getAttribute('value'))
                ->raw("();\n");
    }
}
