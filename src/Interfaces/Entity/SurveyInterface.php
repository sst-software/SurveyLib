<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity;

use Doctrine\Common\Collections\Collection;

interface SurveyInterface
{
    public function getId(): ?int;

    public function getTitle(): ?string;

    public function setTitle(?string $title): static;

    public function getCode(): ?string;

    public function setCode(?string $code): static;

    /**
     * @param bool $recursive
     * @return Collection<int, ContainerInterface>
     */
    public function getContainers(bool $recursive = false): Collection;

    public function addContainer(ContainerInterface $container): static;

    public function removeContainer(ContainerInterface $container): static;

    /**
     * @return Collection<int, SurveyResponseInterface>
     */
    public function getSurveyResponses(): Collection;

    public function addSurveyResponse(SurveyResponseInterface $surveyResponse): static;

    public function removeSurveyResponse(SurveyResponseInterface $surveyResponse): static;
}
