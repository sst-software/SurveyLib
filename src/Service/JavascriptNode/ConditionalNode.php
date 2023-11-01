<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class ConditionalNode extends Node
{
    public function __construct(Node $expr1, Node $expr2, Node $expr3)
    {
        parent::__construct(
            ['expr1' => $expr1, 'expr2' => $expr2, 'expr3' => $expr3],
        );
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->raw('((')
            ->compile($this->nodes['expr1'])
            ->raw(') ? (')
            ->compile($this->nodes['expr2'])
            ->raw(') : (')
            ->compile($this->nodes['expr3'])
            ->raw('))')
        ;
    }
}
