<?php

namespace App\DataFixtures;

use App\Entity\Vente\Vente;
use App\Entity\Vente\LigneVente;
use App\Entity\Produit\Produit;
use App\Entity\User;
use App\Enum\StatutVente;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class VenteFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        // Récupérer les produits et les utilisateurs
        $produits = $manager->getRepository(Produit::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

        if (empty($produits) || empty($users)) {
            return; // Ne rien faire si les dépendances ne sont pas là
        }

        // Créer un certain nombre de ventes
        for ($i = 0; $i < 20; $i++) {
            $vente = new Vente();
            
            // Choisir un utilisateur (vendeur) au hasard
            $vendeur = $faker->randomElement($users);
            
            $vente->setUser($vendeur);
            $vente->setNumeroFacture('FACT-' . $faker->unique()->numberBetween(20240001, 20249999));
            $vente->setDateVente(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 months', 'now')));
            $vente->setStatut($faker->randomElement([StatutVente::VALIDEE, StatutVente::BROUILLON]));

            $totalVente = 0;
            $nombreLignes = $faker->numberBetween(1, 5);
            
            // Créer les lignes de vente
            for ($j = 0; $j < $nombreLignes; $j++) {
                $produitVendu = $faker->randomElement($produits);
                
                // S'assurer que le produit a un prix de vente
                if ($produitVendu->getPrixVente() > 0) {
                    $ligne = new LigneVente();
                    $quantite = $faker->numberBetween(1, 5);
                    $prixUnitaire = $produitVendu->getPrixVente();
                    $sousTotal = $quantite * $prixUnitaire;

                    $ligne->setProduit($produitVendu);
                    $ligne->setQuantite($quantite);
                    $ligne->setPrixUnitaire((string)$prixUnitaire);
                    $ligne->setSousTotal((string)$sousTotal);
                    
                    // Associer la ligne à la vente
                    $vente->addLigneVente($ligne);
                    $totalVente += $sousTotal;
                }
            }

            // Mettre à jour le total de la vente si des lignes ont été ajoutées
            if ($vente->getLigneVentes()->count() > 0) {
                $vente->setTotal((string)$totalVente);
                $manager->persist($vente);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        // Les ventes dépendent des mouvements de stock pour s'assurer qu'il y a du stock à vendre
        return [
            MouvementStockFixtures::class,
        ];
    }
}
