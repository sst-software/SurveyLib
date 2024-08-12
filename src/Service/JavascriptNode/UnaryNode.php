<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class UnaryNode extends Node
{
    private const OPERATORS = [
        '!' => '!',
        'not' => '!',
        '+' => '+',
        '-' => '-',
    ];

    public function __construct(string $operator, Node $node)
    {
        parent::__construct(
            ['node' => $node],
            ['operator' => $operator],
        );
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('(')
            ->raw(self::OPERATORS[$this->attributes['operator']])
            ->compile($this->nodes['node'])
            ->raw(')')
        ;
    }
}
