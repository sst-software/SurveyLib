<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData;

use InvalidArgumentException;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\MultipleChoiceQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\SubItems\MultipleChoiceQuestionAnswerOptionInterface;

class MultipleChoiceQuestionElementData extends QuestionElementData implements MultipleChoiceQuestionElementDataInterface
{
    protected bool $multipleAnswersAllowed = false;

    protected array $answerOptions = [];

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
}
