<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yorkie
 * Date: 18.10.12
 * Time: 11:13
 * To change this template use File | Settings | File Templates.
 */
namespace Networking\InitCmsBundle\Twig\Node;

class JSNode extends \Twig_Node
{
    /**
     * @param  \Twig_NodeInterface $value
     * @param  integer             $lineno
     * @param  string              $tag    (optional)
     * @return void
     */
    public function __construct(\Twig_NodeInterface $method, $lineno, $tag = null)
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
