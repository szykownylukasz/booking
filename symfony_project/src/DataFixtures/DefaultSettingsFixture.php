<?php

namespace App\DataFixtures;

use App\Entity\Settings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DefaultSettingsFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $defaultPrice = new Settings();
        $defaultPrice->setName(Settings::DAILY_PRICE)
            ->setValue('100.00');
        $manager->persist($defaultPrice);

        $defaultSpots = new Settings();
        $defaultSpots->setName(Settings::DEFAULT_TOTAL_SPOTS)
            ->setValue('10');
        $manager->persist($defaultSpots);

        $manager->flush();
    }
}
