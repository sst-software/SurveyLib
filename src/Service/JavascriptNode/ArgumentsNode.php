<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service\JavascriptNode;

class ArgumentsNode extends ArrayNode
{
    public function compile(Compiler $compiler): void
    {
        $this->compileArguments($compiler);
    }
}
