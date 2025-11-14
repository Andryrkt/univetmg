<?php

namespace App\DataFixtures;

use App\Entity\Produit\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    use SlugifyTrait;

    public const CATEGORIE_REFERENCE = 'categorie_';

    public function load(ObjectManager $manager): void
    {
        $categoriesData = [
            'Médicaments' => [
                'Antiparasitaires' => ['Pour chiens', 'Pour chats', 'Pour bovins'],
                'Antibiotiques' => [],
                'Vaccins' => [],
                'Anti-inflammatoires' => [],
                'Vermifuges' => [],
            ],
            'Alimentation' => [
                'Croquettes' => ['Pour chiots', 'Pour chiens adultes', 'Pour chatons'],
                'Pâtées' => [],
                'Compléments alimentaires' => [],
                'Aliments médicalisés' => [],
            ],
            'Hygiène et Soins' => [
                'Shampoings' => [],
                'Soins des yeux et oreilles' => [],
                'Brosses et peignes' => [],
                'Produits dentaires' => [],
            ],
            'Accessoires' => [
                'Laisses et colliers' => [],
                'Jouets' => [],
                'Gamelles et distributeurs' => [],
                'Cages et transport' => [],
            ],
            'Matériel Médical' => [
                'Seringues et aiguilles' => [],
                'Pansements et bandages' => [],
                'Instruments chirurgicaux' => [],
                'Thermomètres' => [],
            ],
        ];

        foreach ($categoriesData as $parentName => $subCategoriesData) {
            $parentCategorie = new Categorie();
            $parentCategorie->setNom($parentName);
            $manager->persist($parentCategorie);
            $this->addReference(self::CATEGORIE_REFERENCE . $this->slugify($parentName), $parentCategorie);

            foreach ($subCategoriesData as $subName => $level3Categories) {
                $subCategorie = new Categorie();
                $subCategorie->setNom($subName);
                $subCategorie->setParent($parentCategorie);
                $manager->persist($subCategorie);
                $this->addReference(self::CATEGORIE_REFERENCE . $this->slugify($subName), $subCategorie);

                foreach ($level3Categories as $level3Name) {
                    $level3Categorie = new Categorie();
                    $level3Categorie->setNom($level3Name);
                    $level3Categorie->setParent($subCategorie);
                    $manager->persist($level3Categorie);
                    $this->addReference(self::CATEGORIE_REFERENCE . $this->slugify($level3Name), $level3Categorie);
                }
            }
        }

        $manager->flush();
    }
}
