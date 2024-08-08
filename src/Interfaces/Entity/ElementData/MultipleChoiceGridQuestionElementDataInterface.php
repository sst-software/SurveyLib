<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity\ElementData;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\SubItems\MultipleChoiceQuestionAnswerOptionInterface;

interface MultipleChoiceGridQuestionElementDataInterface extends ElementDataInterface
{
    public function getMultipleAnswersAllowed(): bool;

    public function setMultipleAnswersAllowed(bool $allowMultipleAnswers): static;

    /**
     * @return MultipleChoiceQuestionAnswerOptionInterface[]
     */
    public function getAnswerOptions(): array;

    /**
     * @param MultipleChoiceQuestionAnswerOptionInterface[] $answerOptions
     * @return $this
     */
    public function setAnswerOptions(array $answerOptions): static;

    public function addAnswerOption(MultipleChoiceQuestionAnswerOptionInterface $answerOption): static;

    /**
     * @return MultipleChoiceGridQuestionQuestionInterface[]
     */
    public function getQuestions(): array;

    /**
     * @param MultipleChoiceGridQuestionQuestionInterface[] $questions
     * @return $this
     */
    public function setQuestions(array $questions): static;
}
