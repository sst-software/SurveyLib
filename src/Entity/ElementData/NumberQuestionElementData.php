<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\NumberQuestionElementDataInterface;

class NumberQuestionElementData extends QuestionElementData implements NumberQuestionElementDataInterface
{
    protected null|int|float $minimum = 0;

    protected null|int|float $maximum = 10;

    protected int $numberOfDecimals = 0;

    public function getMinimum(): null|int|float
    {
        return $this->minimum;
    }

    public function setMinimum(null|float|int $minimum): static
    {
        $this->minimum = $minimum;
        return $this;
    }

    public function getMaximum(): null|int|float
    {
        return $this->maximum;
    }

    public function setMaximum(null|float|int $maximum): static
    {
        $this->maximum = $maximum;
        return $this;
    }

    public function getNumberOfDecimals(): int
    {
        return $this->numberOfDecimals;
    }

    public function setNumberOfDecimals(int $numberOfDecimals): static
    {
        $this->numberOfDecimals = $numberOfDecimals;
        return $this;
    }
}
