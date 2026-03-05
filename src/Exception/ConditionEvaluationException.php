<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Exception;

use Exception;
use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;
use Throwable;

class ConditionEvaluationException extends Exception
{
    public function __construct(
        protected readonly ElementUsageInterface|ContainerInterface $displayItem,
        protected readonly SurveyResponseInterface $surveyResponse,
        protected readonly array $conditionData,
        protected readonly string $displayCondition,
        ?Throwable $previous = null,
    ) {
        $message = sprintf('Error evaluating condition %s for display item %s, %s, for survey response %s with condition data: %s', $this->displayCondition, $this->displayItem::class, $displayItem->getId(), $surveyResponse->getId(), json_encode($conditionData));
        parent::__construct($message, previous: $previous);
    }

    public function getDisplayItem(): ElementUsageInterface|ContainerInterface
    {
        return $this->displayItem;
    }

    public function getSurveyResponse(): SurveyResponseInterface
    {
        return $this->surveyResponse;
    }

    public function getConditionData(): array
    {
        return $this->conditionData;
    }
}
