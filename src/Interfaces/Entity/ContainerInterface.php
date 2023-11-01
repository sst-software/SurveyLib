<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity;

use Doctrine\Common\Collections\Collection;

interface ContainerInterface
{
    public function getId(): ?int;

    public function getTitle(): ?string;

    public function setTitle(?string $title): static;

    public function getSortOrder(): int;

    public function setSortOrder(int $sortOrder): static;

    public function isShuffleElementUsages(): bool;

    public function setShuffleElementUsages(bool $shuffleElementUsages): static;

    public function getDisplayCondition(): ?string;

    public function setDisplayCondition(?string $displayCondition): static;

    public function getSurvey(bool $recursive = false): ?SurveyInterface;

    public function setSurvey(?SurveyInterface $survey): static;

    public function getParentContainer(bool $recursive = false): ?ContainerInterface;

    public function setParentContainer(?ContainerInterface $parentContainer): static;

    /**
     * @param bool $recursive
     * @return Collection<int, ContainerInterface>
     */
    public function getChildContainers(bool $recursive = false): Collection;

    public function addChildContainer(ContainerInterface $childContainer): static;

    public function removeChildContainer(ContainerInterface $childContainer): static;

    /**
     * @param bool $reshuffle don't check if already shuffled, just shuffle (when $this->shuffleElementUsages is true)
     * @return Collection<int, ElementUsageInterface>
     */
    public function getElementUsages(bool $reshuffle = false): Collection;

    public function addElementUsage(ElementUsageInterface $elementUsage): static;

    public function removeElementUsage(ElementUsageInterface $elementUsage): static;
}
