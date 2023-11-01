<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\CustomElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ElementDataInterface;

class CustomElementData extends ElementData implements CustomElementDataInterface
{
    protected string $subType = '';

    protected ?ElementDataInterface $elementData = null;

    public function getSubType(): string
    {
        return $this->subType;
    }

    public function setSubType(string $subType): static
    {
        $this->subType = $subType;
        return $this;
    }

    public function getElementData(): ?ElementDataInterface
    {
        return $this->elementData;
    }

    public function setElementData(?ElementDataInterface $elementData): static
    {
        $this->elementData = $elementData;
        return $this;
    }
}
