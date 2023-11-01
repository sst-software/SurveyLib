<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sst\SurveyLibBundle\Event\AnswerCreate;
use Sst\SurveyLibBundle\Exception\AnswerValidationException;
use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\DateTimeQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\NumberQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;
use Sst\SurveyLibBundle\Interfaces\Service\AddAnswerServiceInterface;
use Sst\SurveyLibBundle\Interfaces\Service\ValidateAnswerServiceInterface;

class AddAnswerService implements AddAnswerServiceInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected EventDispatcherInterface $eventDispatcher,
        protected ValidateAnswerServiceInterface $validateAnswerService,
    ) {
    }

    /**
     * @param SurveyResponseInterface $surveyResponse
     * @param array{elementUsage: ElementUsageInterface, rawAnswer: mixed, skipped: bool} $answers
     * @return array{elementUsage: ElementUsageInterface, answer: ?AnswerInterface}[]
     * @throws AnswerValidationException
     */
    public function addAnswers(SurveyResponseInterface $surveyResponse, array $answers): array
    {
        $result = [];
        foreach ($answers as $answer) {
            if (!array_key_exists('elementUsage', $answer) || !array_key_exists('rawAnswer', $answer) || !array_key_exists('skipped', $answer)) {
                throw new InvalidArgumentException('Invalid answer format');
            }
            try {
                $resultItem = [
                    'elementUsage' => $answer['elementUsage'],
                    'answer' => $this->addAnswer(
                        $surveyResponse,
                        $answer['elementUsage'],
                        $answer['rawAnswer'],
                        $answer['skipped'],
                    ),
                    'validationException' => null,
                ];
            } catch (AnswerValidationException $e) {
                $resultItem = [
                    'elementUsage' => $answer['elementUsage'],
                    'answer' => null,
                    'validationException' => $e,
                ];
            }

            $result[] = $resultItem;
        }
        return $result;
    }

    /**
     * @param SurveyResponseInterface $surveyResponse
     * @param ElementUsageInterface $elementUsage
     * @param mixed $rawAnswer
     * @param bool $skipped
     * @return AnswerInterface
     * @throws AnswerValidationException
     */
    public function addAnswer(
        SurveyResponseInterface $surveyResponse,
        ElementUsageInterface $elementUsage,
        mixed $rawAnswer,
        bool $skipped = false,
    ): AnswerInterface {
        $answer = $this->getAnswer($surveyResponse, $elementUsage);

        $convertedRawAnswer = $this->convertRawAnswer($rawAnswer, $elementUsage->getElementWithOverrides());

        $violations = $this->validateAnswerService->validateAnswer($convertedRawAnswer, $elementUsage, $answer);
        if ($violations->count() > 0) {
            $errorStrings = [];
            foreach ($violations as $violation) {
                $errorStrings[] = $violation->getMessage();
            }
            $stringRawAnswer = (is_array($convertedRawAnswer)) ? print_r($convertedRawAnswer, true) : (string)$convertedRawAnswer;
            throw new AnswerValidationException(
                message: sprintf('%s is not a valid answer for elementUsage %s: %s', $stringRawAnswer, $elementUsage->getCode(), implode(', ', $errorStrings)),
                constraintViolationList: $violations,
            );
        }

        $this->eventDispatcher->dispatch(
            new AnswerCreate($answer),
            ($answer->getId()) ? AnswerCreate::PRE_UPDATE : AnswerCreate::PRE_CREATE,
        );

        if ($skipped) {
            $answer->setSkipped(true);
        } else {
            $answer->setAnswer($convertedRawAnswer);
        }

        if (!($surveyResponse->getAnswers()->contains($answer))) {
            $surveyResponse->addAnswer($answer);
        }
        if (!($elementUsage->getAnswers()->contains($answer))) {
            $elementUsage->addAnswer($answer);
        }

        $this->eventDispatcher->dispatch(
            new AnswerCreate($answer),
            ($answer->getId()) ? AnswerCreate::POST_UPDATE : AnswerCreate::POST_CREATE,
        );
        return $answer;
    }

    protected function convertRawAnswer(mixed $rawAnswer, ElementInterface $elementWithOverrides): mixed
    {
        $elementData = $elementWithOverrides->getElementData();
        if ($elementData instanceof NumberQuestionElementDataInterface) {
            return $this->convertRawAnswerForNumberQuestion($rawAnswer, $elementData);
        }
        if ($elementData instanceof DateTimeQuestionElementDataInterface) {
            return $this->convertRawAnswerForDateTimeQuestion($rawAnswer, $elementData);
        }

        //Only Number and DateTime questions need conversion by default, override this method if you want to convert more types of answers
        return $rawAnswer;
    }

    protected function convertRawAnswerForDateTimeQuestion(?DateTimeInterface $rawAnswer, DateTimeQuestionElementDataInterface $elementData): mixed
    {
        if ($rawAnswer === null) {
            return null;
        }
        if (!$elementData->getIncludeTime()) {
            return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $rawAnswer->format('Y-m-d') . ' 00:00:00');
        }
        return $rawAnswer;
    }

    protected function convertRawAnswerForNumberQuestion(mixed $rawAnswer, NumberQuestionElementDataInterface $elementData): mixed
    {
        if ($rawAnswer === null) {
            return null;
        }

        $numberOfDecimals = $elementData->getNumberOfDecimals();
        $rounded = round($rawAnswer, $numberOfDecimals);
        if ($numberOfDecimals === 0) {
            return (int)$rounded;
        }
        return $rounded;
    }

    protected function getAnswer(SurveyResponseInterface $surveyResponse, ElementUsageInterface $elementUsage): AnswerInterface
    {
        foreach ($surveyResponse->getAnswers() as $answer) {
            if ($answer->getElementUsage() === $elementUsage) {
                return $answer;
            }
        }

        /** @var AnswerInterface $newEntity */
        $newEntity = $this->getNewEntity(AnswerInterface::class);
        $newEntity->setElementUsage($elementUsage);
        return $newEntity;
    }

    protected function getNewEntity(string $interface): object
    {
        $className = $this->entityManager->getClassMetadata($interface)->getName();
        return new $className();
    }
}
