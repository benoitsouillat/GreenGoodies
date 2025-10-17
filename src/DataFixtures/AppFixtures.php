<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création de l'administrateur
        UserFactory::createAdmin();
        
        // Création de 15 produits
        ProductFactory::createMany(15);

        $manager->flush();
    }
}
