<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\QuestionElementDataInterface;

class QuestionElementData extends ElementData implements QuestionElementDataInterface
{
    protected bool $required = true;

    protected mixed $defaultAnswer = null;

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): static
    {
        $this->required = $required;
        return $this;
    }

    public function getDefaultAnswer()
    {
        return $this->defaultAnswer;
    }

    public function setDefaultAnswer($defaultAnswer): static
    {
        $this->defaultAnswer = $defaultAnswer;
        return $this;
    }
}
