<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\TestEntities;

use Sst\SurveyLibBundle\Entity\Traits\SurveyResponseTrait;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;

class DummySurveyResponse implements SurveyResponseInterface
{
    use SurveyResponseTrait;
}
