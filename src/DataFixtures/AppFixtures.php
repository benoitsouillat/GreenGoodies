<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Factory\ProductFactory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        ProductFactory::createMany(15);

        $manager->flush();
    }
}
