<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class ArrayNode extends Node
{
    protected $index;

    public function __construct()
    {
        $this->index = -1;
    }

    public function addElement(Node $value, ?Node $key = null): void
    {
        $key ??= new ConstantNode(++$this->index);

        array_push($this->nodes, $key, $value);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->raw('[');
        $this->compileArguments($compiler);
        $compiler->raw(']');
    }

    protected function getKeyValuePairs()
    {
        $pairs = [];
        foreach (array_chunk($this->nodes, 2) as $pair) {
            $pairs[] = ['key' => $pair[0], 'value' => $pair[1]];
        }

        return $pairs;
    }

    protected function compileArguments(Compiler $compiler)
    {
        $first = true;
        foreach ($this->getKeyValuePairs() as $pair) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $first = false;

            $compiler->compile($pair['value']);
        }
    }
}
