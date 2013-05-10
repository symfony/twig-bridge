<?php
/**
 * Created by JetBrains PhpStorm.
 * User: arthens
 * Date: 10/05/13
 * Time: 3:31 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Symfony\Bridge\Twig\Node;


class AutoescapedValue extends \Twig_Node_Print
{
    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->subcompile($this->getNode('expr'))
        ;
    }
}
