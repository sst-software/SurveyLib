<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementOverrideInterface;

/**
 * Class with data that overrides the data of an Element
 * Set values to false when you want to use the original data of the element
 * Set to a value or false, when you want to override the original data of the element with this new value
 */
class ElementOverride implements ElementOverrideInterface
{
    protected string|false|null $title = false;

    protected string|false|null $text = false;

    protected array|false|null $classes = false;

    protected ElementDataInterface|false|null $elementData = false;

    public function getTitle(): string|false|null
    {
        return $this->title;
    }

    public function setTitle(string|false|null $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): string|false|null
    {
        return $this->text;
    }

    public function setText(string|false|null $text): static
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string[]|false|null
     */
    public function getClasses(): array|false|null
    {
        return $this->classes;
    }

    /**
     * @param string[]|false|null $classes
     * @return static
     */
    public function setClasses(array|false|null $classes): static
    {
        $this->classes = $classes;

        return $this;
    }

    public function getElementData(): ElementDataInterface|false|null
    {
        return $this->elementData;
    }

    public function setElementData(ElementDataInterface|false|null $elementData): static
    {
        $this->elementData = $elementData;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
