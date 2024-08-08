<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Event;

use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\EventDispatcher\Event;

class AnswerCreate extends Event
{
    public function __construct(
        protected AnswerInterface $answer,
        protected ?ConstraintViolationListInterface $validationViolations = null,
    ) {
    }

    public function getValidationViolations(): ?ConstraintViolationListInterface
    {
        return $this->validationViolations;
    }

    public function getAnswer(): AnswerInterface
    {
        return $this->answer;
    }

    public const PRE_CREATE = 'sstSurveyLib.entities.answer.pre_create';
    public const POST_CREATE = 'sstSurveyLib.entities.answer.post_create';
    public const PRE_UPDATE = 'sstSurveyLib.entities.answer.pre_update';
    public const POST_UPDATE = 'sstSurveyLib.entities.answer.post_update';
    public const PRE_VALIDATE = 'sstSurveyLib.entities.answer.pre_validate';
    public const POST_VALIDATE = 'sstSurveyLib.entities.answer.post_validate';
}
