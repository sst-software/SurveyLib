<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ContainerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;
use Sst\SurveyLibBundle\Interfaces\Service\DisplayConditionServiceInterface;
use Sst\SurveyLibBundle\Interfaces\Service\NextElementServiceInterface;

class NextElementService implements NextElementServiceInterface
{
    public function __construct(
        protected readonly DisplayConditionServiceInterface $displayConditionService,
    ) {
    }

    /**
     * Get the next element to present, based on a given survey response and the last presented item.
     * the last presented item can be an element-usage or a container, or can be null, if nothing is presented yet, of if it's unknown.
     * If no last presented item is provided, the last saved answer of the given survey response is considered as last presented.
     * If no last saved answer is found, the first element-usage of the first container of the survey will be returned.
     */
    public function getNextElement(
        SurveyResponseInterface $surveyResponse,
        ElementUsageInterface|ContainerInterface|null $lastPresentedItem = null,
        bool $reverse = false,
    ): ?ElementUsageInterface {
        $survey = $surveyResponse->getSurvey();
        if ($survey === null) {
            return null;
        }
        $surveyContainers = $survey->getContainers();
        $mainContainers = $this->filterVisibleItems($surveyContainers, $surveyResponse, $reverse);
        if ($lastPresentedItem instanceof ContainerInterface) {
            $mainParentContainer = $lastPresentedItem->getParentContainer(true);
            $mainContainers = $this->addContainerIfNotAlreadyInList($mainParentContainer, $mainContainers, $surveyContainers, $reverse);
        }
        if ($lastPresentedItem instanceof ElementUsageInterface) {
            $mainParentContainer = $lastPresentedItem->getContainer()->getParentContainer(true);
            $mainContainers = $this->addContainerIfNotAlreadyInList($mainParentContainer, $mainContainers, $surveyContainers, $reverse);
        }
        if ($mainContainers->isEmpty()) {
            return null;
        }

        if ($lastPresentedItem instanceof ElementUsageInterface) {
            return $this->getNextElementUsageInContainer(
                $lastPresentedItem->getContainer(),
                $lastPresentedItem,
                $surveyResponse,
                $reverse,
            );
        }

        if ($lastPresentedItem instanceof ContainerInterface) {
            $nextContainer = $this->getNextContainer($lastPresentedItem, $surveyResponse, false, $reverse);
            if ($nextContainer !== null) {
                return $this->getNextElementUsageInContainer(
                    $nextContainer,
                    null,
                    $surveyResponse,
                    $reverse,
                );
            }
        }

        if ($lastPresentedItem === null) {
            $elementUsageOfLastSavedAnswer = $this->getElementUsageOfLastSavedAnswer($surveyResponse);
            if ($elementUsageOfLastSavedAnswer) {
                return $this->getNextElementUsageInContainer(
                    $elementUsageOfLastSavedAnswer->getContainer(),
                    $elementUsageOfLastSavedAnswer,
                    $surveyResponse,
                    $reverse,
                );
            }

            $firstContainer = $this->getFirstChildContainer($survey, $surveyResponse, $reverse);
            if ($firstContainer !== null) {
                return $this->getNextElementUsageInContainer(
                    $firstContainer,
                    null,
                    $surveyResponse,
                    $reverse,
                );
            }
        }

        return null;
    }

    /**
     * The last saved answer of a surveyresponse can be seen as the last presented item.
     * This function retrieves all the answers of a surveyresponse, sorts them by their updated_at date and returns the latest one.
     */
    protected function getElementUsageOfLastSavedAnswer(SurveyResponseInterface $surveyResponse): ?ElementUsageInterface
    {
        $answers = $surveyResponse->getAnswers();
        if ($answers->isEmpty()) {
            return null;
        }
        $answerArray = $answers->toArray();
        usort($answerArray, fn(AnswerInterface $a, AnswerInterface $b) => $a->getUpdatedAt() <=> $b->getUpdatedAt());
        $lastAnswer = end($answerArray);
        if (!$lastAnswer) {
            return null;
        }
        return $lastAnswer->getElementUsage();
    }

