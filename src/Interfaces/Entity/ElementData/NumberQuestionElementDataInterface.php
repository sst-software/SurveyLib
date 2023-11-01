<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity\ElementData;

interface NumberQuestionElementDataInterface extends QuestionElementDataInterface
{
    public function getMinimum(): null|int|float;

    public function setMinimum(null|int|float $minimum): static;

    public function getMaximum(): null|int|float;

    public function setMaximum(null|int|float $maximum): static;

    public function getNumberOfDecimals(): int;

    public function setNumberOfDecimals(int $numberOfDecimals): static;
}
