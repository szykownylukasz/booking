<?php

namespace App\Entity;

use App\Repository\DailyAvailabilityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailyAvailabilityRepository::class)]
#[ORM\UniqueConstraint(name: "UNIQ_DATE", columns: ["date"])]
class DailyAvailability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $availableSpots = null;

    #[ORM\Column]
    private ?int $totalSpots = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getAvailableSpots(): ?int
    {
        return $this->availableSpots;
    }

    public function setAvailableSpots(int $availableSpots): self
    {
        $this->availableSpots = $availableSpots;
        return $this;
    }

    public function getTotalSpots(): ?int
    {
        return $this->totalSpots;
    }

    public function setTotalSpots(int $totalSpots): self
    {
        $this->totalSpots = $totalSpots;
        return $this;
    }

    public function decreaseAvailableSpots(int $count = 1): self
    {
        $this->availableSpots = max(0, $this->availableSpots - $count);
        return $this;
    }

    public function increaseAvailableSpots(int $count = 1): self
    {
        $this->availableSpots = min($this->totalSpots, $this->availableSpots + $count);
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
