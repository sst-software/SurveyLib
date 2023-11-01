<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class BinaryNode extends Node
{
    private const OPERATORS = [
        '~' => '+',
        'and' => '&&',
        'or' => '||',
    ];

    private const FUNCTIONS = [
        'in' => 'includes',
        'contains' => 'includes',
        'starts with' => 'startsWith',
        'ends with' => 'endsWith',
    ];

    public function __construct(string $operator, Node $left, Node $right)
    {
        parent::__construct(
            ['left' => $left, 'right' => $right],
            ['operator' => $operator],
        );
    }

    public function compile(Compiler $compiler)
    {
        $operator = $this->attributes['operator'];

        if ('matches' == $operator) {
            $compiler
                ->compile($this->nodes['left'])
                ->raw('.match(')
                ->compile($this->nodes['right'])
                ->raw(')')
            ;

            return;
        }

        if (isset(self::FUNCTIONS[$operator])) {
            $compiler
                ->compile($this->nodes['right'])
                ->raw(sprintf('.%s(', self::FUNCTIONS[$operator]))
                ->compile($this->nodes['left'])
                ->raw(')')
            ;

            return;
        }

        if ($operator === 'not in') {
            $compiler
                ->raw('!(')
                ->compile($this->nodes['right'])
                ->raw('.includes(')
                ->compile($this->nodes['left'])
                ->raw(')')
                ->raw(')')
            ;

            return;
        }

        if ($operator === 'pow') {
            $compiler
                ->raw('Math.pow(')
                ->compile($this->nodes['left'])
                ->raw(', ')
                ->compile($this->nodes['right'])
                ->raw(')')
            ;

            return;
        }

        if ($operator === '..') {
            $compiler
                ->raw('Array.from({length: ')
                ->compile($this->nodes['right'])
                ->raw(' - ')
                ->compile($this->nodes['left'])
                ->raw(' + 1 }, (v, k) => k + ')
                ->compile($this->nodes['left'])
                ->raw(')')
            ;
            return;
        }

        if (isset(self::OPERATORS[$operator])) {
            $operator = self::OPERATORS[$operator];
        }

        $compiler
            ->raw('(')
            ->compile($this->nodes['left'])
            ->raw(' ')
            ->raw($operator)
            ->raw(' ')
            ->compile($this->nodes['right'])
            ->raw(')')
        ;
    }
}
