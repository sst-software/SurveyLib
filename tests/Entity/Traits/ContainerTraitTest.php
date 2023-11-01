<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Tests\Entity\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyContainer;
use Sst\SurveyLibBundle\Tests\TestEntities\DummyElementUsage;
use Sst\SurveyLibBundle\Tests\TestEntities\DummySurvey;

class ContainerTraitTest extends TestCase
{
    public function testGetElementUsages(): void
    {
        $container = new DummyContainer();
        $elementUsageMock1 = (new DummyElementUsage())
            ->setSortOrder(1)
        ;
        $elementUsageMock2 = (new DummyElementUsage())
            ->setSortOrder(2)
        ;
        $elementUsageMock3 = (new DummyElementUsage())
            ->setSortOrder(3)
        ;

        $container->addElementUsage($elementUsageMock1);
        $container->addElementUsage($elementUsageMock2);
        $container->addElementUsage($elementUsageMock3);

        $usages = $container->getElementUsages();
        $usagesArray = $usages->toArray();
        $this->assertCount(3, $usages);
        $this->assertEquals([$elementUsageMock1, $elementUsageMock2, $elementUsageMock3], $usagesArray);
    }

    public function testGetElementUsagesShuffled(): void
    {
        $container = new DummyContainer();
        $elementUsageMock1 = (new DummyElementUsage())
            ->setSortOrder(1)
        ;
        $elementUsageMock2 = (new DummyElementUsage())
            ->setSortOrder(2)
        ;
        $elementUsageMock3 = (new DummyElementUsage())
            ->setSortOrder(3)
        ;

        $container->addElementUsage($elementUsageMock1);
        $container->addElementUsage($elementUsageMock2);
        $container->addElementUsage($elementUsageMock3);

        $container->setShuffleElementUsages(true);

        $usages = $container->getElementUsages();
        $this->assertCount(3, $usages);

        //We can't test if the array is shuffled, but we can test if it has the same items as the original array
        $usagesArray = $usages->toArray();
        $strippedUsagesArray = $usagesArray;
        foreach ($usagesArray as $key => $usage) {
            unset($strippedUsagesArray[$key]);
        }
        $this->assertEmpty($strippedUsagesArray);
    }

    public function testGetContainersRecursive(): void
    {
        $container = new DummyContainer();
        $childContainer = new DummyContainer();
        $childChildContainer = new DummyContainer();
        $childContainer->addChildContainer($childChildContainer);
        $container->addChildContainer($childContainer);

        $result = $container->getChildContainers(true);
        $this->assertEquals([$childContainer, $childChildContainer], $result->toArray());
    }

    public function testGetContainersNonRecursive(): void
    {
        $container = new DummyContainer();
        $childContainer = new DummyContainer();
        $childChildContainer = new DummyContainer();
        $childContainer->addChildContainer($childChildContainer);
        $container->addChildContainer($childContainer);

        $result = $container->getChildContainers(false);
        $this->assertEquals([$childContainer], $result->toArray());
    }

    public function testGetSurveyRecursive(): void
    {
        $container = new DummyContainer();
        $childContainer = new DummyContainer();
        $childChildContainer = new DummyContainer();
        $childContainer->addChildContainer($childChildContainer);
        $container->addChildContainer($childContainer);
        $survey = new DummySurvey();
        $container->setSurvey($survey);

        $result = $childChildContainer->getSurvey(true);
        $this->assertEquals($survey, $result);
    }

    public function testGetSurveyNonRecursive(): void
    {
        $container = new DummyContainer();
        $childContainer = new DummyContainer();
        $childChildContainer = new DummyContainer();
        $childContainer->addChildContainer($childChildContainer);
        $container->addChildContainer($childContainer);
        $survey = new DummySurvey();
        $container->setSurvey($survey);

        $result = $childChildContainer->getSurvey(false);
        $this->assertNull($result);
    }

    public function testNoChildContainersWithElementUsages(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $container = new DummyContainer();
        $childContainer = new DummyContainer();
        $container->addChildContainer($childContainer);

        $elementUsage = new DummyElementUsage();
        $container->addElementUsage($elementUsage);
    }

    public function testNoElementUsagesWithChildContainers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $container = new DummyContainer();
        $elementUsage = new DummyElementUsage();
        $container->addElementUsage($elementUsage);

        $childContainer = new DummyContainer();
        $container->addChildContainer($childContainer);
    }
}
