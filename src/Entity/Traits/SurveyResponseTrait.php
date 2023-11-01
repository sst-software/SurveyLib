<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\Traits;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sst\SurveyLibBundle\Interfaces\Entity\AnswerInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyInterface;

trait SurveyResponseTrait
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column]
    protected ?DateTimeImmutable $startDateTime = null;

    #[ORM\Column(nullable: true)]
    protected ?DateTimeImmutable $completedDateTime = null;

    #[ORM\ManyToOne(inversedBy: 'surveyResponses')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?SurveyInterface $survey = null;

    #[ORM\OneToMany(mappedBy: 'surveyResponse', targetEntity: AnswerInterface::class)]
    protected Collection|array $answers = [];

    #[ORM\Column(nullable: false)]
    protected array $shuffledElementUsageSortOrders = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDateTime(): ?DateTimeImmutable
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(DateTimeImmutable $startDateTime): static
    {
        $this->startDateTime = $startDateTime;
        return $this;
    }

    public function getCompletedDateTime(): ?DateTimeImmutable
    {
        return $this->completedDateTime;
    }

    public function setCompletedDateTime(?DateTimeImmutable $completedDateTime): static
    {
        $this->completedDateTime = $completedDateTime;
        return $this;
    }

    public function getShuffledElementUsageSortOrders(): array
    {
        return $this->shuffledElementUsageSortOrders;
    }

    public function setShuffledElementUsageSortOrders(array $shuffledElementUsageSortOrders): static
    {
        $this->shuffledElementUsageSortOrders = $shuffledElementUsageSortOrders;

        return $this;
    }

    public function getSurvey(): ?SurveyInterface
    {
        return $this->survey;
    }

    public function setSurvey(?SurveyInterface $survey): static
    {
        $this->survey = $survey;
        return $this;
    }

    /**
     * @return Collection<int, AnswerInterface>
     */
    public function getAnswers(): Collection
    {
        if (is_array($this->answers)) {
            $this->answers = new ArrayCollection();
        }
        return $this->answers;
    }

    public function addAnswer(AnswerInterface $answer): static
    {
        if (is_array($this->answers)) {
            $this->answers = new ArrayCollection();
        }
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setSurveyResponse($this);
        }

        return $this;
    }

    public function removeAnswer(AnswerInterface $answer): static
    {
        if (is_array($this->answers)) {
            $this->answers = new ArrayCollection();
        }
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getSurveyResponse() === $this) {
                $answer->setSurveyResponse(null);
            }
        }

        return $this;
    }
}
