<?php

namespace App\Repository;

use App\Entity\DailyAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DailyAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyAvailability::class);
    }

    public function findInDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        return $this->createQueryBuilder('d')
            ->where('d.date >= :startDate')
            ->andWhere('d.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }
}
