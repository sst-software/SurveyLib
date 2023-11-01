<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData\SubItems;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\SubItems\MultipleChoiceGridQuestionQuestionInterface;

class MultipleChoiceGridQuestionQuestion implements MultipleChoiceGridQuestionQuestionInterface
{
    protected string $text = '';

    protected int $order = -1;

    protected string $uniqueIdentifier = '';

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;
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

    public function getUniqueIdentifier(): string
    {
        return $this->uniqueIdentifier;
    }

    public function setUniqueIdentifier(string $uniqueIdentifier): static
    {
        $this->uniqueIdentifier = $uniqueIdentifier;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
