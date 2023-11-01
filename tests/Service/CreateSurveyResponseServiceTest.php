<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Service;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionObject;
use Sst\SurveyLibBundle\Service\CreateSurveyResponseService;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyContainer;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElementUsage;
use Sst\SurveyLibBundle\Tests\TestEntities\DummySurvey;
use Sst\SurveyLibBundle\Tests\TestEntities\DummySurveyResponse;

class CreateSurveyResponseServiceTest extends TestCase
{
    protected CreateSurveyResponseService $service;

    protected function setUp(): void
    {
        $this->service = new CreateSurveyResponseService(
            $this->getEntityManager(),
            $this->createMock(EventDispatcherInterface::class),
        );
        parent::setUp();
    }

    public function testCreateSurveyResponseWithoutContainers(): void
    {
        $survey = new DummySurvey();
        $surveyResponse = $this->service->createSurveyResponse($survey);
        $this->assertEmpty($surveyResponse->getShuffledElementUsageSortOrders());
        $this->assertEquals((new DateTimeImmutable())->format('Y-m-d H:i:s'), $surveyResponse->getStartDateTime()->format('Y-m-d H:i:s'));
        $this->assertEquals($survey, $surveyResponse->getSurvey());
    }

    public function testCreateSurveyResponseWithoutShuffledContainers(): void
    {
        $survey = new DummySurvey();
        $container = new DummyContainer();
        $survey->addContainer($container);
        $subContainer = new DummyContainer();
        $container->addChildContainer($subContainer);
        $elementUsage = new DummyElementUsage();
        $subContainer->addElementUsage($elementUsage);
        $elementUsage2 = new DummyElementUsage();
        $subContainer->addElementUsage($elementUsage2);

        $surveyResponse = $this->service->createSurveyResponse($survey);
        $this->assertEmpty($surveyResponse->getShuffledElementUsageSortOrders());
    }

    public function testCreateSurveyResponseWithShuffledContainers(): void
    {
        $survey = new DummySurvey();
        $container = new DummyContainer();
        $survey->addContainer($container);
        $subContainer = new DummyContainer();
        $subContainer->setShuffleElementUsages(true);
        $container->addChildContainer($subContainer);
        $elementUsage = new DummyElementUsage();
        $this->setElementUsageId($elementUsage, 1);
        $subContainer->addElementUsage($elementUsage);
        $elementUsage2 = new DummyElementUsage();
        $this->setElementUsageId($elementUsage2, 2);
        $subContainer->addElementUsage($elementUsage2);

        $surveyResponse = $this->service->createSurveyResponse($survey);
        $this->assertCount(2, $surveyResponse->getShuffledElementUsageSortOrders());
        $this->assertArrayHasKey($elementUsage->getId(), $surveyResponse->getShuffledElementUsageSortOrders());
        $this->assertArrayHasKey($elementUsage2->getId(), $surveyResponse->getShuffledElementUsageSortOrders());
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        $dummyMetadata = new ClassMetadata(DummySurveyResponse::class);
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getClassMetadata')->willReturn($dummyMetadata);
        return $em;
    }

    protected function setElementUsageId(DummyElementUsage &$elementUsage, int $elementUsageId): void
    {
        $reflectedElementUsage = new ReflectionObject($elementUsage);
        $reflectedIdProperty = $reflectedElementUsage->getProperty('id');
        $reflectedIdProperty->setValue($elementUsage, $elementUsageId);
    }
}
