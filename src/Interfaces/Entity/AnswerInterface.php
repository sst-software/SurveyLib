<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity;

interface AnswerInterface extends TimestampableInterface
{
    public function getId(): ?int;

    public function getAnswer(): mixed;

    public function setAnswer(mixed $answer): static;

    public function isSkipped(): bool;

    public function setSkipped(bool $skipped): static;

    public function getSurveyResponse(): ?SurveyResponseInterface;

    public function setSurveyResponse(?SurveyResponseInterface $surveyResponse): static;

    public function getElementUsage(): ?ElementUsageInterface;

    public function setElementUsage(?ElementUsageInterface $elementUsage): static;
}
