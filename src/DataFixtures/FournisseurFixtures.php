<?php

namespace App\DataFixtures;

use App\Entity\Admin\Fournisseur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FournisseurFixtures extends Fixture
{
    use SlugifyTrait;

    public const FOURNISSEUR_REFERENCE = 'fournisseur_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $fournisseursData = [
            'VetoPharma Distribution',
            'Centravet',
            'Zoetis France',
            'Boehringer Ingelheim Animal Health',
            'Elanco',
            'MSD Santé Animale',
            'Ceva Santé Animale',
        ];

        foreach ($fournisseursData as $nom) {
            $fournisseur = new Fournisseur();
            $fournisseur->setNom($nom);
            $fournisseur->setTelephone($faker->phoneNumber);
            $fournisseur->setAdresse($faker->address);
            $fournisseur->setEmail(strtolower(str_replace(' ', '.', $nom)) . '@example.com');
            
            $manager->persist($fournisseur);
            $this->addReference(self::FOURNISSEUR_REFERENCE . $this->slugify($nom), $fournisseur);
        }

        $manager->flush();
    }
}
