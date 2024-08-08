<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyInterface;

trait ContainerTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    protected int $sortOrder = 0;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    protected bool $shuffleElementUsages = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $displayCondition = null;

    #[ORM\ManyToOne(targetEntity: SurveyInterface::class, inversedBy: 'containers')]
    #[ORM\JoinColumn(nullable: true)]
    protected ?SurveyInterface $survey = null;

    #[ORM\ManyToOne(targetEntity: ContainerInterface::class, inversedBy: 'childContainers')]
    protected ?ContainerInterface $parentContainer = null;

    #[ORM\OneToMany(mappedBy: 'parentContainer', targetEntity: ContainerInterface::class, cascade: ['persist'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    protected Collection|array $childContainers = [];

    #[ORM\OneToMany(mappedBy: 'container', targetEntity: ElementUsageInterface::class, cascade: ['persist'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    protected Collection|array $elementUsages = [];

    protected bool $elementUsagesAreShuffled = false;

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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function isShuffleElementUsages(): bool
    {
        return $this->shuffleElementUsages;
    }

    public function setShuffleElementUsages(bool $shuffleElementUsages): static
    {
        $this->shuffleElementUsages = $shuffleElementUsages;

        return $this;
    }

    public function getDisplayCondition(): ?string
    {
        return $this->displayCondition;
    }

    public function setDisplayCondition(?string $displayCondition): static
    {
        $this->displayCondition = $displayCondition;

        return $this;
    }

    public function getSurvey(bool $recursive = false): ?SurveyInterface
    {
        if (!$recursive) {
            return $this->survey;
        }
        if ($this->getSurvey() !== null) {
            return $this->getSurvey();
        }
        if ($this->getParentContainer() !== null) {
            return $this->getParentContainer()->getSurvey(true);
        }
        return null;
    }

    public function setSurvey(?SurveyInterface $survey): static
    {
        $this->survey = $survey;

        return $this;
    }

    public function getParentContainer(bool $recursive = false): ?ContainerInterface
    {
        if (!$recursive) {
            return $this->parentContainer;
        }

        if ($this->parentContainer === null) {
            return $this;
        }
        return $this->parentContainer->getParentContainer(true);
    }

    public function setParentContainer(?ContainerInterface $parentContainer): static
    {
        $this->parentContainer = $parentContainer;

        return $this;
    }

    /**
     * @return Collection<int, ContainerInterface>
     */
    public function getChildContainers(bool $recursive = false): Collection
    {
        if (is_array($this->childContainers)) {
            $this->childContainers = new ArrayCollection();
        }
        if (!$recursive) {
            return $this->childContainers;
        }

        $result = $this->childContainers;
        foreach ($this->childContainers as $childContainer) {
            $result = new ArrayCollection(
                array_merge(
                    $result->toArray(),
                    $childContainer->getChildContainers(true)->toArray(),
                )
            );
        }
        return $result;
    }

    public function addChildContainer(ContainerInterface $childContainer): static
    {
        if ($this->getElementUsages()->count() > 0) {
            throw new InvalidArgumentException('This container contains element usages. A container can only have 1 type of children, which means that you cannot add childContainers to this container');
        }

        if (is_array($this->childContainers)) {
            $this->childContainers = new ArrayCollection();
        }
        if (!$this->childContainers->contains($childContainer)) {
            $this->childContainers->add($childContainer);
            $childContainer->setParentContainer($this);
        }

        return $this;
    }

    public function removeChildContainer(ContainerInterface $childContainer): static
    {
        if (is_array($this->childContainers)) {
            $this->childContainers = new ArrayCollection();
        }
        if ($this->childContainers->removeElement($childContainer)) {
            // set the owning side to null (unless already changed)
            if ($childContainer->getParentContainer() === $this) {
                $childContainer->setParentContainer(null);
            }
        }

        return $this;
    }

    /**
     * @param bool $reshuffle don't check if already shuffled, just shuffle (when $this->shuffleElementUsages is true)
     * @return Collection<int, ElementUsageInterface>
     */
    public function getElementUsages(bool $reshuffle = false): Collection
    {
        if (is_array($this->elementUsages)) {
            $this->elementUsages = new ArrayCollection();
        }
        if (
            !$this->isShuffleElementUsages() ||
            ($this->elementUsagesAreShuffled && !$reshuffle)
        ) {
            return $this->elementUsages;
        }

        $elementUsages = $this->elementUsages->toArray();
        shuffle($elementUsages);
        foreach ($elementUsages as $key => &$elementUsage) {
            $elementUsage->setSortOrder($key);
        }
        $this->elementUsagesAreShuffled = true;
        return new ArrayCollection($elementUsages);
    }

    public function addElementUsage(ElementUsageInterface $elementUsage): static
    {
        if ($this->getChildContainers()->count() > 0) {
            throw new InvalidArgumentException('This container contains childContainers. A container can only have 1 type of children, which means that you cannot add elementUsages to this container');
        }

        if (is_array($this->elementUsages)) {
            $this->elementUsages = new ArrayCollection();
        }

        if (!$this->elementUsages->contains($elementUsage)) {
            $this->elementUsages->add($elementUsage);
            $elementUsage->setContainer($this);
        }

        return $this;
    }

    public function removeElementUsage(ElementUsageInterface $elementUsage): static
    {
        if (is_array($this->elementUsages)) {
            $this->elementUsages = new ArrayCollection();
        }

        if ($this->elementUsages->removeElement($elementUsage)) {
            // set the owning side to null (unless already changed)
            if ($elementUsage->getContainer() === $this) {
                $elementUsage->setContainer(null);
            }
        }

        return $this;
    }
}