    /**
     * Returns the next element usage of a container.
     * Works recursively, so if the given elementusage has no next elementusage in the same container, it will look for the next elementusage in a sibling-container.
     * If the container has no elementusages, it will check it's child-container for the next element usage (also recursively).
     */
    protected function getNextElementUsageInContainer(
        ContainerInterface $container,
        ElementUsageInterface|null $elementUsage,
        SurveyResponseInterface $surveyResponse,
        bool $reverse,
    ): ElementUsageInterface|ContainerInterface|null {
        $elementUsages = $this->getSortedElementUsagesFromContainer($container, $surveyResponse, $elementUsage, $reverse);

        if (!$elementUsages->isEmpty()) {
            if ($elementUsage === null) {
                return $elementUsages->first();
            }
            $key = $elementUsages->indexOf($elementUsage);
            if ($key !== false) {
                $elementUsages->first();
                while ($elementUsages->current() !== $elementUsage) {
                    $elementUsages->next();
                }
                $nextSibling = $elementUsages->next();
                $nextSiblingPresent = $nextSibling !== false;
                if ($nextSiblingPresent) {
                    return $nextSibling;
                }
                $nextContainer = $this->getNextContainer($container, $surveyResponse, false, $reverse);
                if ($nextContainer === null) {
                    return null;
                }
                return $this->getNextElementUsageInContainer($nextContainer, null, $surveyResponse, $reverse);
            }
        }
        if ($container->getChildContainers()->isEmpty()) {
            return null;
        }

        $firstChild = $this->getFirstChildContainer($container, $surveyResponse, $reverse);
        return $this->getNextElementUsageInContainer($firstChild, $elementUsage, $surveyResponse, $reverse);
    }

    /**
     * Returns the first parent (so not recursively) and all sibling containers of a container.
     * Note that the parent can also be a survey
     * If a survey is given as parameter, the returned parent will be itself and the returned sibling containers will contain its child-containers (non-recursively)
     * Returned items are filtered by visibility, determined by the displayConditionService
     */
    protected function getParentAndSiblingContainers(
        ContainerInterface|SurveyInterface $containerOrSurvey,
        SurveyResponseInterface $surveyResponse,
        bool $reverse,
    ): array {
        if ($containerOrSurvey instanceof ContainerInterface) {
            $parentContainer = $containerOrSurvey->getParentContainer();
            if ($parentContainer !== null) {
                $childContainers = $parentContainer->getChildContainers();
                $filteredSiblingContainers = $this->filterVisibleItems($childContainers, $surveyResponse, $reverse);
                return ['parent' => $parentContainer, 'siblingContainers' => $this->addContainerIfNotAlreadyInList($containerOrSurvey, $filteredSiblingContainers, $childContainers, $reverse)];
            } else {
                $survey = $containerOrSurvey->getSurvey();
                if ($survey !== null) {
                    $surveyContainers = $survey->getContainers();
                    $filteredSurveyContainers = $this->filterVisibleItems($surveyContainers, $surveyResponse, $reverse);
                    return ['parent' => $survey, 'siblingContainers' => $this->addContainerIfNotAlreadyInList($containerOrSurvey, $filteredSurveyContainers, $surveyContainers, $reverse)];
                }
            }
        }

        return ['parent' => $containerOrSurvey, 'siblingContainers' => $this->filterVisibleItems($containerOrSurvey->getContainers(), $surveyResponse, $reverse)];
    }

    protected function addContainerIfNotAlreadyInList(ContainerInterface $container, Collection $filteredContainers, Collection $originalContainers, bool $reverse): Collection
    {
        if (
            $originalContainers->contains($container) &&
            !$filteredContainers->contains($container)
        ) {
            $filteredContainers->add($container);
            $iterator = $filteredContainers->getIterator();
            $iterator->uasort(function (ContainerInterface $a, ContainerInterface $b) use ($reverse) {
                if ($reverse) {
                    return $b->getSortOrder() <=> $a->getSortOrder();
                }
                return $a->getSortOrder() <=> $b->getSortOrder();
            });
            $filteredContainers = new ArrayCollection(iterator_to_array($iterator));
        }
        return $filteredContainers;
    }

