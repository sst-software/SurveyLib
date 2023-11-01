<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Service;

use Symfony\Component\ExpressionLanguage\Node\Node;

interface AstToJavascriptServiceInterface
{
    public function translateAstToJavascript(Node $ast): string;
}
