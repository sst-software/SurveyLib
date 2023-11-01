<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sst\SurveyLibBundle\Entity\ElementData\DateTimeQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\MultipleChoiceQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\NumberQuestionElementData;
use Sst\SurveyLibBundle\Entity\ElementData\SubItems\MultipleChoiceQuestionAnswerOption;
use Sst\SurveyLibBundle\Entity\ElementData\TextQuestionElementData;
use Sst\SurveyLibBundle\Enums\ElementType;
use Sst\SurveyLibBundle\Exception\AnswerValidationException;
use Sst\SurveyLibBundle\Interfaces\Service\ValidateAnswerServiceInterface;
use Sst\SurveyLibBundle\Service\AddAnswerService;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyAnswer;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElement;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElementUsage;
use Sst\SurveyLibBundle\Tests\TestEntities\DummySurveyResponse;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class AddAnswerServiceTest extends TestCase
{
    protected AddAnswerService $service;

    protected function setUp(): void
    {
        $validateAnswerService = $this->createMock(ValidateAnswerServiceInterface::class);
        $validateAnswerService->method('validateAnswer')->willReturn(new ConstraintViolationList());
        $this->service = new AddAnswerService(
            $this->getEntityManager(),
            $this->createMock(EventDispatcherInterface::class),
            $validateAnswerService,
        );
        parent::setUp();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        $dummyMetadata = new ClassMetadata(DummyAnswer::class);
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getClassMetadata')->willReturn($dummyMetadata);
        return $em;
    }

    public function testUpdateExistingAnswer(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $answer = new DummyAnswer();
        $elementUsage = new DummyElementUsage();
        $answer->setElementUsage($elementUsage);
        $element = new DummyElement();
        $element->setType(ElementType::TEXT);
        $elementUsage->setElement($element);

        $elementData = new TextQuestionElementData();
        $element->setElementData($elementData);

        $surveyResponse->addAnswer($answer);

        $result = $this->service->addAnswers($surveyResponse, [['elementUsage' => $elementUsage, 'rawAnswer' => 'test', 'skipped' => false]]);
        $this->assertCount(1, $result);
        $this->assertEquals($answer, $result[0]['answer']);
        $this->assertEquals('test', $result[0]['answer']->getAnswer());

        $this->assertEquals($result[0]['elementUsage']->getAnswers()->first(), $result[0]['answer']);
        $this->assertEquals($surveyResponse->getAnswers()->first(), $result[0]['answer']);
    }

    public function testSkippedQuestion(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::TEXT);
        $elementUsage->setElement($element);

        $elementData = new TextQuestionElementData();
        $elementData->setRequired(false);
        $element->setElementData($elementData);

        $result = $this->service->addAnswers($surveyResponse, [['elementUsage' => $elementUsage, 'rawAnswer' => null, 'skipped' => true]]);
        $this->assertCount(1, $result);
        $this->assertTrue($result[0]['answer']->isSkipped());

        $this->assertEquals($result[0]['elementUsage']->getAnswers()->first(), $result[0]['answer']);
        $this->assertEquals($surveyResponse->getAnswers()->first(), $result[0]['answer']);
    }

    public function testInvalidAnswer(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $elementUsage = new DummyElementUsage();

        $element = new DummyElement();
        $element->setType(ElementType::MULTIPLE_CHOICE);
        $elementUsage->setElement($element);
        $elementUsage->setCode('testElementUsage');

        $elementData = new MultipleChoiceQuestionElementData();
        $elementData->addAnswerOption((new MultipleChoiceQuestionAnswerOption())->setValue(1));
        $element->setElementData($elementData);

        $validateAnswerService = $this->createMock(ValidateAnswerServiceInterface::class);
        $constraintViolationList = new ConstraintViolationList(
            [
                new ConstraintViolation('test', null, [], null, null, null),
                new ConstraintViolation('test2', null, [], null, null, null),
            ]
        );
        $validateAnswerService
            ->method('validateAnswer')
            ->willReturn($constraintViolationList)
        ;

        $service = new AddAnswerService(
            $this->getEntityManager(),
            $this->createMock(EventDispatcherInterface::class),
            $validateAnswerService,
        );

        $result = $service->addAnswers($surveyResponse, [['elementUsage' => $elementUsage, 'rawAnswer' => 2, 'skipped' => false]]);
        $this->assertCount(1, $result);
        $this->assertNull($result[0]['answer']);
        $this->assertInstanceOf(AnswerValidationException::class, $result[0]['validationException']);
        $this->assertEquals('2 is not a valid answer for elementUsage testElementUsage: test, test2', $result[0]['validationException']->getMessage());
        $this->assertEquals($constraintViolationList, $result[0]['validationException']->getConstraintViolationList());

        $this->assertEmpty($result[0]['elementUsage']->getAnswers());
        $this->assertEmpty($surveyResponse->getAnswers());
    }

    public function testInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $surveyResponse = new DummySurveyResponse();
        $this->service->addAnswers($surveyResponse, [['invalidValue' => true, 'invalidRawAnswer' => null, 'invalidSkipped' => false]]);
    }

    public function testNumberInputConvertedToInt(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $elementUsage = new DummyElementUsage();
        $element = new DummyElement();
        $element->setType(ElementType::NUMBER);
        $elementUsage->setElement($element);

        $elementData = new NumberQuestionElementData();
        $elementData->setNumberOfDecimals(0);
        $element->setElementData($elementData);

        $result = $this->service->addAnswers($surveyResponse, [['elementUsage' => $elementUsage, 'rawAnswer' => 7.052, 'skipped' => false]]);
        $this->assertCount(1, $result);
        $this->assertEquals(7, $result[0]['answer']->getAnswer());
        $this->assertIsInt($result[0]['answer']->getAnswer());
    }

    public function testNumberInputConvertedToFloat(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $elementUsage = new DummyElementUsage();
        $element = new DummyElement();
        $element->setType(ElementType::NUMBER);
        $elementUsage->setElement($element);

        $elementData = new NumberQuestionElementData();
        $elementData->setNumberOfDecimals(2);
        $element->setElementData($elementData);

        $result = $this->service->addAnswers($surveyResponse, [['elementUsage' => $elementUsage, 'rawAnswer' => 7.052, 'skipped' => false]]);
        $this->assertCount(1, $result);
        $this->assertEquals(7.05, $result[0]['answer']->getAnswer());
        $this->assertIsFloat($result[0]['answer']->getAnswer());
    }

    public function testDateTimeQuestionOnlyDate(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $elementUsage = new DummyElementUsage();
        $element = new DummyElement();
        $element->setType(ElementType::DATETIME);
        $elementUsage->setElement($element);

        $elementData = new DateTimeQuestionElementData();
        $elementData->setIncludeTime(false);
        $element->setElementData($elementData);

        $rawAnswer = new \DateTimeImmutable('2020-02-29 13:04:53');
        $result = $this->service->addAnswers(
            $surveyResponse,
            [['elementUsage' => $elementUsage, 'rawAnswer' => $rawAnswer, 'skipped' => false]]
        );
        $this->assertCount(1, $result);
        $this->assertSame('2020-02-29 00:00:00', $result[0]['answer']->getAnswer()->format('Y-m-d H:i:s'));
    }
}
