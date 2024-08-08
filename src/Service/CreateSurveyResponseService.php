<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sst\SurveyLibBundle\Event\SurveyResponseCreate;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;
use Sst\SurveyLibBundle\Interfaces\Service\CreateSurveyResponseServiceInterface;

class CreateSurveyResponseService implements CreateSurveyResponseServiceInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createSurveyResponse(SurveyInterface $survey): SurveyResponseInterface
    {
        /** @var SurveyResponseInterface $surveyResponse */
        $surveyResponse = $this->getNewEntity(SurveyResponseInterface::class);
        $this->eventDispatcher->dispatch(new SurveyResponseCreate($surveyResponse), SurveyResponseCreate::PRE_CREATE);
        $surveyResponse->setSurvey($survey);
        $surveyResponse->setStartDateTime(new DateTimeImmutable());
        $surveyResponse->setShuffledElementUsageSortOrders($this->getShuffledElementUsageSortOrders($survey));
        $this->eventDispatcher->dispatch(new SurveyResponseCreate($surveyResponse), SurveyResponseCreate::POST_CREATE);
        return $surveyResponse;
    }

    /**
     * @return array<int, int> key: elementUsageId, value: sortOrder
     */
    protected function getShuffledElementUsageSortOrders(SurveyInterface $survey): array
    {
        $containers = $survey->getContainers(true);
        $shuffledElementUsageSortOrders = [];
        foreach ($containers as $container) {
            if (!$container->isShuffleElementUsages()) {
                continue;
            }
            foreach ($container->getElementUsages() as $elementUsage) {
                $shuffledElementUsageSortOrders[$elementUsage->getId()] = $elementUsage->getSortOrder();
            }
        }
        return $shuffledElementUsageSortOrders;
    }

    protected function getNewEntity(string $interface): object
    {
        $className = $this->entityManager->getClassMetadata($interface)->getName();
        return new $className();
    }
}
