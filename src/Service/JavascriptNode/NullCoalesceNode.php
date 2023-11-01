<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class NullCoalesceNode extends Node
{
    public function __construct(Node $expr1, Node $expr2)
    {
        parent::__construct(['expr1' => $expr1, 'expr2' => $expr2]);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->raw('((')
            ->compile($this->nodes['expr1'])
            ->raw(') ?? (')
            ->compile($this->nodes['expr2'])
            ->raw('))')
        ;
    }
}
