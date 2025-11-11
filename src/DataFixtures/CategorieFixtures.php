<?php

namespace App\DataFixtures;

use App\Entity\Produit\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    public const CATEGORIE_REFERENCE = 'categorie_';

    public function load(ObjectManager $manager): void
    {
        $categoriesData = [
            'Médicaments' => [
                'Antiparasitaires', 'Antibiotiques', 'Vaccins', 'Anti-inflammatoires', 'Vermifuges'
            ],
            'Alimentation' => [
                'Croquettes', 'Pâtées', 'Compléments alimentaires', 'Aliments médicalisés'
            ],
            'Hygiène et Soins' => [
                'Shampoings', 'Soins des yeux et oreilles', 'Brosses et peignes', 'Produits dentaires'
            ],
            'Accessoires' => [
                'Laisses et colliers', 'Jouets', 'Gamelles et distributeurs', 'Cages et transport'
            ],
            'Matériel Médical' => [
                'Seringues et aiguilles', 'Pansements et bandages', 'Instruments chirurgicaux', 'Thermomètres'
            ],
        ];

        foreach ($categoriesData as $parentName => $subCategories) {
            $parentCategorie = new Categorie();
            $parentCategorie->setNom($parentName);
            $manager->persist($parentCategorie);
            $this->addReference(self::CATEGORIE_REFERENCE . $this->slugify($parentName), $parentCategorie);

            foreach ($subCategories as $subName) {
                $subCategorie = new Categorie();
                $subCategorie->setNom($subName);
                $subCategorie->setParent($parentCategorie);
                $manager->persist($subCategorie);
                $this->addReference(self::CATEGORIE_REFERENCE . $this->slugify($subName), $subCategorie);
            }
        }

        $manager->flush();
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
