<?php

namespace App\Repository;

use App\Entity\Settings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Settings::class);
    }

    public function findByKey(string $key): ?Settings
    {
        return $this->findOneBy(['key' => $key]);
    }
}
