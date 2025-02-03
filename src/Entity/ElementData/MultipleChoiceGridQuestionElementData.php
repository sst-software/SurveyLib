<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData;

use InvalidArgumentException;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\MultipleChoiceGridQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\SubItems\MultipleChoiceGridQuestionQuestionInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\SubItems\MultipleChoiceQuestionAnswerOptionInterface;

class MultipleChoiceGridQuestionElementData extends ElementData implements MultipleChoiceGridQuestionElementDataInterface
{
    protected bool $multipleAnswersAllowed = false;

    /** @var MultipleChoiceQuestionAnswerOptionInterface[] */
    protected array $answerOptions = [];

    /** @var MultipleChoiceGridQuestionQuestionInterface[] */
    protected array $questions = [];

    public function getMultipleAnswersAllowed(): bool
    {
        return $this->multipleAnswersAllowed;
    }

    public function setMultipleAnswersAllowed(bool $allowMultipleAnswers): static
    {
        $this->multipleAnswersAllowed = $allowMultipleAnswers;
        return $this;
    }

    /**
     * @return MultipleChoiceQuestionAnswerOptionInterface[]
     */
    public function getAnswerOptions(): array
    {
        return $this->answerOptions;
    }

    /**
     * @param MultipleChoiceQuestionAnswerOptionInterface[] $answerOptions
     * @return $this
     */
    public function setAnswerOptions(array $answerOptions): static
    {
        foreach ($answerOptions as $answerOption) {
            if (!$answerOption instanceof MultipleChoiceQuestionAnswerOptionInterface) {
                throw new InvalidArgumentException('Answer options must be of type MultipleChoiceQuestionAnswerOptionInterface');
            }
        }

        $this->answerOptions = $answerOptions;
        return $this;
    }

    public function addAnswerOption(MultipleChoiceQuestionAnswerOptionInterface $answerOption): static
    {
        $this->answerOptions[] = $answerOption;
        return $this;
    }

    /**
     * @return MultipleChoiceGridQuestionQuestionInterface[]
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    /**
     * @param MultipleChoiceGridQuestionQuestionInterface[] $questions
     * @return $this
     */
    public function setQuestions(array $questions): static
    {
        foreach ($questions as $question) {
            if (!$question instanceof MultipleChoiceGridQuestionQuestionInterface) {
                throw new InvalidArgumentException('Answer options must be of type MultipleChoiceGridQuestionQuestionInterface');
            }
            if (array_key_exists($question->getUniqueIdentifier(), $this->questions)) {
                throw new InvalidArgumentException('Question with this unique identifier already exists');
            }
        }

        $this->questions = $questions;
        return $this;
    }

    public function addQuestion(MultipleChoiceGridQuestionQuestionInterface $question): static
    {
        if (array_key_exists($question->getUniqueIdentifier(), $this->questions)) {
            throw new InvalidArgumentException('Question with this unique identifier already exists');
        }
        $this->questions[$question->getUniqueIdentifier()] = $question;
        return $this;
    }
}
