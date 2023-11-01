<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity\ElementData\SubItems;

use JsonSerializable;

interface MultipleChoiceGridQuestionQuestionInterface extends JsonSerializable
{
    public function getText(): string;

    public function setText(string $text): static;

    public function getOrder(): int;

    public function setOrder(int $order): static;

    public function getUniqueIdentifier(): string;

    public function setUniqueIdentifier(string $uniqueIdentifier): static;
}
