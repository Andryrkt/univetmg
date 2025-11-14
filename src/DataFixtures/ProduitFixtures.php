<?php

namespace App\DataFixtures;

use App\Entity\Produit\Produit;
use App\Entity\Admin\Fournisseur;
use App\Entity\Produit\Categorie;
use App\Entity\Unite\Unite;
use App\Entity\Unite\UniteConversion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProduitFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupération des catégories selon le format de CategorieFixtures
        $categories = [];
        $categorieReferences = [
            'medicaments', 'antiparasitaires', 'antibiotiques', 'vaccins', 'anti-inflammatoires', 'vermifuges',
            'alimentation', 'croquettes', 'patees', 'complements-alimentaires', 'aliments-medicalises',
            'hygiene-et-soins', 'shampoings', 'soins-des-yeux-et-oreilles', 'brosses-et-peignes', 'produits-dentaires',
            'accessoires', 'laisses-et-colliers', 'jouets', 'gamelles-et-distributeurs', 'cages-et-transport',
            'materiel-medical', 'seringues-et-aiguilles', 'pansements-et-bandages', 'instruments-chirurgicaux', 'thermometres'
        ];

        foreach ($categorieReferences as $refName) {
            $referenceName = 'categorie_' . $refName;
            if ($this->hasReference($referenceName, Categorie::class)) {
                $categories[] = $this->getReference($referenceName, Categorie::class);
            }
        }

        // Récupération des fournisseurs selon le format de FournisseurFixtures
        $fournisseurs = [];
        $fournisseurReferences = [
            'vetopharma-distribution',
            'centravet',
            'zoetis-france',
            'boehringer-ingelheim-animal-health',
            'elanco',
            'msd-sante-animale',
            'ceva-sante-animale'
        ];

        foreach ($fournisseurReferences as $refName) {
            $referenceName = 'fournisseur_' . $refName;
            if ($this->hasReference($referenceName, Fournisseur::class)) {
                $fournisseurs[] = $this->getReference($referenceName, Fournisseur::class);
            }
        }

        // Récupération des unités selon le format de UniteFixtures
        $unites = [];
        $uniteReferences = [
            'mg', 'g', 'kg', 'ml', 'l', 'u', 'cp', 'gél', 'pip', 'sachet', 'flacon', 'boîte', 'tube', 'spray'
        ];

        foreach ($uniteReferences as $refName) {
            $referenceName = 'unite_' . $refName;
            if ($this->hasReference($referenceName, Unite::class)) {
                $unites[] = $this->getReference($referenceName, Unite::class);
            }
        }

        // Fallback: récupération depuis la base de données si les références ne sont pas trouvées
        if (empty($categories)) {
            $categories = $manager->getRepository(Categorie::class)->findAll();
        }
        if (empty($unites)) {
            $unites = $manager->getRepository(Unite::class)->findAll();
        }
        if (empty($fournisseurs)) {
            $fournisseurs = $manager->getRepository(Fournisseur::class)->findAll();
        }

        if (empty($categories) || empty($unites) || empty($fournisseurs)) {
            throw new \Exception('Impossible de charger les fixtures Produit: certaines dépendances sont manquantes (Catégories, Unités ou Fournisseurs)');
        }

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

            if ($faker->boolean(70)) {
                $produit->setDatePeremption($faker->dateTimeBetween('+6 months', '+3 years'));
            }

            $baseUnit = $faker->randomElement($unites);
            $produit->setCategorie($faker->randomElement($categories));
            $produit->setUniteDeBase($baseUnit);
            $produit->setFournisseur($faker->randomElement($fournisseurs));

            $manager->persist($produit);

            // Créer des conversions d'unités
            $otherUnits = array_filter($unites, fn($u) => $u !== $baseUnit);
            if (!empty($otherUnits)) {
                $numberOfConversions = $faker->numberBetween(0, min(2, count($otherUnits)));
                
                if ($numberOfConversions > 0) {
                    $chosenUnits = $faker->randomElements($otherUnits, $numberOfConversions);

                    foreach ($chosenUnits as $targetUnit) {
                        $conversion = new UniteConversion();
                        $conversion->setProduit($produit);
                        $conversion->setUniteSource($baseUnit);
                        $conversion->setUniteCible($targetUnit);
                        $conversion->setFacteur($faker->randomFloat(2, 0.1, 10));
                        $manager->persist($conversion);
                    }
                }
            }

            // Ajouter une référence pour le produit
            $this->addReference('produit_' . $i, $produit);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategorieFixtures::class,
            UniteFixtures::class,
            FournisseurFixtures::class,
            UserFixtures::class,
        ];
    }
}