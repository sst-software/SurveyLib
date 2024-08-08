<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Types;

use Doctrine\DBAL\Types\JsonType;

class ElementOverrideType extends JsonType
{
    public const ELEMENT_OVERRIDE_TYPE = 'elementOverride';
}
