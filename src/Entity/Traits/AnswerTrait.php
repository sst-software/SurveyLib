<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sst\SurveyLibBundle\Interfaces\Entity\ElementUsageInterface;
use Sst\SurveyLibBundle\Interfaces\Entity\SurveyResponseInterface;
use Sst\SurveyLibBundle\Types\RawAnswerType;

trait AnswerTrait
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(type: RawAnswerType::RAW_ANSWER_DATA_TYPE, nullable: true)]
    protected mixed $answer = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    protected bool $skipped = false;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?SurveyResponseInterface $surveyResponse = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?ElementUsageInterface $elementUsage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswer(): mixed
    {
        return $this->answer;
    }

    public function setAnswer(mixed $answer): static
    {
        $this->answer = $answer;
        if ($this->isSkipped()) {
            $this->skipped = false;
        }

        return $this;
    }

    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    public function setSkipped(bool $skipped): static
    {
        $this->skipped = $skipped;
        if ($skipped) {
            $this->answer = null;
        }
        return $this;
    }

    public function getSurveyResponse(): ?SurveyResponseInterface
    {
        return $this->surveyResponse;
    }

    public function setSurveyResponse(?SurveyResponseInterface $surveyResponse): static
    {
        $this->surveyResponse = $surveyResponse;

        return $this;
    }

    public function getElementUsage(): ?ElementUsageInterface
    {
        return $this->elementUsage;
    }

    public function setElementUsage(?ElementUsageInterface $elementUsage): static
    {
        $this->elementUsage = $elementUsage;

        return $this;
    }
}
