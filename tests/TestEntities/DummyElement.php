<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\TestEntities;

use Sst\SurveyLibBundle\Entity\Traits\ElementTrait;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementInterface;

class DummyElement implements ElementInterface
{
    use ElementTrait;
}
