<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;

interface SurveyResponseInterface extends TimestampableInterface
{
    public function getId(): ?int;

    public function getStartDateTime(): ?DateTimeImmutable;

    public function setStartDateTime(DateTimeImmutable $startDateTime): static;

    public function getCompletedDateTime(): ?DateTimeImmutable;

    public function setCompletedDateTime(?DateTimeImmutable $completedDateTime): static;

    public function getShuffledElementUsageSortOrders(): array;

    public function setShuffledElementUsageSortOrders(array $shuffledElementUsageSortOrders): static;

    public function getSurvey(): ?SurveyInterface;

    public function setSurvey(?SurveyInterface $survey): static;

    /**
     * @return Collection<int, AnswerInterface>
     */
    public function getAnswers(): Collection;

    public function addAnswer(AnswerInterface $answer): static;

    public function removeAnswer(AnswerInterface $answer): static;
}
