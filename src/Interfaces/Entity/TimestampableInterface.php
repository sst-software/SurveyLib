<?php

declare(strict_types=1);

namespace Sst\SurveyLibBundle\Interfaces\Entity;

use DateTimeImmutable;

interface TimestampableInterface
{
    public function setCreatedAt(?DateTimeImmutable $createdAt): static;

    public function getCreatedAt(): ?DateTimeImmutable;

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static;

    public function getUpdatedAt(): ?DateTimeImmutable;
}
