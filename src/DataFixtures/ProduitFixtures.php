<?php

namespace App\DataFixtures;

use App\Entity\Produit\Produit;
use App\Entity\Admin\Fournisseur;
use App\Entity\Produit\Categorie;
use App\Entity\Unite\Unite;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProduitFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupérer les références des catégories, unités et fournisseurs par classe
        $categorieReferences = array_values($this->referenceRepository->getReferencesByClass(Categorie::class));
        $uniteReferences = array_values($this->referenceRepository->getReferencesByClass(Unite::class));
        $fournisseurReferences = array_values($this->referenceRepository->getReferencesByClass(Fournisseur::class));

        if (empty($categorieReferences) || empty($uniteReferences) || empty($fournisseurReferences)) {
            echo "Warning: One or more reference arrays are empty. Skipping product creation.\n";
            return;
        }

        // Utiliser le premier élément de chaque tableau pour le test
        $firstCategorie = $categorieReferences[0];
        $firstUnite = $uniteReferences[0];
        $firstFournisseur = $fournisseurReferences[0];

        for ($i = 0; $i < 25; $i++) {
            $produit = new Produit();

            $produit->setNom($faker->words(3, true));
            $produit->setDescription($faker->sentence(10));
            $produit->setCode('PROD-' . $faker->unique()->numberBetween(1000, 9999));
            
            $prixAchat = $faker->randomFloat(2, 5, 100);
            $produit->setPrixAchat($prixAchat);
            $produit->setPrixVente($prixAchat * $faker->randomFloat(2, 1.2, 2.5));

            $produit->setStockInitial($faker->numberBetween(50, 200));
            $produit->setStockMinimum($faker->numberBetween(10, 30));

            if ($faker->boolean(70)) { // 70% de chance d'avoir une date de péremption
                $produit->setDatePeremption($faker->dateTimeBetween('+6 months', '+3 years'));
            }

            // Assigner les relations en utilisant le premier élément
            $produit->setCategorie($firstCategorie);
            $produit->setUniteDeBase($firstUnite);
            $produit->setFournisseur($firstFournisseur);

            $manager->persist($produit);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategorieFixtures::class,
            UniteFixtures::class,
            FournisseurFixtures::class,
        ];
    }
}
