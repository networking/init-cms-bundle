<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Twig\Node;

/**
 * Class JSNode
 * @package Networking\InitCmsBundle\Twig\Node
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class JSNode extends \Twig_Node
{
    /**
     * @param \Twig_Node $method
     * @param array $lineno
     * @param null $tag
     */
    public function __construct(\Twig_Node $method, $lineno, $tag = null)
    {
        parent::__construct(array('method' => $method), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
                ->addDebugInfo($this)
                ->write("print \$this->env->getExtension('networking_init_cms_helper')->")
                ->raw($this->getNode('method')->getAttribute('value'))
                ->raw("();\n");
        ;
    }
}
