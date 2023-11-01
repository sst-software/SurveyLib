<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity\ElementData;

interface ScaleQuestionElementDataInterface extends QuestionElementDataInterface
{
    public function getMinimum(): int|float;

    public function setMinimum(int|float $minimum): static;

    public function getMaximum(): int|float;

    public function setMaximum(int|float $maximum): static;

    public function getSteps(): int|float;

    public function setSteps(int|float $steps): static;

    public function getAnchorMinimum(): string;

    public function setAnchorMinimum(string $anchorMinimum): static;

    public function getAnchorMaximum(): string;

    public function setAnchorMaximum(string $anchorMaximum): static;
}