    /**
     * Recursive function to get the next container, based on the given container (or survey)
     * If the given container is the last sibling, it will return the first ("next"-)container of the parent (recursively)
     * The first call to this function should always have a container as parameter,
     * however, a survey is also allowed, because the recursion of this function might need that
     */
    protected function getNextContainer(
        ContainerInterface|SurveyInterface $containerOrSurvey,
        SurveyResponseInterface $surveyResponse,
        bool $getFirstChild,
        bool $reverse,
    ): ?ContainerInterface {
        $parentAndSiblings = $this->getParentAndSiblingContainers($containerOrSurvey, $surveyResponse, $reverse);
        /** @var ContainerInterface|SurveyInterface $parent */
        $parent = $parentAndSiblings['parent'];
        /** @var Collection<int, ContainerInterface> $siblingContainers */
        $siblingContainers = $parentAndSiblings['siblingContainers'];

        if ($reverse) {
            $iterator = $siblingContainers->getIterator();
            $iterator->uasort(function (ContainerInterface $a, ContainerInterface $b) {
                return $b->getSortOrder() <=> $a->getSortOrder();
            });
            $siblingContainers = new ArrayCollection(iterator_to_array($iterator));
        }

        $key = $siblingContainers->indexOf($containerOrSurvey);
        if ($key !== false) {
            $siblingContainers->first();
            while ($siblingContainers->current() !== $containerOrSurvey) {
                $siblingContainers->next();
            }
            $nextSibling = $siblingContainers->next();
            $containerIsLastSibling = $nextSibling === false;
            if (!$containerIsLastSibling) {
                if ($getFirstChild) {
                    return $this->getFirstChildContainer($nextSibling, $surveyResponse, $reverse);
                }
                return $nextSibling;
            }
            return $this->getNextContainer($parent, $surveyResponse, true, $reverse);
        }
        return null;
    }

    /**
     * Returns a filtered subset of the given collection, based on the visibility of the items in the collection. (determined by the displayConditionService).
     * If an item has no visible child containers or element usages, it will not be included in the result. (checked recursively)
     * @param Collection<int, ContainerInterface|ElementUsageInterface> $containersOrElementUsages
     * @return Collection<int, ContainerInterface|ElementUsageInterface>
     */
    protected function filterVisibleItems(Collection $containersOrElementUsages, SurveyResponseInterface $surveyResponse, bool $reverse): Collection
    {
        $result = new ArrayCollection();
        foreach ($containersOrElementUsages as $key => $containerOrElement) {
            if ($this->displayConditionService->itemVisible($containerOrElement, $surveyResponse)) {
                if ($containerOrElement instanceof ElementUsageInterface) {
                    $result->set($key, $containerOrElement);
                    continue;
                }
                $hasVisibleChildContainers = !$this->filterVisibleItems($containerOrElement->getChildContainers(), $surveyResponse, $reverse)->isEmpty();
                $hasVisibleElementUsages = !$this->filterVisibleItems($containerOrElement->getElementUsages(), $surveyResponse, $reverse)->isEmpty();
                if ($hasVisibleChildContainers || $hasVisibleElementUsages) {
                    $result->set($key, $containerOrElement);
                }
            }
        }
        if ($reverse) {
            $iterator = $result->getIterator();
            $iterator->uasort(function ($a, $b) {
                return $b->getSortOrder() <=> $a->getSortOrder();
            });
        }
        return $result;
    }

