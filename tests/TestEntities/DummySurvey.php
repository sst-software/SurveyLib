<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\TestEntities;

use Sst\SurveyLibBundle\Entity\Traits\SurveyTrait;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyInterface;

class DummySurvey implements SurveyInterface
{
    use SurveyTrait;
}
