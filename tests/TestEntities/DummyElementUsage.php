<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\TestEntities;

use Sst\SurveyLibBundle\Entity\Traits\ElementUsageTrait;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;

class DummyElementUsage implements ElementUsageInterface
{
    use ElementUsageTrait;
}
