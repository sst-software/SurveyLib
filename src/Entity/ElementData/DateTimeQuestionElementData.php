<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData;

use DateInterval;
use DateTimeInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\DateTimeQuestionElementDataInterface;

class DateTimeQuestionElementData extends QuestionElementData implements DateTimeQuestionElementDataInterface
{
    protected bool $includeTime = false;

    protected ?DateTimeInterface $minimum = null;

    protected ?DateTimeInterface $maximum = null;

    protected ?DateInterval $maxPast = null;

    protected ?DateInterval $maxFuture = null;

    public function getIncludeTime(): bool
    {
        return $this->includeTime;
    }

    public function setIncludeTime(bool $includeTime): static
    {
        $this->includeTime = $includeTime;
        return $this;
    }

    public function getMinimum(): ?DateTimeInterface
    {
        return $this->minimum;
    }

    public function setMinimum(?DateTimeInterface $minimum): static
    {
        $this->minimum = $minimum;
        return $this;
    }

    public function getMaximum(): ?DateTimeInterface
    {
        return $this->maximum;
    }

    public function setMaximum(?DateTimeInterface $maximum): static
    {
        $this->maximum = $maximum;
        return $this;
    }

    public function getMaxPast(): ?DateInterval
    {
        return $this->maxPast;
    }

    public function setMaxPast(?DateInterval $maxPast): static
    {
        $this->maxPast = $maxPast;
        return $this;
    }

    public function getMaxFuture(): ?DateInterval
    {
        return $this->maxFuture;
    }

    public function setMaxFuture(?DateInterval $maxFuture): static
    {
        $this->maxFuture = $maxFuture;
        return $this;
    }
}
