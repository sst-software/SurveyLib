<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\TestEntities;

use Sst\SurveyLibBundle\Entity\Traits\AnswerTrait;
use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;

class DummyAnswer implements AnswerInterface
{
    use AnswerTrait;
}
