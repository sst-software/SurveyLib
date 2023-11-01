<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity\ElementData;

interface QuestionElementDataInterface extends ElementDataInterface
{
    public function getRequired(): bool;

    public function setRequired(bool $required): static;

    public function getDefaultAnswer();

    public function setDefaultAnswer($defaultAnswer): static;
}
