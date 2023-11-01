<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;

trait SurveyTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: ContainerInterface::class)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    protected Collection|array $containers = [];

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyResponseInterface::class)]
    protected Collection|array $surveyResponses = [];

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $code = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param bool $recursive
     * @return Collection<int, ContainerInterface>
     */
    public function getContainers(bool $recursive = false): Collection
    {
        if (is_array($this->containers)) {
            $this->containers = new ArrayCollection();
        }
        if (!$recursive) {
            return $this->containers;
        }

        $result = $this->containers;
        foreach ($this->containers as $container) {
            $result = new ArrayCollection(
                array_merge(
                    $result->toArray(),
                    $container->getChildContainers(true)->toArray(),
                )
            );
        }
        return $result;
    }

    public function addContainer(ContainerInterface $container): static
    {
        if (is_array($this->containers)) {
            $this->containers = new ArrayCollection();
        }
        if (!$this->containers->contains($container)) {
            $this->containers->add($container);
            $container->setSurvey($this);
        }

        return $this;
    }

    public function removeContainer(ContainerInterface $container): static
    {
        if (is_array($this->containers)) {
            $this->containers = new ArrayCollection();
        }
        if ($this->containers->removeElement($container)) {
            // set the owning side to null (unless already changed)
            if ($container->getSurvey() === $this) {
                $container->setSurvey(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SurveyResponseInterface>
     */
    public function getSurveyResponses(): Collection
    {
        if (is_array($this->surveyResponses)) {
            $this->surveyResponses = new ArrayCollection();
        }
        return $this->surveyResponses;
    }

    public function addSurveyResponse(SurveyResponseInterface $surveyResponse): static
    {
        if (is_array($this->surveyResponses)) {
            $this->surveyResponses = new ArrayCollection();
        }
        if (!$this->surveyResponses->contains($surveyResponse)) {
            $this->surveyResponses->add($surveyResponse);
            $surveyResponse->setSurvey($this);
        }

        return $this;
    }

    public function removeSurveyResponse(SurveyResponseInterface $surveyResponse): static
    {
        if (is_array($this->surveyResponses)) {
            $this->surveyResponses = new ArrayCollection();
        }
        if ($this->surveyResponses->removeElement($surveyResponse)) {
            // set the owning side to null (unless already changed)
            if ($surveyResponse->getSurvey() === $this) {
                $surveyResponse->setSurvey(null);
            }
        }

        return $this;
    }
}
