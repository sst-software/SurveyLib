<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity;

use Sst\SurveyLibBundle\Enums\ElementType;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ElementDataInterface;

interface ElementInterface
{
    public function getId(): ?int;

    public function getTitle(): ?string;

    public function setTitle(?string $title): static;

    public function getType(): ?ElementType;

    public function setType(ElementType $type): static;

    public function getText(): ?string;

    public function setText(?string $text): static;

    /**
     * @return string[]
     */
    public function getClasses(): array;

    /**
     * @param string[] $classes
     * @return static
     */
    public function setClasses(array $classes): static;

    public function getElementData(): ?ElementDataInterface;

    public function setElementData(ElementDataInterface $elementData): static;

    public function elementDataIsValidForElement(ElementDataInterface $elementData): bool;
}