    /**
     * Recursive function to get the first (sub)child container of a container (or survey), which is visible (determined by the displayConditionService).
     * Will return the "deepest" child of the tree, starting from the given container or survey parameter.
     * Returns the given parameter if it's a container, without any children.
     * Returns null if the given parameter is a survey without any (visible) containers.
     */
    protected function getFirstChildContainer(ContainerInterface|SurveyInterface $containerOrSurvey, SurveyResponseInterface $surveyResponse, bool $reverse): ?ContainerInterface
    {
        $childContainers = $containerOrSurvey instanceof ContainerInterface ? $containerOrSurvey->getChildContainers() : $containerOrSurvey->getContainers();
        /** @var Collection<int, ContainerInterface> $filteredChildContainers */
        $filteredChildContainers = $this->filterVisibleItems($childContainers, $surveyResponse, $reverse);
        /** @var false|ContainerInterface $nonEmptyChildContainer */
        $nonEmptyChildContainer = false;
        foreach ($filteredChildContainers as $child) {
            if (!$child->getElementUsages()->isEmpty()) {
                $nonEmptyChildContainer = $child;
                break;
            }
            if (!$child->getChildContainers()->isEmpty()) {
                return $this->getFirstChildContainer($child, $surveyResponse, $reverse);
            }
        }
        $firstChildContainer = $nonEmptyChildContainer ?: $filteredChildContainers->first();

        if ($firstChildContainer === false) {
            if ($containerOrSurvey instanceof ContainerInterface) {
                return $containerOrSurvey;
            }
            return null;
        }
        return $firstChildContainer;
    }

    /**
     * Only returns visible element-usages, determined by the displayConditionService
     * @param ElementUsageInterface|null $elementUsage Current elementUsage, if known. Used to check; if the current item is not in the filtered list, it will be returned in the result anyway
     * @return Collection<int, ElementUsageInterface>
     */
    protected function getSortedElementUsagesFromContainer(ContainerInterface $container, SurveyResponseInterface $surveyResponse, ElementUsageInterface|null $elementUsage, bool $reverse): Collection
    {
        if (!$container->isShuffleElementUsages()) {
            $containerElementUsages = $container->getElementUsages();
            $filteredElementUsages = $this->filterVisibleItems($containerElementUsages, $surveyResponse, $reverse);
            if ($elementUsage !== null && $containerElementUsages->contains($elementUsage) && !$filteredElementUsages->contains($elementUsage)) {
                $filteredElementUsages->add($elementUsage);
            }
            $iterator = $filteredElementUsages->getIterator();
            $iterator->uasort(function (ElementUsageInterface $a, ElementUsageInterface $b) use ($reverse) {
                if ($reverse) {
                    return $b->getSortOrder() <=> $a->getSortOrder();
                }
                return $a->getSortOrder() <=> $b->getSortOrder();
            });
            return new ArrayCollection(iterator_to_array($iterator));
        }

        $unfilteredElementUsages = new ArrayCollection();
        foreach ($container->getElementUsages() as $elementUsage) {
            $shuffledElementUsageSortOrder = $surveyResponse->getShuffledElementUsageSortOrders();
            $storedElementOrder = $shuffledElementUsageSortOrder[$elementUsage->getId()] ?? false;
            if ($storedElementOrder !== false) {
                $elementUsage->setSortOrder($storedElementOrder);
                $unfilteredElementUsages->set($storedElementOrder, $elementUsage);
                continue;
            }

            //Fallback; all shuffled sort-orders should be present at the surveyResponse,
            //but just to be sure, we add all elementUsages for which we didn't find a sort-order, to the end of the collection
            $unfilteredElementUsages->add($elementUsage);
        }

        $filteredElementUsages = $this->filterVisibleItems($unfilteredElementUsages, $surveyResponse, $reverse);
        if ($elementUsage !== null && $unfilteredElementUsages->contains($elementUsage) && !$filteredElementUsages->contains($elementUsage)) {
            $filteredElementUsages->add($elementUsage);
        }
        $iterator = $filteredElementUsages->getIterator();
        $iterator->uasort(function (ElementUsageInterface $a, ElementUsageInterface $b) use ($reverse) {
            if ($reverse) {
                return $b->getSortOrder() <=> $a->getSortOrder();
            }
            return $a->getSortOrder() <=> $b->getSortOrder();
        });
        return new ArrayCollection(iterator_to_array($iterator));
    }
}
