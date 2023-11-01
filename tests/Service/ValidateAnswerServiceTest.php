<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Service;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sst\SurveyLibBundle\Entity\ElementData\CustomElementData;
use Sst\SurveyLibBundle\Entity\ElementData\DateTimeQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\MultipleChoiceGridQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\MultipleChoiceQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\NumberQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\ScaleQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\SubItems\MultipleChoiceGridQuestionQuestion;
use Sst\SurveyLibBundle\Entity\ElementData\SubItems\MultipleChoiceQuestionAnswerOption;
use Sst\SurveyLibBundle\Entity\ElementData\TextQuestionElementData;
use Sst\SurveyLibBundle\Enums\ElementType;
use Sst\SurveyLibBundle\Service\ValidateAnswerService;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyAnswer;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElement;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElementUsage;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidateAnswerServiceTest extends TestCase
{
    public function testInvalidSkippedAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::MULTIPLE_CHOICE);
        $elementUsage->setElement($element);

        $elementData = new MultipleChoiceQuestionElementData();
        $elementData->addAnswerOption((new MultipleChoiceQuestionAnswerOption())->setValue(1));
        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $answer->setSkipped(true);
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $context = $this->createMock(ExecutionContextInterface::class);
        $context
            ->expects($this->exactly(2))
            ->method('getRoot')
            ->willReturn($answer)
        ;

        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $context
            ->expects($this->once())
            ->method('buildViolation')
            ->with('This element is required, so it cannot be skipped.')
            ->willReturn($constraintViolationBuilder)
        ;
        $constraintViolationBuilder
            ->expects($this->once())
            ->method('addViolation')
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($context, $answer, $contextualValidator) {
                $this->assertCount(2, $constraints);
                $this->assertInstanceOf(Blank::class, $constraints[0]);
                $this->assertInstanceOf(Callback::class, $constraints[1]);

                $constraints[1]->callback->__invoke($rawAnswer, $context, null);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $result = $service->validateAnswer(1, $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testValidSkippedAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::MULTIPLE_CHOICE);
        $elementUsage->setElement($element);

        $elementData = new MultipleChoiceQuestionElementData();
        $elementData->addAnswerOption((new MultipleChoiceQuestionAnswerOption())->setValue(1));
        $elementData->setRequired(false);
        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $answer->setSkipped(true);
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $context = $this->createMock(ExecutionContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getRoot')
            ->willReturn($answer)
        ;

        $context
            ->expects($this->never())
            ->method('buildViolation')
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($context, $answer, $contextualValidator) {
                $this->assertCount(2, $constraints);
                $this->assertInstanceOf(Blank::class, $constraints[0]);
                $this->assertInstanceOf(Callback::class, $constraints[1]);

                $constraints[1]->callback->__invoke($rawAnswer, $context, null);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $result = $service->validateAnswer(null, $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testMultipleChoiceAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::MULTIPLE_CHOICE);
        $elementUsage->setElement($element);

        $elementData = new MultipleChoiceQuestionElementData();
        $elementData->addAnswerOption((new MultipleChoiceQuestionAnswerOption())->setValue(1));
        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($contextualValidator) {
                $this->assertCount(1, $constraints);
                $this->assertInstanceOf(Choice::class, $constraints[0]);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $result = $service->validateAnswer(2, $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testMultipleChoiceGridAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::MULTIPLE_CHOICE_GRID);
        $elementUsage->setElement($element);

        $elementData = new MultipleChoiceGridQuestionElementData();
        $elementData->setMultipleAnswersAllowed(false);
        $elementData->setAnswerOptions([
            (new MultipleChoiceQuestionAnswerOption())->setValue('test1'),
            (new MultipleChoiceQuestionAnswerOption())->setValue('test2'),
            (new MultipleChoiceQuestionAnswerOption())->setValue('test3'),
        ]);
        $elementData->setQuestions([
            (new MultipleChoiceGridQuestionQuestion())->setUniqueIdentifier('q1'),
            (new MultipleChoiceGridQuestionQuestion())->setUniqueIdentifier('q2'),
            (new MultipleChoiceGridQuestionQuestion())->setUniqueIdentifier('q3'),
        ]);

        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $answer->setSkipped(false);
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $context = $this->createMock(ExecutionContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getRoot')
            ->willReturn($answer)
        ;

        $context
            ->expects($this->never())
            ->method('buildViolation')
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($context, $answer, $contextualValidator) {
                $this->assertCount(2, $constraints);
                $this->assertInstanceOf(Type::class, $constraints[0]);
                $this->assertInstanceOf(Callback::class, $constraints[1]);

                $constraints[1]->callback->__invoke($rawAnswer, $context, null);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $rawAnswer = [
            [
                'uniqueQuestionIdentifier' => 'q1',
                'answer' => 'test1',
            ],
            [
                'uniqueQuestionIdentifier' => 'q2',
                'answer' => 'test2',
            ],
        ];


        $result = $service->validateAnswer($rawAnswer, $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testMultipleMultipleChoiceGridAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::MULTIPLE_CHOICE_GRID);
        $elementUsage->setElement($element);

        $elementData = new MultipleChoiceGridQuestionElementData();
        $elementData->setMultipleAnswersAllowed(true);
        $elementData->setAnswerOptions([
            (new MultipleChoiceQuestionAnswerOption())->setValue('test1'),
            (new MultipleChoiceQuestionAnswerOption())->setValue('test2'),
            (new MultipleChoiceQuestionAnswerOption())->setValue('test3'),
        ]);
        $elementData->setQuestions([
            (new MultipleChoiceGridQuestionQuestion())->setUniqueIdentifier('q1'),
            (new MultipleChoiceGridQuestionQuestion())->setUniqueIdentifier('q2'),
            (new MultipleChoiceGridQuestionQuestion())->setUniqueIdentifier('q3'),
        ]);


        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $answer->setSkipped(false);
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $context = $this->createMock(ExecutionContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getRoot')
            ->willReturn($answer)
        ;

        $context
            ->expects($this->never())
            ->method('buildViolation')
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($context, $answer, $contextualValidator) {
                $this->assertCount(2, $constraints);
                $this->assertInstanceOf(Type::class, $constraints[0]);
                $this->assertInstanceOf(Callback::class, $constraints[1]);

                $constraints[1]->callback->__invoke($rawAnswer, $context, null);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $rawAnswer = [
            [
                'uniqueQuestionIdentifier' => 'q1',
                'answer' => ['test1', 'test2'],
            ],
            [
                'uniqueQuestionIdentifier' => 'q2',
                'answer' => ['test2'],
            ],
        ];


        $result = $service->validateAnswer($rawAnswer, $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testInvalidMultipleChoiceGridAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::MULTIPLE_CHOICE_GRID);
        $elementUsage->setElement($element);

        $elementData = new MultipleChoiceGridQuestionElementData();
        $elementData->setMultipleAnswersAllowed(false);
        $elementData->setAnswerOptions([
            (new MultipleChoiceQuestionAnswerOption())->setValue('test1'),
            (new MultipleChoiceQuestionAnswerOption())->setValue('test2'),
            (new MultipleChoiceQuestionAnswerOption())->setValue('test3'),
        ]);
        $elementData->setQuestions([
            (new MultipleChoiceGridQuestionQuestion())->setUniqueIdentifier('q1'),
            (new MultipleChoiceGridQuestionQuestion())->setUniqueIdentifier('q2'),
            (new MultipleChoiceGridQuestionQuestion())->setUniqueIdentifier('q3'),
        ]);

        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $answer->setSkipped(false);
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $context = $this->createMock(ExecutionContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getRoot')
            ->willReturn($answer)
        ;

        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $context
            ->expects($this->once())
            ->method('buildViolation')
            ->with('The value you selected is not a valid choice.')
            ->willReturn($constraintViolationBuilder)
        ;

        $constraintViolationBuilder
            ->expects($this->once())
            ->method('addViolation')
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($context, $answer, $contextualValidator) {
                $this->assertCount(2, $constraints);
                $this->assertInstanceOf(Type::class, $constraints[0]);
                $this->assertInstanceOf(Callback::class, $constraints[1]);

                $constraints[1]->callback->__invoke($rawAnswer, $context, null);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $rawAnswer = [
            [
                'uniqueQuestionIdentifier' => 'q1',
                'answer' => 'test1',
            ],
            [
                'uniqueQuestionIdentifier' => 'q2',
                'answer' => 'doesNotExist',
            ],
        ];

        $result = $service->validateAnswer($rawAnswer, $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testTextAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::TEXT);
        $elementUsage->setElement($element);

        $elementData = new TextQuestionElementData();
        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($contextualValidator) {
                $this->assertCount(3, $constraints);
                $this->assertInstanceOf(Type::class, $constraints[0]);
                $this->assertEquals('string', $constraints[0]->type);
                $this->assertInstanceOf(NotBlank::class, $constraints[1]);
                $this->assertInstanceOf(Length::class, $constraints[2]);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $result = $service->validateAnswer('text', $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testScaleAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::SCALE);
        $elementUsage->setElement($element);

        $elementData = (new ScaleQuestionElementData())
            ->setMinimum(3)
            ->setMaximum(8)
        ;
        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($contextualValidator) {
                $this->assertCount(3, $constraints);
                $this->assertInstanceOf(Type::class, $constraints[0]);
                $this->assertEquals(['int', 'float'], $constraints[0]->type);
                $this->assertInstanceOf(GreaterThanOrEqual::class, $constraints[1]);
                $this->assertInstanceOf(LessThanOrEqual::class, $constraints[2]);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $result = $service->validateAnswer(3, $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testNumberAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::NUMBER);
        $elementUsage->setElement($element);

        $elementData = (new NumberQuestionElementData())
            ->setMinimum(3)
            ->setMaximum(8)
        ;
        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($contextualValidator) {
                $this->assertCount(3, $constraints);
                $this->assertInstanceOf(Type::class, $constraints[0]);
                $this->assertEquals(['int', 'float'], $constraints[0]->type);
                $this->assertInstanceOf(GreaterThanOrEqual::class, $constraints[1]);
                $this->assertInstanceOf(LessThanOrEqual::class, $constraints[2]);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $result = $service->validateAnswer(3, $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testDateTimeAnswer(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::DATETIME);
        $elementUsage->setElement($element);

        $minimum = new DateTimeImmutable('2020-02-28 13:04:53');
        $maximum = new DateTimeImmutable('2020-12-31 13:04:53');
        $elementData = (new DateTimeQuestionElementData())
            ->setMinimum($minimum)
            ->setMaximum($maximum)
            ->setMaxFuture(new \DateInterval('P10D'))
            ->setMaxPast(new \DateInterval('P10D'))
        ;
        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($maximum, $minimum, $contextualValidator) {
                $this->assertCount(5, $constraints);
                $this->assertInstanceOf(Callback::class, $constraints[0]);
                $this->assertInstanceOf(GreaterThanOrEqual::class, $constraints[1]);
                $this->assertInstanceOf(LessThanOrEqual::class, $constraints[2]);
                $this->assertInstanceOf(GreaterThanOrEqual::class, $constraints[3]);
                $this->assertInstanceOf(LessThanOrEqual::class, $constraints[4]);

                $this->assertEquals($minimum->format('Y-m-d H:i:s'), $constraints[1]->value->format('Y-m-d H:i:s'));
                $this->assertEquals($maximum->format('Y-m-d H:i:s'), $constraints[2]->value->format('Y-m-d H:i:s'));

                $tenDaysInFuture = (new DateTimeImmutable())->add(new \DateInterval('P10D'));
                $tenDaysInPast = (new DateTimeImmutable())->sub(new \DateInterval('P10D'));

                $this->assertEquals($tenDaysInPast->format('Y-m-d H:i:s'), $constraints[3]->value->format('Y-m-d H:i:s'));
                $this->assertEquals($tenDaysInFuture->format('Y-m-d H:i:s'), $constraints[4]->value->format('Y-m-d H:i:s'));
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $result = $service->validateAnswer(new DateTimeImmutable(), $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }

    public function testRequiredCustomElement(): void
    {
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::CUSTOM);
        $elementUsage->setElement($element);

        $elementData = new CustomElementData();
        $subElementData = new TextQuestionElementData();
        $elementData->setElementData($subElementData);
        $element->setElementData($elementData);

        $answer = new DummyAnswer();
        $answer->setSkipped(true);
        $elementUsage->addAnswer($answer);

        $constraintViolationList = new ConstraintViolationList();

        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidator
            ->expects($this->once())
            ->method('getViolations')
            ->willReturn($constraintViolationList)
        ;

        $context = $this->createMock(ExecutionContextInterface::class);
        $context
            ->expects($this->exactly(2))
            ->method('getRoot')
            ->willReturn($answer)
        ;

        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $context
            ->expects($this->once())
            ->method('buildViolation')
            ->with('This element is required, so it cannot be skipped.')
            ->willReturn($constraintViolationBuilder)
        ;
        $constraintViolationBuilder
            ->expects($this->once())
            ->method('addViolation')
        ;

        $contextualValidator
            ->method('validate')
            ->will($this->returnCallback(function ($rawAnswer, $constraints) use ($context, $answer, $contextualValidator) {
                $this->assertCount(2, $constraints);
                $this->assertInstanceOf(Blank::class, $constraints[0]);
                $this->assertInstanceOf(Callback::class, $constraints[1]);

                $constraints[1]->callback->__invoke($rawAnswer, $context, null);
                return $contextualValidator;
            }))
        ;

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('startContext')
            ->willReturn($contextualValidator)
        ;

        $service = new ValidateAnswerService(
            $this->createMock(EventDispatcherInterface::class),
            $validator
        );

        $result = $service->validateAnswer(null, $elementUsage, $answer);
        $this->assertEquals($constraintViolationList, $result);
    }
}
