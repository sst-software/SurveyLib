<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Service;

use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;

interface NextElementServiceInterface
{
    public function getNextElement(
        SurveyResponseInterface $surveyResponse,
        ElementUsageInterface|ContainerInterface|null $lastPresentedItem = null,
        bool $reverse = false,
    ): ?ElementUsageInterface;
}
