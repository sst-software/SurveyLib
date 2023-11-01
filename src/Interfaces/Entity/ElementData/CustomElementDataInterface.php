<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity\ElementData;

interface CustomElementDataInterface extends ElementDataInterface
{
    public function getSubType(): string;

    public function setSubType(string $subType): static;

    public function getElementData(): ?ElementDataInterface;

    public function setElementData(?ElementDataInterface $elementData): static;
}
