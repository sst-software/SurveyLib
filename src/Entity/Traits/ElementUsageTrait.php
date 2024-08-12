<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementOverrideInterface;
use Sst\SurveyLibBundle\Types\ElementOverrideType;

trait ElementUsageTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(nullable: false, options: ['default' => 0])]
    protected int $sortOrder = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $displayCondition = null;

    // Please note; in order to let conditions work properly, this code should be unique, at least per survey.
    #[ORM\Column(length: 255)]
    protected string $code = '';

    #[ORM\ManyToOne(inversedBy: 'elementUsages')]
    #[ORM\JoinColumn(nullable: true)]
    protected ?ContainerInterface $container = null;

    #[ORM\ManyToOne(targetEntity: ElementInterface::class, cascade: ['persist'], inversedBy: 'elementUsages')]
    #[ORM\JoinColumn(nullable: true)]
    protected ?ElementInterface $element = null;

    #[ORM\Column(type: ElementOverrideType::ELEMENT_OVERRIDE_TYPE, nullable: true)]
    protected ?ElementOverrideInterface $elementOverride = null;

    #[ORM\OneToMany(mappedBy: 'elementUsage', targetEntity: AnswerInterface::class)]
    protected Collection|array $answers = [];

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDisplayCondition(): ?string
    {
        return $this->displayCondition;
    }

    public function setDisplayCondition(?string $displayCondition): static
    {
        $this->displayCondition = $displayCondition;

        return $this;
    }

    /**
     * @return string unique code to identify the element usage
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Please note; in order to let conditions work properly, this code should be unique, at least per survey.
     * @return $this
     */
    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function setContainer(?ContainerInterface $container): static
    {
        $this->container = $container;

        return $this;
    }

    public function getElement(): ?ElementInterface
    {
        return $this->element;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setElement(?ElementInterface $element): static
    {
        if (!$this->elementCanBeAdded($element)) {
            throw new InvalidArgumentException('Element already in use in the same survey.');
        }

        $this->element = $element;

        return $this;
    }

    public function elementCanBeAdded(?ElementInterface $element): bool
    {
        $container = $this->getContainer();
        if ($container === null || $element === null) {
            return true;
        }
        $survey = $container->getSurvey(true);
        if ($survey === null) {
            return true;
        }
        foreach ($survey->getContainers(true) as $container) {
            foreach ($container->getElementUsages() as $elementUsage) {
                if ($elementUsage->getElement() === $element) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getElementOverride(): ?ElementOverrideInterface
    {
        return $this->elementOverride;
    }

    public function setElementOverride(?ElementOverrideInterface $elementOverride): static
    {
        if ($elementOverride?->getElementData() !== null &&
            $this->getElement() !== null &&
            !$this->getElement()->elementDataIsValidForElement($elementOverride->getElementData())
        ) {
            throw new InvalidArgumentException('Element data override is not valid for this element.');
        }
        $this->elementOverride = $elementOverride;
        return $this;
    }

    /**
     * @return Collection<int, AnswerInterface>
     */
    public function getAnswers(): Collection
    {
        if (is_array($this->answers)) {
            $this->answers = new ArrayCollection();
        }
        return $this->answers;
    }

    public function addAnswer(AnswerInterface $answer): static
    {
        if (is_array($this->answers)) {
            $this->answers = new ArrayCollection();
        }
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setElementUsage($this);
        }

        return $this;
    }

    public function removeAnswer(AnswerInterface $answer): static
    {
        if (is_array($this->answers)) {
            $this->answers = new ArrayCollection();
        }
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getElementUsage() === $this) {
                $answer->setElementUsage(null);
            }
        }

        return $this;
    }

    public function getElementWithOverrides(): ?ElementInterface
    {
        if ($this->getElement() === null) {
            return null;
        }
        $overrideData = $this->getElementOverride();
        if ($overrideData === null) {
            return $this->getElement();
        }

        $element = clone $this->getElement();

        $titleOverride = $overrideData->getTitle();
        if ($titleOverride !== false) {
            $element->setTitle($titleOverride);
        }

        $textOverride = $overrideData->getText();
        if ($textOverride !== false) {
            $element->setText($textOverride);
        }

        $classesOverride = $overrideData->getClasses();
        if ($classesOverride !== false) {
            $element->setClasses($classesOverride);
        }

        $elementDataOverride = $overrideData->getElementData();
        if ($elementDataOverride !== false) {
            $element->setElementData($elementDataOverride);
        }
        return $element;
    }
}
