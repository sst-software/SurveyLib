<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service;

use DateTimeImmutable;
use DateTimeInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sst\SurveyLibBundle\Event\AnswerCreate;
use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\CustomElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\DateTimeQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\MultipleChoiceGridQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\MultipleChoiceQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\NumberQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\QuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\ScaleQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\SubItems\MultipleChoiceGridQuestionQuestionInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementData\TextQuestionElementDataInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Service\ValidateAnswerServiceInterface;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidateAnswerService implements ValidateAnswerServiceInterface
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected ValidatorInterface $validator,
    ) {
    }

    public function validateAnswer(mixed $rawAnswer, ElementUsageInterface $elementUsage, AnswerInterface $answer): ConstraintViolationListInterface
    {
        $this->eventDispatcher->dispatch(
            new AnswerCreate($answer),
            AnswerCreate::PRE_VALIDATE,
        );

        $elementWithOverrides = $elementUsage->getElementWithOverrides();
        if ($answer->isSkipped()) {
            $constraints = $this->getConstraintsForSkippedElement($elementUsage, $answer);
        } else {
            $elementData = $elementWithOverrides->getElementData();
            if ($elementData === null) {
                throw new \InvalidArgumentException('Element data is null');
            }
            switch (true) {
                case $elementData instanceof TextQuestionElementDataInterface:
                    $constraints = $this->getConstraintsForTextElement($elementUsage, $answer);
                    break;
                case $elementData instanceof MultipleChoiceQuestionElementDataInterface:
                    $constraints = $this->getConstraintsForMultipleChoiceElement($elementUsage, $answer);
                    break;
                case $elementData instanceof MultipleChoiceGridQuestionElementDataInterface:
                    $constraints = $this->getConstraintsForMultipleChoiceGridElement($elementUsage, $answer);
                    break;
                case $elementData instanceof ScaleQuestionElementDataInterface:
                    $constraints = $this->getConstraintsForScaleElement($elementUsage, $answer);
                    break;
                case $elementData instanceof NumberQuestionElementDataInterface:
                    $constraints = $this->getConstraintsForNumberElement($elementUsage, $answer);
                    break;
                case $elementData instanceof DateTimeQuestionElementDataInterface:
                    $constraints = $this->getConstraintsForDateTimeElement($elementUsage, $answer);
                    break;
                case $elementData instanceof CustomElementDataInterface:
                    $constraints = $this->getConstraintsForCustomElement($elementUsage, $answer);
                    break;
                default:
                    $constraints = $this->getConstraintsForUnknownElement($elementUsage, $answer);
            }
        }

        $violations = $this->validator->startContext($answer)
            ->validate($rawAnswer, $constraints)
            ->getViolations()
        ;

        $this->eventDispatcher->dispatch(
            new AnswerCreate($answer, $violations),
            AnswerCreate::POST_VALIDATE,
        );
        return $violations;
    }

    /**
     * @return Constraint[]
     */
    protected function getConstraintsForUnknownElement(
        ElementUsageInterface $elementUsage,
        AnswerInterface $answer,
    ): array {
        //This function is called for all elements which have an unrecognized elementdata implementation
        //Override this method in case you need this, return an array of constraints
        return [];
    }

    protected function getConstraintsForSkippedElement(
        ElementUsageInterface $elementUsage,
        AnswerInterface $answer,
    ): array {
        return [
            new Blank(),
            new Callback(callback: function ($rawAnswer, ExecutionContextInterface $context, $payload): void {
                $elementData = $context->getRoot()?->getElementUsage()?->getElementWithOverrides()?->getElementData();
                if ($elementData === null) {
                    throw new \InvalidArgumentException('Answer cannot be validated because the element-data cannot be retrieved from the answer');
                }

                if ($elementData instanceof CustomElementDataInterface) {
                    $classMethods = get_class_methods($elementData->getElementData());
                    $elementDataHasRequiredMethod = in_array('getRequired', $classMethods, true);
                    if (!$elementDataHasRequiredMethod) {
                        return;
                    }
                    $required = $elementData->getElementData()->getRequired();
                    if (!$required) {
                        return;
                    }
                    if ($context->getRoot()->isSkipped()) {
                        $context
                            ->buildViolation('This element is required, so it cannot be skipped.')
                            ->addViolation()
                        ;
                    }
                    return;
                }

                if (!$elementData instanceof QuestionElementDataInterface) {
                    return;
                }

                $required = $elementData->getRequired();
                if (!$required) {
                    return;
                }
                if ($context->getRoot()->isSkipped()) {
                    $context
                        ->buildViolation('This element is required, so it cannot be skipped.')
                        ->addViolation()
                    ;
                }
            }),
        ];
    }

    /**
     * @return Constraint[]
     */
    protected function getConstraintsForTextElement(
        ElementUsageInterface $elementUsage,
        AnswerInterface $answer,
    ): array {
        $elementData = $elementUsage->getElementWithOverrides()->getElementData();
        if (!$elementData instanceof TextQuestionElementDataInterface) {
            throw new \InvalidArgumentException('Element is not a text question');
        }
        $result = [new Type('string')];
        if ($elementData->getRequired()) {
            $result[] = new NotBlank();
        }
        if (!$elementData->getIsLong()) {
            $result[] = new Length(['max' => 255]);
        }
        return $result;
    }

    /**
     * @return Constraint[]
     */
    protected function getConstraintsForMultipleChoiceElement(
        ElementUsageInterface $elementUsage,
        AnswerInterface $answer,
    ): array {
        $elementData = $elementUsage->getElementWithOverrides()->getElementData();
        if (!$elementData instanceof MultipleChoiceQuestionElementDataInterface) {
            throw new \InvalidArgumentException('Element is not a multiple-choice question');
        }

        $answerOptions = $elementData->getAnswerOptions();
        $answerOptionValues = [];
        foreach ($answerOptions as $answerOption) {
            $answerOptionValues[] = $answerOption->getValue();
        }
        $constraints = [new Choice(['choices' => $answerOptionValues, 'multiple' => $elementData->getMultipleAnswersAllowed()])];
        if ($elementData->getRequired()) {
            $constraints[] = new NotBlank();
        }
        return $constraints;
    }

    /**
     * @return Constraint[]
     */
    protected function getConstraintsForMultipleChoiceGridElement(
        ElementUsageInterface $elementUsage,
        AnswerInterface $answer,
    ): array {
        $elementData = $elementUsage->getElementWithOverrides()->getElementData();
        if (!$elementData instanceof MultipleChoiceGridQuestionElementDataInterface) {
            throw new \InvalidArgumentException('Element is not a multiple-choice-grid question');
        }

        return [
            new Type('array'),
            new Callback(callback: function ($rawAnswer, ExecutionContextInterface $context, $payload): void {
                $elementData = $context->getRoot()?->getElementUsage()?->getElementWithOverrides()?->getElementData();
                if ($elementData === null) {
                    throw new \InvalidArgumentException('Answer cannot be validated because the element-data cannot be retrieved from the answer');
                }

                foreach ($rawAnswer as $rawAnswerItem) {
                    if (!array_key_exists('uniqueQuestionIdentifier', $rawAnswerItem) || !array_key_exists('answer', $rawAnswerItem)) {
                        $context
                            ->buildViolation('This provided answer has answers with missing keys; either `uniqueQuestionIdentifier` or `answer` is missing.')
                            ->addViolation()
                        ;
                    }
                    if ($elementData->getMultipleAnswersAllowed() && !is_array($rawAnswerItem['answer'])) {
                        $context
                            ->buildViolation('This element supports multiple answers, so it expects an array as answer, but one of the provided answers has another type as answer.')
                            ->addViolation()
                        ;
                    }
                    if (!$elementData->getMultipleAnswersAllowed() && is_array($rawAnswerItem['answer'])) {
                        $context
                            ->buildViolation('This provided answer has an answer with multiple answers, but that is not allowed for this element.')
                            ->addViolation()
                        ;
                    }

                    $foundQuestions = array_filter(
                        $elementData->getQuestions(),
                        fn(MultipleChoiceGridQuestionQuestionInterface $question) => $question->getUniqueIdentifier() === $rawAnswerItem['uniqueQuestionIdentifier'],
                    );
                    if (count($foundQuestions) !== 1) {
                        $context
                            ->buildViolation('This provided answer has an answer with an unrecognized uniqueQuestionIdentifier "%identifier%"', ['%identifier%' => $rawAnswerItem['uniqueQuestionIdentifier']])
                            ->addViolation()
                        ;
                    }

                    $answers = (is_array($rawAnswerItem['answer'])) ? $rawAnswerItem['answer'] : [$rawAnswerItem['answer']];
                    foreach ($answers as $rawAnswerItemAnswer) {
                        $rawAnswerOptionFound = false;
                        foreach ($elementData->getAnswerOptions() as $answerOption) {
                            if ($answerOption->getValue() === $rawAnswerItemAnswer) {
                                $rawAnswerOptionFound = true;
                            }
                        }
                        if (!$rawAnswerOptionFound) {
                            $context
                                ->buildViolation('The value: %value% is not a valid choice.', ['%value%' => json_encode($rawAnswerItemAnswer)])
                                ->addViolation()
                            ;
                        }
                    }
                }
                if (count($rawAnswer) !== count($elementData->getQuestions())) {
                    $context
                        ->buildViolation('You need to answer all of the questions.')
                        ->addViolation()
                    ;
                }
            }),
        ];
    }

    /**
     * @return Constraint[]
     */
    protected function getConstraintsForNumberElement(
        ElementUsageInterface $elementUsage,
        AnswerInterface $answer,
    ): array {
        $elementData = $elementUsage->getElementWithOverrides()->getElementData();
        if (!$elementData instanceof NumberQuestionElementDataInterface) {
            throw new \InvalidArgumentException('Element is not a number question');
        }

        return [
            new Type(['type' => ['int', 'float']]),
            new GreaterThanOrEqual(['value' => $elementData->getMinimum()]),
            new LessThanOrEqual(['value' => $elementData->getMaximum()]),
        ];
    }

    /**
     * @return Constraint[]
     */
    protected function getConstraintsForDateTimeElement(
        ElementUsageInterface $elementUsage,
        AnswerInterface $answer,
    ): array {
        $elementData = $elementUsage->getElementWithOverrides()->getElementData();
        if (!$elementData instanceof DateTimeQuestionElementDataInterface) {
            throw new \InvalidArgumentException('Element is not a datetime question');
        }

        $result = [
            new Callback(callback: function ($rawAnswer, ExecutionContextInterface $context, $payload): void {
                if (!$rawAnswer instanceof DateTimeInterface) {
                    $context
                        ->buildViolation('This value should be of type {{ type }}.')
                        ->setParameter('{{ type }}', 'DateTime')
                        ->addViolation()
                    ;
                }
            }),
        ];
        if ($elementData->getMinimum() !== null) {
            $result[] = new GreaterThanOrEqual(['value' => $elementData->getMinimum()]);
        }

        if ($elementData->getMaximum() !== null) {
            $result[] = new LessThanOrEqual(['value' => $elementData->getMaximum()]);
        }

        if ($elementData->getMaxPast()) {
            $minimalDate = (new DateTimeImmutable())->sub($elementData->getMaxPast());
            $result[] = new GreaterThanOrEqual(['value' => $minimalDate]);
        }

        if ($elementData->getMaxFuture()) {
            $maximumDate = (new DateTimeImmutable())->add($elementData->getMaxFuture());
            $result[] = new LessThanOrEqual(['value' => $maximumDate]);
        }

        return $result;
    }

    /**
     * @return Constraint[]
     */
    protected function getConstraintsForScaleElement(
        ElementUsageInterface $elementUsage,
        AnswerInterface $answer,
    ): array {
        $elementData = $elementUsage->getElementWithOverrides()->getElementData();
        if (!$elementData instanceof ScaleQuestionElementDataInterface) {
            throw new \InvalidArgumentException('Element is not a scale question');
        }

        return [
            new Type(['type' => ['int', 'float']]),
            new GreaterThanOrEqual(['value' => $elementData->getMinimum()]),
            new LessThanOrEqual(['value' => $elementData->getMaximum()]),
        ];
    }

    /**
     * @return Constraint[]
     */
    protected function getConstraintsForCustomElement(
        ElementUsageInterface $elementUsage,
        AnswerInterface $answer,
    ): array {
        $elementData = $elementUsage->getElementWithOverrides()->getElementData();
        if (!$elementData instanceof CustomElementDataInterface) {
            throw new \InvalidArgumentException('Element is not a custom question');
        }
        //Custom elements don't have any default validations
        //Override this method in your own service, if you want to add validation for your own custom elements
        return [];
    }
}
