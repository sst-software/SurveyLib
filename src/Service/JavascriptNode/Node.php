<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class Node
{
    public $nodes = [];
    public $attributes = [];

    public function __construct(
        array $nodes = [],
        array $attributes = [],
    ) {
        $this->nodes = $nodes;
        $this->attributes = $attributes;
    }

    public function compile(Compiler $compiler)
    {
        foreach ($this->nodes as $node) {
            $node->compile($compiler);
        }
    }
}
