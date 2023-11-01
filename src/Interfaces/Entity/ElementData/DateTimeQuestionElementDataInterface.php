<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity\ElementData;

interface DateTimeQuestionElementDataInterface extends QuestionElementDataInterface
{
    public function getIncludeTime(): bool;

    public function setIncludeTime(bool $includeTime): static;

    public function getMinimum(): ?\DateTimeInterface;

    public function setMinimum(?\DateTimeInterface $minimum): static;

    public function getMaximum(): ?\DateTimeInterface;

    public function setMaximum(?\DateTimeInterface $maximum): static;

    public function getMaxPast(): ?\DateInterval;

    public function setMaxPast(?\DateInterval $maxPast): static;

    public function getMaxFuture(): ?\DateInterval;

    public function setMaxFuture(?\DateInterval $maxFuture): static;
}
