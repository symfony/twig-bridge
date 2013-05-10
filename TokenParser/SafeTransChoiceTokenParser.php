<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\TokenParser;

use Symfony\Bridge\Twig\Node\TransNode;
use Symfony\Bridge\Twig\Node\AutoescapedValue;

/**
 * Token Parser for the 'transchoice' tag.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SafeTransChoiceTokenParser extends TransTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param \Twig_Token $token A Twig_Token instance
     *
     * @return \Twig_NodeInterface A Twig_NodeInterface instance
     *
     * @throws \Twig_Error_Syntax
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $count = $this->parser->getExpressionParser()->parseExpression();

        $domain = null;
        $locale = null;

        if ($stream->test('from')) {
            // {% transchoice count from "messages" %}
            $stream->next();
            $domain = $this->parser->getExpressionParser()->parseExpression();
        }

        if ($stream->test('into')) {
            // {% transchoice count into "fr" %}
            $stream->next();
            $locale =  $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(array($this, 'decideTransChoiceFork'), true);

        list($message, $params) = $this->convertToMessageAndParams($body);

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new TransNode($message, $domain, $count, $params, $locale, $lineno, $this->getTag());
    }

    public function decideTransChoiceFork($token)
    {
        return $token->test(array('endsafetranschoice'));
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'safetranschoice';
    }

    /**
     * Convert the body of the block into a message and a list of params to be injected in the translated message
     */
    private function convertToMessageAndParams(\Twig_NodeInterface $body)
    {
        $message = "";
        $params = array();
        $count = 0;

        $children = $body instanceof \Twig_Node_Text ? array($body) : $body->getIterator();
        foreach ($children as $child)
        {
            if ($child instanceof \Twig_Node_Text)
            {
                $message .= $child->getAttribute('data');
            }
            else if ($child instanceof \Twig_Node_Print)
            {
                $placeHolder = '%' . ++$count . '%';

                $params[] = new \Twig_Node_Expression_Constant($placeHolder, $body->getLine());
                $params[] = new AutoescapedValue($child->getNode('expr'), $body->getLine());

                $message .= $placeHolder;
            }
            else
            {
                throw new \Twig_Error_Syntax('A message inside a trans tag must be a simple text or an expression');
            }
        }

        return array(
            new \Twig_Node_Expression_Constant(trim($message), $body->getLine()),
            new \Twig_Node_Expression_Array($params, $body->getLine()),
        );
    }
}
