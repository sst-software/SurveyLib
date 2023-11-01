<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity\ElementData;

interface TextQuestionElementDataInterface extends QuestionElementDataInterface
{
    public function getIsLong(): bool;

    public function setIsLong(bool $isLong): static;
}
