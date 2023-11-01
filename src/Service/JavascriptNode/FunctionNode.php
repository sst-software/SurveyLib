<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class FunctionNode extends Node
{
    public function __construct(string $name, Node $arguments)
    {
        parent::__construct(
            ['arguments' => $arguments],
            ['name' => $name],
        );
    }

    public function compile(Compiler $compiler)
    {
        $arguments = [];
        foreach ($this->nodes['arguments']->nodes as $node) {
            $arguments[] = $compiler->subcompile($node);
        }

        $function = $compiler->getFunction($this->attributes['name']);

        $compiler->raw($function['compiler'](...$arguments));
    }
}
