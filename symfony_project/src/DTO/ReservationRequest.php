<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ReservationRequest
{
    #[Assert\NotBlank]
    #[Assert\Type(\DateTime::class)]
    private \DateTime $startDate;

    #[Assert\NotBlank]
    #[Assert\Type(\DateTime::class)]
    private \DateTime $endDate;

    #[Assert\IsTrue(message: 'End date must be after or equal to start date')]
    public function isValidDateRange(): bool
    {
        return $this->endDate >= $this->startDate;
    }

    public function __construct(\DateTime $startDate, \DateTime $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }
}
