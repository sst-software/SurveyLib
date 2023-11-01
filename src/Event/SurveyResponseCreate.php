<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Event;

use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SurveyResponseCreate extends Event
{
    public function __construct(protected SurveyResponseInterface $surveyResponse)
    {
    }

    public function getSurveyResponse(): SurveyResponseInterface
    {
        return $this->surveyResponse;
    }

    public const PRE_CREATE = 'sstSurveyLib.entities.surveyResponse.pre_create';
    public const POST_CREATE = 'sstSurveyLib.entities.surveyResponse.post_create';
}
