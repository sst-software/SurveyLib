<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Service;

use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;

interface AddAnswerServiceInterface
{
    /**
     * @param array{elementUsage: ElementUsageInterface, rawAnswer: mixed, skipped: bool} $answers
     * @return array{elementUsage: ElementUsageInterface, answer: ?AnswerInterface}[]
     * @throws AnswerValidationException
     */
    public function addAnswers(
        SurveyResponseInterface $surveyResponse,
        array $answers,
    ): array;

    /**
     * @throws AnswerValidationException
     */
    public function addAnswer(
        SurveyResponseInterface $surveyResponse,
        ElementUsageInterface $elementUsage,
        mixed $rawAnswer,
        bool $skipped = false,
    ): AnswerInterface;
}
