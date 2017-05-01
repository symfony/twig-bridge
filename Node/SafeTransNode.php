<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\Node;

/**
 * @author Giacomo Gatelli<arthens@gmail.com>
 */
class SafeTransNode extends \Twig_Node
{
    public function __construct(\Twig_NodeInterface $message, \Twig_NodeInterface $domain = null, \Twig_Node_Expression $count = null, \Twig_Node_Expression $params = null, \Twig_Node_Expression $locale = null, $lineno = 0, $tag = null)
    {
        parent::__construct(array('count' => $count, 'message' => $message, 'domain' => $domain, 'params' => $params, 'locale' => $locale), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $message = $this->getNode('message');
        $params = $this->getNode('params');
        $method = null === $this->getNode('count') ? 'trans' : 'transChoice';

        $compiler
            ->write('echo $this->env->getExtension(\'translator\')->getTranslator()->'.$method.'(')
            ->subcompile($message)
        ;

        $compiler->raw(', ');

        if (null !== $this->getNode('count')) {
            $compiler
                ->subcompile($this->getNode('count'))
                ->raw(', ')
            ;
        }

        $compiler->subcompile($params);

        $compiler->raw(', ');

        if (null === $this->getNode('domain')) {
            $compiler->repr('messages');
        } else {
            $compiler->subcompile($this->getNode('domain'));
        }

        if (null !== $this->getNode('locale')) {
            $compiler
                ->raw(', ')
                ->subcompile($this->getNode('locale'))
            ;
        }
        $compiler->raw(");\n");
    }
}
