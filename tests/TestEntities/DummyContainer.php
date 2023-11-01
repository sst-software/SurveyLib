<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\TestEntities;

use Sst\SurveyLibBundle\Entity\Traits\ContainerTrait;
use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;

class DummyContainer implements ContainerInterface
{
    use ContainerTrait;
}
