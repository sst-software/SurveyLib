<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Sst\SurveyLibBundle\Interfaces\Service\AstToJavascriptServiceInterface;
use Sst\SurveyLibBundle\Service\DisplayConditionService;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyAnswer;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyContainer;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElementUsage;
use Sst\SurveyLibBundle\Tests\TestEntities\DummySurveyResponse;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class DisplayConditionServiceTest extends TestCase
{
    protected DisplayConditionService $service;

    protected function setUp(): void
    {
        $astToJavascriptService = $this->createMock(AstToJavascriptServiceInterface::class);
        $astToJavascriptService->method('translateAstToJavascript')->willReturn('mockServiceOutput');
        $this->service = new DisplayConditionService(
            $astToJavascriptService
        );
        parent::setUp();
    }

    public function testEmptyDisplayConditionItemVisible(): void
    {
        $elementUsage = new DummyElementUsage();
        $surveyResponse = new DummySurveyResponse();
        $this->assertTrue($this->service->itemVisible($elementUsage, $surveyResponse));
    }

    public function testEmptyDisplayConditionPhpCondition(): void
    {
        $elementUsage = new DummyElementUsage();
        $this->assertEquals('true', $this->service->getPhpCondition($elementUsage));
    }

    public function testEmptyDisplayConditionJavascriptCondition(): void
    {
        $elementUsage = new DummyElementUsage();
        $this->assertEquals('true', $this->service->getJavascriptCondition($elementUsage));
    }

    public function testDisplayConditionPhpConditionStringOutput(): void
    {
        $elementUsage = new DummyElementUsage();
        $elementUsage->setDisplayCondition("((answers['test']['answer'] in {11:1,12:2,13:3,14:4,15:5}) and (answers['test']['answer'] === null)) ? true : false");
        $result = $this->service->getPhpCondition($elementUsage);
        $this->assertIsString($result);
    }

    public function testDisplayConditionPhpConditionException(): void
    {
        $this->expectException(SyntaxError::class);
        $elementUsage = new DummyElementUsage();
        $elementUsage->setDisplayCondition("((invalidValue['test']['answer'] in 1..3");
        $this->service->getPhpCondition($elementUsage);
    }

    public function testItemVisible(): void
    {
        $elementUsage = new DummyElementUsage();
        $elementUsage->setCode('test');
        $elementUsage->setDisplayCondition("answers['test']['answer'] in 1..5");

        $answer = new DummyAnswer();
        $answer->setAnswer(1);
        $elementUsage->addAnswer($answer);

        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->addAnswer($answer);

        $this->assertTrue($this->service->itemVisible($elementUsage, $surveyResponse));
    }

    public function testContainerVisible(): void
    {
        $container = new DummyContainer();
        $container->setDisplayCondition("answers['test']['answer'] in 1..5");

        $elementUsage = new DummyElementUsage();
        $elementUsage->setCode('test');
        $answer = new DummyAnswer();
        $answer->setAnswer(1);
        $elementUsage->addAnswer($answer);

        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->addAnswer($answer);

        $this->assertTrue($this->service->itemVisible($container, $surveyResponse));
    }

    public function testContainerNotVisible(): void
    {
        $container = new DummyContainer();
        $container->setDisplayCondition("answers['test']['answer'] in 1..5");

        $elementUsage = new DummyElementUsage();
        $elementUsage->setCode('test');
        $answer = new DummyAnswer();
        $answer->setAnswer(6);
        $elementUsage->addAnswer($answer);

        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->addAnswer($answer);

        $this->assertFalse($this->service->itemVisible($container, $surveyResponse));
    }

    public function testDisplayConditionJavascriptConditionStringOutput(): void
    {
        $elementUsage = new DummyElementUsage();
        $elementUsage->setDisplayCondition("((answers['test']['answer'] in {11:1,12:2,13:3,14:4,15:5}) and (answers['test']['answer'] === null)) ? true : false");
        $result = $this->service->getJavascriptCondition($elementUsage);
        $this->assertEquals('mockServiceOutput', $result);
    }
}
