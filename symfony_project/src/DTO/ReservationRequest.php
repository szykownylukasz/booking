<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ReservationRequest
{
    #[Assert\NotBlank]
    #[Assert\Type(\DateTimeImmutable::class)]
    private \DateTimeImmutable $startDate;

    #[Assert\NotBlank]
    #[Assert\Type(\DateTimeImmutable::class)]
    private \DateTimeImmutable $endDate;

    #[Assert\IsTrue(message: 'End date must be after start date')]
    public function isValidDateRange(): bool
    {
        return $this->endDate > $this->startDate;
    }

    public function __construct(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $this->startDate = $startDate instanceof \DateTimeImmutable ? $startDate : \DateTimeImmutable::createFromMutable($startDate);
        $this->endDate = $endDate instanceof \DateTimeImmutable ? $endDate : \DateTimeImmutable::createFromMutable($endDate);
    }

    public function getStartDate(): string
    {
        return $this->startDate->format('Y-m-d');
    }

    public function getRawStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate instanceof \DateTimeImmutable ? $startDate : \DateTimeImmutable::createFromMutable($startDate);
        return $this;
    }

    public function getEndDate(): string
    {
        return $this->endDate->format('Y-m-d');
    }

    public function getRawEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate instanceof \DateTimeImmutable ? $endDate : \DateTimeImmutable::createFromMutable($endDate);
        return $this;
    }
}
