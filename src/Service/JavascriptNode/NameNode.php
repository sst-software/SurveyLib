<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class NameNode extends Node
{
    public function __construct(string $name)
    {
        parent::__construct(
            [],
            ['name' => $name],
        );
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->raw($this->attributes['name']);
    }
}
