<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Service;

use Sst\SurveyLibBundle\Interfaces\Entity\SurveyInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;

interface CreateSurveyResponseServiceInterface
{
    public function createSurveyResponse(SurveyInterface $survey): SurveyResponseInterface;
}
