<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity;

use Doctrine\Common\Collections\Collection;

interface ElementUsageInterface
{
    public function getId(): ?int;

    public function getSortOrder(): int;

    public function setSortOrder(int $sortOrder): static;

    public function getDisplayCondition(): ?string;

    public function setDisplayCondition(?string $displayCondition): static;

    /**
     * @return string unique code to identify the element usage
     */
    public function getCode(): string;

    public function setCode(string $code): static;

    public function getContainer(): ?ContainerInterface;

    public function setContainer(?ContainerInterface $container): static;

    public function getElement(): ?ElementInterface;

    public function setElement(?ElementInterface $element): static;

    public function elementCanBeAdded(?ElementInterface $element): bool;

    public function getElementOverride(): ?ElementOverrideInterface;

    public function setElementOverride(?ElementOverrideInterface $elementOverride): static;

    /**
     * @return Collection<int, AnswerInterface>
     */
    public function getAnswers(): Collection;

    public function addAnswer(AnswerInterface $answer): static;

    public function removeAnswer(AnswerInterface $answer): static;

    public function getElementWithOverrides(): ?ElementInterface;
}
