<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData\SubItems;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\SubItems\MultipleChoiceQuestionAnswerOptionInterface;

class MultipleChoiceQuestionAnswerOption implements MultipleChoiceQuestionAnswerOptionInterface
{
    protected string $text = '';

    protected mixed $value = null;

    protected int $order = -1;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
