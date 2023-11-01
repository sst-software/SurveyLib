<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity;

use JsonSerializable;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ElementDataInterface;

interface ElementOverrideInterface extends JsonSerializable
{
    public function getTitle(): string|false|null;

    public function setTitle(string|null|false $title): static;

    public function getText(): string|false|null;

    public function setText(string|false|null $text): static;

    /**
     * @return string[]|false|null
     */
    public function getClasses(): array|false|null;

    /**
     * @param string[]|false|null $classes
     * @return static
     */
    public function setClasses(array|false|null $classes): static;

    public function getElementData(): ElementDataInterface|false|null;

    public function setElementData(ElementDataInterface|false|null $elementData): static;
}
