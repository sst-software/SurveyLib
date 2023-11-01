<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Service;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Sst\SurveyLibBundle\Interfaces\Service\DisplayConditionServiceInterface;
use Sst\SurveyLibBundle\Service\NextElementService;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyAnswer;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyContainer;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElementUsage;
use Sst\SurveyLibBundle\Tests\TestEntities\DummySurvey;
use Sst\SurveyLibBundle\Tests\TestEntities\DummySurveyResponse;

class NextElementServiceTest extends TestCase
{
    private NextElementService $service;

    private DisplayConditionServiceInterface $mockedDisplayConditionService;

    protected function setUp(): void
    {
        $this->mockedDisplayConditionService = $this->createMock(DisplayConditionServiceInterface::class);
        $this->mockedDisplayConditionService
            ->method('itemVisible')
            ->willReturn(true)
        ;
        $this->service = new NextElementService($this->mockedDisplayConditionService);
        parent::setUp();
    }

    public function testNoSurvey(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $this->assertNull($this->service->getNextElement($surveyResponse));
    }

    public function testNoContainers(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());
        $this->assertNull($this->service->getNextElement($surveyResponse));
    }

    /*
     *              Survey
     *             Container
     *          Q1           Q2
     *          ^ last        ^ expected
     */
    public function testElementHasEasyNextSibling(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());
        $container = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container);
        $elementUsage1 = new DummyElementUsage();
        $elementUsage1->setSortOrder(1);
        $elementUsage2 = new DummyElementUsage();
        $elementUsage2->setSortOrder(2);
        $container->addElementUsage($elementUsage1);
        $container->addElementUsage($elementUsage2);
        $result = $this->service->getNextElement($surveyResponse, $elementUsage1);
        $this->assertEquals($elementUsage2, $result);
    }

    /*
 *                    Survey
 *                   Container
 *          Q1          Q2           Q3
 *          ^ last      ^invisible    ^ expected
 */
    public function testShouldSkipMiddleSibling(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());
        $container = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container);
        $elementUsage1 = new DummyElementUsage();
        $elementUsage1->setSortOrder(1);
        $elementUsage2 = new DummyElementUsage();
        $elementUsage2->setSortOrder(2);
        $elementUsage3 = new DummyElementUsage();
        $elementUsage3->setSortOrder(3);
        $container->addElementUsage($elementUsage1);
        $container->addElementUsage($elementUsage2);
        $container->addElementUsage($elementUsage3);

        $mockedDisplayConditionService = $this->createMock(DisplayConditionServiceInterface::class);
        $mockedDisplayConditionService
            ->method('itemVisible')
            ->will($this->returnCallback(function ($elementUsage, $surveyResponse) use ($elementUsage2) {
                if ($elementUsage === $elementUsage2) {
                    return false;
                }
                return true;
            }));
        ;
        $service = new NextElementService($mockedDisplayConditionService);

        $result = $service->getNextElement($surveyResponse, $elementUsage1);
        $this->assertEquals($elementUsage3, $result);
    }

    /*
     *                                Survey
     *                               Container
     *     Q7      Q4      Q3      Q9     Q1    Q2    Q5    Q6    Q8   Q10
     *     ^ last   ^ expected
     */
    public function testElementHasEasyNextSiblingShuffledElements(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());
        $container = new DummyContainer();
        $container->setShuffleElementUsages(true);

        $elementUsages = [];
        for ($i = 0; $i < 10; $i++) {
            $elementUsage = new DummyElementUsage();
            $this->setElementUsageId($elementUsage, $i);
            $elementUsage->setSortOrder($i);
            $container->addElementUsage($elementUsage);
            $elementUsages[] = $elementUsage;
        }
        shuffle($elementUsages);
        $shuffleOrder = [];
        foreach ($elementUsages as $key => $elementUsage) {
            $elementUsage->setSortOrder($key);
            $shuffleOrder[$elementUsage->getId()] = $key;
        }
        $surveyResponse->setShuffledElementUsageSortOrders($shuffleOrder);
        $surveyResponse->getSurvey()->addContainer($container);

        $firstElementUsage = array_shift($elementUsages);

        $result = $this->service->getNextElement($surveyResponse, $firstElementUsage);
        $secondElementUsage = array_shift($elementUsages);
        $this->assertEquals($secondElementUsage, $result);
    }

    /*
     *              Survey
     *     Container1     Container2
     *     Q1      Q2      Q3      Q4
     *             ^ last   ^ expected
     */
    public function testFirstElementOfNextSiblingContainerReturned(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container1);
        $elementUsage1 = new DummyElementUsage();
        $elementUsage1->setSortOrder(1);
        $elementUsage2 = new DummyElementUsage();
        $elementUsage2->setSortOrder(2);
        $container1->addElementUsage($elementUsage1);
        $container1->addElementUsage($elementUsage2);

        $container2 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container2);
        $elementUsage3 = new DummyElementUsage();
        $elementUsage3->setSortOrder(1);
        $elementUsage4 = new DummyElementUsage();
        $elementUsage4->setSortOrder(2);
        $container2->addElementUsage($elementUsage3);
        $container2->addElementUsage($elementUsage4);

        $result = $this->service->getNextElement($surveyResponse, $elementUsage2);
        $this->assertEquals($elementUsage3, $result);
    }

    /*
     *              Survey
     *     Container1     Container2
     *      Q1      Q2      Container3
     *                        Q3      Q4
     *              ^ last    ^ expected
     */
    public function testFirstElementOfChildOfNextSiblingContainerReturned(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container1);
        $elementUsage1 = new DummyElementUsage();
        $elementUsage1->setSortOrder(1);
        $elementUsage2 = new DummyElementUsage();
        $elementUsage2->setSortOrder(2);
        $container1->addElementUsage($elementUsage1);
        $container1->addElementUsage($elementUsage2);

        $container2 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container2);

        $container3 = new DummyContainer();
        $container2->addChildContainer($container3);
        $elementUsage3 = new DummyElementUsage();
        $elementUsage3->setSortOrder(1);
        $elementUsage4 = new DummyElementUsage();
        $elementUsage4->setSortOrder(2);
        $container3->addElementUsage($elementUsage3);
        $container3->addElementUsage($elementUsage4);

        $result = $this->service->getNextElement($surveyResponse, $elementUsage2);
        $this->assertEquals($elementUsage3, $result);
    }

    /*
     *              Survey
     *     Container1     Container2            Container4
     *                     ^ invisible
     *      Q1      Q2      Container3            Container5
     *                        Q3      Q4           Q5      Q6
     *              ^ last                         ^ expected
     */
    public function testFirstElementOfChildOfNextSiblingContainerReturnedMiddleInvisible(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = (new DummyContainer())->setTitle('container1');
        $surveyResponse->getSurvey()->addContainer($container1);
        $elementUsage1 = new DummyElementUsage();
        $elementUsage1->setSortOrder(1);
        $elementUsage2 = new DummyElementUsage();
        $elementUsage2->setSortOrder(2);
        $container1->addElementUsage($elementUsage1);
        $container1->addElementUsage($elementUsage2);

        $container2 = (new DummyContainer())->setTitle('container2');
        $surveyResponse->getSurvey()->addContainer($container2);

        $container3 = (new DummyContainer())->setTitle('container3');
        $container2->addChildContainer($container3);
        $elementUsage3 = new DummyElementUsage();
        $elementUsage3->setSortOrder(1);
        $elementUsage4 = new DummyElementUsage();
        $elementUsage4->setSortOrder(2);
        $container3->addElementUsage($elementUsage3);
        $container3->addElementUsage($elementUsage4);

        $container4 = (new DummyContainer())->setTitle('container4');
        $surveyResponse->getSurvey()->addContainer($container4);
        $container5 = (new DummyContainer())->setTitle('container5');
        $container4->addChildContainer($container5);
        $elementUsage5 = new DummyElementUsage();
        $elementUsage5->setSortOrder(5);
        $container5->addElementUsage($elementUsage5);
        $elementUsage6 = new DummyElementUsage();
        $elementUsage6->setSortOrder(6);
        $container5->addElementUsage($elementUsage6);

        $mockedDisplayConditionService = $this->createMock(DisplayConditionServiceInterface::class);
        $mockedDisplayConditionService
            ->method('itemVisible')
            ->will($this->returnCallback(function ($elementUsage, $surveyResponse) use ($container2) {
                if ($elementUsage === $container2) {
                    return false;
                }
                return true;
            }));
        ;
        $service = new NextElementService($mockedDisplayConditionService);

        $result = $service->getNextElement($surveyResponse, $elementUsage2);
        $this->assertEquals($elementUsage5, $result);
    }

    /*
     *              Survey
     *     Container1     Container2            Container4
     *      Q1      Q2      Container3            Container5
     *                        ^ invisible
     *                        Q3      Q4           Q5      Q6
     *              ^ last                          ^ expected
     */
    public function testFirstElementOfChildOfNextSiblingContainerReturnedMiddleSubItemInvisible(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = (new DummyContainer())->setTitle('container1');
        $surveyResponse->getSurvey()->addContainer($container1);
        $elementUsage1 = new DummyElementUsage();
        $elementUsage1->setSortOrder(1);
        $elementUsage2 = new DummyElementUsage();
        $elementUsage2->setSortOrder(2);
        $container1->addElementUsage($elementUsage1);
        $container1->addElementUsage($elementUsage2);

        $container2 = (new DummyContainer())->setTitle('container2');
        $surveyResponse->getSurvey()->addContainer($container2);

        $container3 = (new DummyContainer())->setTitle('container3');
        $container2->addChildContainer($container3);
        $elementUsage3 = new DummyElementUsage();
        $elementUsage3->setSortOrder(1);
        $elementUsage4 = new DummyElementUsage();
        $elementUsage4->setSortOrder(2);
        $container3->addElementUsage($elementUsage3);
        $container3->addElementUsage($elementUsage4);

        $container4 = (new DummyContainer())->setTitle('container4');
        $surveyResponse->getSurvey()->addContainer($container4);
        $container5 = (new DummyContainer())->setTitle('container5');
        $container4->addChildContainer($container5);
        $elementUsage5 = new DummyElementUsage();
        $elementUsage5->setSortOrder(5);
        $container5->addElementUsage($elementUsage5);
        $elementUsage6 = new DummyElementUsage();
        $elementUsage6->setSortOrder(6);
        $container5->addElementUsage($elementUsage6);

        $mockedDisplayConditionService = $this->createMock(DisplayConditionServiceInterface::class);
        $mockedDisplayConditionService
            ->method('itemVisible')
            ->will($this->returnCallback(function ($elementUsage, $surveyResponse) use ($container3) {
                if ($elementUsage === $container3) {
                    return false;
                }
                return true;
            }));
        ;
        $service = new NextElementService($mockedDisplayConditionService);

        $result = $service->getNextElement($surveyResponse, $elementUsage2);
        $this->assertEquals($elementUsage5, $result);
    }

    /*
     *              Survey
     *     Container1     Container3
     *       Container2     Container4
     *           Q1              Q2
     *            ^ last          ^ expected
     */
    public function testFirstElementOfChildOfNextSiblingOfParentContainerReturned(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container1);
        $container2 = new DummyContainer();
        $container1->addChildContainer($container2);
        $elementUsage1 = new DummyElementUsage();
        $container2->addElementUsage($elementUsage1);

        $container3 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container3);
        $container4 = new DummyContainer();
        $container3->addChildContainer($container4);
        $elementUsage2 = new DummyElementUsage();
        $container4->addElementUsage($elementUsage2);

        $result = $this->service->getNextElement($surveyResponse, $elementUsage1);
        $this->assertEquals($elementUsage2, $result);
    }

    /*
     *              Survey
     *             Container1
     *           Q1    Q2    Q3
     *                        ^ last
     */
    public function testNoNextElementBecauseCurrentElementIsLast(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container1);

        $elementUsage1 = new DummyElementUsage();
        $container1->addElementUsage($elementUsage1);

        $elementUsage2 = new DummyElementUsage();
        $container1->addElementUsage($elementUsage2);

        $elementUsage3 = new DummyElementUsage();
        $container1->addElementUsage($elementUsage3);

        $result = $this->service->getNextElement($surveyResponse, $elementUsage3);
        $this->assertNull($result);
    }

    /*
     *              Survey
     *     Container1     Container2
     *      ^ last
     *         Q1           Container3
     *                        Container4
     *                          Q2
     *                           ^ expected
     */
    public function testGetSubSubSUbElementBasedOnContainer(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container1);

        $container2 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container2);

        $container3 = new DummyContainer();
        $container2->addChildContainer($container3);

        $container4 = new DummyContainer();
        $container3->addChildContainer($container4);

        $elementUsage1 = new DummyElementUsage();
        $container1->addElementUsage($elementUsage1);

        $elementUsage2 = new DummyElementUsage();
        $container4->addElementUsage($elementUsage2);


        $result = $this->service->getNextElement($surveyResponse, $container1);
        $this->assertEquals($elementUsage2, $result);
    }

    /*
     *             Survey
     *  Container1       Container2
     *                  ElementUsage2_1
     *                    ^ expected
     */
    public function testSkippingEmptyContainersAndReturningElementUsage(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container1);

        $container2 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container2);

        $elementUsage2_1 = new DummyElementUsage();
        $container2->addElementUsage($elementUsage2_1);

        $result = $this->service->getNextElement($surveyResponse, null);
        $this->assertEquals($elementUsage2_1, $result);
    }

    /*
     *                         Survey
     *  Container1           Container2        Container3
     *    Q1                   Container2_1      Q3
     *                           ^ last             ^ expected
     *                            Q2
     */
    public function testLastContainerIsOnLowerLevel(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container1);

        $container2 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container2);

        $container2_1 = new DummyContainer();
        $container2->addChildContainer($container2_1);

        $container3 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container3);

        $elementUsage1 = new DummyElementUsage();
        $container1->addElementUsage($elementUsage1);

        $elementUsage2 = new DummyElementUsage();
        $container2_1->addElementUsage($elementUsage2);

        $elementUsage3 = new DummyElementUsage();
        $container3->addElementUsage($elementUsage3);

        $result = $this->service->getNextElement($surveyResponse, $container2_1);
        $this->assertEquals($elementUsage3, $result);
    }

    /*
     *             Survey
     *  Container1       Container2
     *  Q1_1  Q1_2      Container2_1
     *                     Q2_1_1
     *    ^ expected
     */
    public function testGetFirstElementUsageWithUnbalancedContainerDepth(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container1);

        $elementUsage1_1 = new DummyElementUsage();
        $container1->addElementUsage($elementUsage1_1);
        $elementUsage1_1->setCode('$elementUsage1_1');

        $elementUsage1_2 = new DummyElementUsage();
        $container1->addElementUsage($elementUsage1_2);
        $elementUsage1_2->setCode('$elementUsage1_2');

        $container2 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container2);


        $container2_1 = new DummyContainer();
        $container2->addChildContainer($container2_1);

        $elementUsage2_1_1 = new DummyElementUsage();
        $container2_1->addElementUsage($elementUsage2_1_1);
        $elementUsage2_1_1->setCode('$elementUsage2_1_1');

        $result = $this->service->getNextElement($surveyResponse, null);
        $this->assertEquals($elementUsage1_1, $result);
    }

    /*
     *                          Survey
     *          Container1                Container2
     * Container1_1    Container 1_2        Q2_1
     *                     Q1_2_1
     *                      ^ last           ^ expected
     */
    public function testGetNextElementUsageWithUnbalancedContainerDepth(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $surveyResponse->setSurvey(new DummySurvey());

        $container1 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container1);

        $container1_1 = new DummyContainer();
        $container1->addChildContainer($container1_1);

        $container1_2 = new DummyContainer();
        $container1->addChildContainer($container1_2);

        $elementUsage1_2_1 = new DummyElementUsage();
        $container1_2->addElementUsage($elementUsage1_2_1);
        $elementUsage1_2_1->setCode('$elementUsage1_2_1');

        $container2 = new DummyContainer();
        $surveyResponse->getSurvey()->addContainer($container2);

        $elementUsage2_1 = new DummyElementUsage();
        $container2->addElementUsage($elementUsage2_1);
        $elementUsage2_1->setCode('$elementUsage2_1');

        $result = $this->service->getNextElement($surveyResponse, $elementUsage1_2_1);
        $this->assertEquals($elementUsage2_1, $result);
    }

    /*
     *              Survey
     *             Container
     *       Q1        Q2        Q3
     *        A1        A2        A3
     *                  ^ latest  ^ expected
     */
    public function testNoLastElementGiven(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $survey = new DummySurvey();
        $surveyResponse->setSurvey($survey);

        $container = new DummyContainer();
        $survey->addContainer($container);

        $answer1 = new DummyAnswer();
        $answer1->setUpdatedAt(new DateTimeImmutable('2019-01-01'));
        $surveyResponse->addAnswer($answer1);
        $elementUsage1 = new DummyElementUsage();
        $answer1->setElementUsage($elementUsage1);
        $container->addElementUsage($elementUsage1);

        $answer2 = new DummyAnswer();
        $answer2->setUpdatedAt(new DateTimeImmutable('2023-01-01'));
        $surveyResponse->addAnswer($answer2);
        $elementUsage2 = new DummyElementUsage();
        $answer2->setElementUsage($elementUsage2);
        $container->addElementUsage($elementUsage2);

        $answer3 = new DummyAnswer();
        $answer3->setUpdatedAt(new DateTimeImmutable('2021-01-01'));
        $surveyResponse->addAnswer($answer3);
        $elementUsage3 = new DummyElementUsage();
        $answer3->setElementUsage($elementUsage3);
        $container->addElementUsage($elementUsage3);

        $result = $this->service->getNextElement($surveyResponse);
        $this->assertEquals($elementUsage3, $result);
    }

    /*
     *              Survey
     *             Container
     *       Q1        Q2        Q3
     *        ^ expected
     */
    public function testNoAnswersGivenFirstElementGiven(): void
    {
        $surveyResponse = new DummySurveyResponse();
        $survey = new DummySurvey();
        $surveyResponse->setSurvey($survey);

        $container = new DummyContainer();
        $survey->addContainer($container);


        $elementUsage1 = new DummyElementUsage();
        $container->addElementUsage($elementUsage1);

        $elementUsage2 = new DummyElementUsage();
        $container->addElementUsage($elementUsage2);

        $elementUsage3 = new DummyElementUsage();
        $container->addElementUsage($elementUsage3);

        $result = $this->service->getNextElement($surveyResponse);
        $this->assertEquals($elementUsage1, $result);
    }

    /*
     *             Survey
     *  Container1          Container2           Container3           Container4
     *  ElementUsage1       ElementUsage2        ElementUsage3        ElementUsage4
     *   ^ answered 2020      ^ answered 2021        ^ answered 2020       ^ not answered
     *                        ^ skipped by display condition
     *                                               ^ expected
     */
    public function testSkippedElementAnsweredAnywayAsLastItem(): void
    {
        $elementUsage1 = (new DummyElementUsage())
            ->setCode('testvraag')
        ;

        $elementUsage2 = (new DummyElementUsage())
            ->setCode('testvraag2')
            ->setDisplayCondition("(('testvraag' in availableAnswerKeys) ? (answers['testvraag']['answer'] == 'test-value') : true)")
        ;

        $elementUsage3 = (new DummyElementUsage())
            ->setCode('testvraag3')
        ;

        $elementUsage4 = (new DummyElementUsage())
            ->setCode('testvraag4')
        ;

        $answer1 = (new DummyAnswer())
            ->setElementUsage($elementUsage1)
            ->setAnswer('answer1')
            ->setUpdatedAt(new \DateTimeImmutable('2020-01-01 00:00:00'))
        ;

        $answer2 = (new DummyAnswer())
            ->setElementUsage($elementUsage2)
            ->setAnswer('answer2')
            ->setUpdatedAt(new \DateTimeImmutable('2021-01-01 00:00:00'))
        ;

        $answer3 = (new DummyAnswer())
            ->setElementUsage($elementUsage3)
            ->setAnswer('answer3')
            ->setUpdatedAt(new \DateTimeImmutable('2020-01-01 00:00:00'))
        ;
        $container1 = (new DummyContainer())->setTitle('container1')->setSortOrder(1)->addElementUsage($elementUsage1);
        $container2 = (new DummyContainer())->setTitle('container2')->setSortOrder(2)->addElementUsage($elementUsage2);
        $container3 = (new DummyContainer())->setTitle('container3')->setSortOrder(3)->addElementUsage($elementUsage3);
        $container4 = (new DummyContainer())->setTitle('container4')->setSortOrder(4)->addElementUsage($elementUsage4);

        $survey = (new DummySurvey())
            ->addContainer($container1)
            ->addContainer($container2)
            ->addContainer($container3)
            ->addContainer($container4)
        ;

        $surveyResponse = (new DummySurveyResponse())
            ->setSurvey($survey)
            ->addAnswer($answer1)
            ->addAnswer($answer2)
            ->addAnswer($answer3)
        ;

        $mockedDisplayConditionService = $this->createMock(DisplayConditionServiceInterface::class);
        $mockedDisplayConditionService
            ->method('itemVisible')
            ->will($this->returnCallback(function ($elementUsage, $surveyResponse) use ($elementUsage2) {
                if ($elementUsage === $elementUsage2) {
                    return false;
                }
                return true;
            }));
        ;
        $service = new NextElementService($mockedDisplayConditionService);

        $result = $service->getNextElement($surveyResponse);
        $this->assertEquals($elementUsage3, $result);
    }

    protected function setElementUsageId(DummyElementUsage &$elementUsage, int $elementUsageId): void
    {
        $reflectedElementUsage = new ReflectionObject($elementUsage);
        $reflectedIdProperty = $reflectedElementUsage->getProperty('id');
        $reflectedIdProperty->setValue($elementUsage, $elementUsageId);
    }
}
