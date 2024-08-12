<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service;

use Sst\SurveyLibBundle\Interfaces\Service\AstToJavascriptServiceInterface;
use Symfony\Component\ExpressionLanguage\Node\ArgumentsNode;
use Symfony\Component\ExpressionLanguage\Node\ArrayNode;
use Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use Symfony\Component\ExpressionLanguage\Node\ConditionalNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\FunctionNode;
use Symfony\Component\ExpressionLanguage\Node\GetAttrNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;
use Symfony\Component\ExpressionLanguage\Node\Node;
use Symfony\Component\ExpressionLanguage\Node\NullCoalesceNode;
use Symfony\Component\ExpressionLanguage\Node\UnaryNode;

class AstToJavascriptService implements AstToJavascriptServiceInterface
{
    public function translateAstToJavascript(Node $ast): string
    {
        $translatedAst = $this->replaceNodesForJavascriptEquivalents($ast);
        $compiler = new JavascriptNode\Compiler([]);
        return $compiler->compile($translatedAst)->getSource();
    }

    protected function replaceNodesForJavascriptEquivalents(Node $node): mixed
    {
        switch (true) {
            case $node instanceof ArgumentsNode:
                $argumentsNode = new JavascriptNode\ArgumentsNode();
                foreach (array_chunk($node->nodes, 2) as $pair) {
                    $argumentsNode->addElement($this->replaceNodesForJavascriptEquivalents($pair[1]), $this->replaceNodesForJavascriptEquivalents($pair[0]));
                }
                return $argumentsNode;
            case $node instanceof ArrayNode:
                $arrayNode = new JavascriptNode\ArrayNode();
                foreach (array_chunk($node->nodes, 2) as $pair) {
                    $arrayNode->addElement($this->replaceNodesForJavascriptEquivalents($pair[1]), $this->replaceNodesForJavascriptEquivalents($pair[0]));
                }
                return $arrayNode;
            case $node instanceof BinaryNode:
                return new JavascriptNode\BinaryNode(
                    $node->attributes['operator'],
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['left']),
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['right']),
                );
            case $node instanceof ConditionalNode:
                return new JavascriptNode\ConditionalNode(
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['expr1']),
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['expr2']),
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['expr3']),
                );
            case $node instanceof ConstantNode:
                return new JavascriptNode\ConstantNode($node->attributes['value'], false, $node->isNullSafe);
            case $node instanceof FunctionNode:
                return new JavascriptNode\FunctionNode(
                    $node->attributes['name'],
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['arguments']),
                );
            case $node instanceof GetAttrNode:
                return new JavascriptNode\GetAttrNode(
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['node']),
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['attribute']),
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['arguments']),
                    $node->attributes['type'],
                );
            case $node instanceof NameNode:
                return new JavascriptNode\NameNode($node->attributes['name']);
            case $node instanceof NullCoalesceNode:
                return new JavascriptNode\NullCoalesceNode(
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['expr1']),
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['expr2']),
                );
            case $node instanceof UnaryNode:
                return new JavascriptNode\UnaryNode(
                    $node->attributes['operator'],
                    $this->replaceNodesForJavascriptEquivalents($node->nodes['node']),
                );
            case $node instanceof Node:
                $subNodes = [];
                foreach ($node->nodes as $subNode) {
                    $subNodes[] = $this->replaceNodesForJavascriptEquivalents($subNode);
                }
                return new JavascriptNode\Node($subNodes, $node->attributes);
            default:
                return $node;
        }
    }
}
