<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class ConstantNode extends Node
{
    public readonly bool $isNullSafe;

    public function __construct(
        mixed $value,
        bool $isIdentifier = false,
        bool $isNullSafe = false,
    ) {
        //$isIdentifier is not used, but we keep it, to keep the same signature as the original class
        $this->isNullSafe = $isNullSafe;
        parent::__construct(
            [],
            ['value' => $value],
        );
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->repr($this->attributes['value']);
    }
}
