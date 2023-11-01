<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Service;

use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;

interface DisplayConditionServiceInterface
{
    public function itemVisible(ElementUsageInterface|ContainerInterface $displayItem, SurveyResponseInterface $surveyResponse): bool;

    public function getPhpCondition(ElementUsageInterface|ContainerInterface $displayItem): string;

    public function getJavascriptCondition(ElementUsageInterface|ContainerInterface $displayItem): string;

    public function getConditionData(SurveyResponseInterface $surveyResponse): array;
}
