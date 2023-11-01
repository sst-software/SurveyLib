<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ScaleQuestionElementDataInterface;

class ScaleQuestionElementData extends QuestionElementData implements ScaleQuestionElementDataInterface
{
    protected int|float $minimum = 0;

    protected int|float $maximum = 10;

    protected int|float $steps = 10;

    protected string $anchorMinimum = '';

    protected string $anchorMaximum = '';

    public function getMinimum(): int|float
    {
        return $this->minimum;
    }

    public function setMinimum(float|int $minimum): static
    {
        $this->minimum = $minimum;
        return $this;
    }

    public function getMaximum(): int|float
    {
        return $this->maximum;
    }

    public function setMaximum(float|int $maximum): static
    {
        $this->maximum = $maximum;
        return $this;
    }

    public function getSteps(): int|float
    {
        return $this->steps;
    }

    public function setSteps(float|int $steps): static
    {
        $this->steps = $steps;
        return $this;
    }

    public function getAnchorMinimum(): string
    {
        return $this->anchorMinimum;
    }

    public function setAnchorMinimum(string $anchorMinimum): static
    {
        $this->anchorMinimum = $anchorMinimum;
        return $this;
    }

    public function getAnchorMaximum(): string
    {
        return $this->anchorMaximum;
    }

    public function setAnchorMaximum(string $anchorMaximum): static
    {
        $this->anchorMaximum = $anchorMaximum;
        return $this;
    }
}
