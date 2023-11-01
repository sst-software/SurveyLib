<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\ElementData;

use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ElementDataInterface;

class ElementData implements ElementDataInterface
{
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
