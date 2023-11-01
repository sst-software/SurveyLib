<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class AnswerValidationException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        protected readonly ?ConstraintViolationListInterface $constraintViolationList = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getConstraintViolationList(): ?ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }
}
