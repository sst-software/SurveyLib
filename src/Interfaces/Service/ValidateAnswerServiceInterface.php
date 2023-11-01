<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Service;

use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ValidateAnswerServiceInterface
{
    public function validateAnswer(mixed $rawAnswer, ElementUsageInterface $elementUsage, AnswerInterface $answer): ConstraintViolationListInterface;
}
