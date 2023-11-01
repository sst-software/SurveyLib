<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity\ElementData\SubItems;

use JsonSerializable;

interface MultipleChoiceQuestionAnswerOptionInterface extends JsonSerializable
{
    public function getText(): string;

    public function setText(string $text): static;

    public function getValue(): mixed;

    public function setValue(mixed $value): static;

    public function getOrder(): int;

    public function setOrder(int $order): static;
}
