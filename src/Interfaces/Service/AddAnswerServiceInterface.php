<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Service;

use Sst\SurveyLibBundle\Exception\AnswerValidationException;
use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;

interface AddAnswerServiceInterface
{
    /**
     * @param SurveyResponseInterface $surveyResponse
     * @param array{elementUsage: ElementUsageInterface, rawAnswer: mixed, skipped: bool} $answers
     * @return array{elementUsage: ElementUsageInterface, answer: ?AnswerInterface}[]
     * @throws AnswerValidationException
     */
    public function addAnswers(
        SurveyResponseInterface $surveyResponse,
        array $answers,
    ): array;

    /**
     * @param SurveyResponseInterface $surveyResponse
     * @param ElementUsageInterface $elementUsage
     * @param mixed $rawAnswer
     * @param bool $skipped
     * @return AnswerInterface
     * @throws AnswerValidationException
     */
    public function addAnswer(
        SurveyResponseInterface $surveyResponse,
        ElementUsageInterface $elementUsage,
        mixed $rawAnswer,
        bool $skipped = false,
    ): AnswerInterface;
}
