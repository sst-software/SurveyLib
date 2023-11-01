<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\TextQuestionElementDataInterface;

class TextQuestionElementData extends QuestionElementData implements TextQuestionElementDataInterface
{
    protected bool $isLong = false;

    public function getIsLong(): bool
    {
        return $this->isLong;
    }

    public function setIsLong(bool $isLong): static
    {
        $this->isLong = $isLong;
        return $this;
    }
}
