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
use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * Class JSNode.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
#[YieldReady]
class JSNode extends Node
{
    /**
     * @param Node $method
     * @param int $lineno
     */
    public function __construct(Node $method, int $lineno)
    {
        parent::__construct(['method' => $method], [], $lineno);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Compiler $compiler
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
