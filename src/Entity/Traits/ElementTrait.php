<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Sst\SurveyLibBundle\Enums\ElementType;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\CustomElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\DateTimeQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\MultipleChoiceGridQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\MultipleChoiceQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\NumberQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ScaleQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\TextQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Types\ElementDataType;

trait ElementTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, enumType: ElementType::class)]
    protected ?ElementType $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $text = null;

    #[ORM\Column(length: 255)]
    protected string $classes = '';

    #[ORM\Column(type: ElementDataType::ELEMENT_DATA_TYPE, nullable: true)]
    protected ?ElementDataInterface $elementData = null;

    #[ORM\OneToMany(mappedBy: 'element', targetEntity: ElementUsageInterface::class)]
    protected Collection|array $elementUsages = [];

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

    public function getType(): ?ElementType
    {
        return $this->type;
    }

    public function setType(ElementType $type): static
    {
        $this->type = $type;

        // When changing the type, we need to make sure the elementData is compatible with the new type
        if ($this->getElementData()) {
            try {
                $this->setElementData($this->getElementData());
            } catch (InvalidArgumentException) {
                $this->setElementData(null);
            }
        }

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return explode(',', $this->classes);
    }

    /**
     * @param string[] $classes
     * @return static
     */
    public function setClasses(array $classes): static
    {
        $this->classes = implode(',', $classes);

        return $this;
    }

    public function getElementData(): ?ElementDataInterface
    {
        return $this->elementData;
    }

    public function setElementData(?ElementDataInterface $elementData): static
    {
        if ($elementData === null || $this->getType() === null) {
            $this->elementData = null;
            return $this;
        }

        if (!$this->elementDataIsValidForElement($elementData)) {
            throw new InvalidArgumentException('Given elementData does not match with type ' . $this->getType()->value);
        }

        $this->elementData = $elementData;

        return $this;
    }

    /**
     * @return Collection<int, ElementUsageInterface>
     */
    public function getElementUsages(): Collection
    {
        if (is_array($this->elementUsages)) {
            $this->elementUsages = new ArrayCollection();
        }
        return $this->elementUsages;
    }

    public function elementDataIsValidForElement(ElementDataInterface $elementData): bool
    {
        $expectedInterface = match ($this->getType()) {
            ElementType::TEXT => TextQuestionElementDataInterface::class,
            ElementType::NUMBER => NumberQuestionElementDataInterface::class,
            ElementType::DATETIME => DateTimeQuestionElementDataInterface::class,
            ElementType::MULTIPLE_CHOICE => MultipleChoiceQuestionElementDataInterface::class,
            ElementType::MULTIPLE_CHOICE_GRID => MultipleChoiceGridQuestionElementDataInterface::class,
            ElementType::SCALE => ScaleQuestionElementDataInterface::class,
            ElementType::INFO => ElementDataInterface::class,
            ElementType::CUSTOM => CustomElementDataInterface::class,
        };

        return in_array($expectedInterface, class_implements($elementData));
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }
}
