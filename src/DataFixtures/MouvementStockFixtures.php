<?php

namespace App\DataFixtures;

use App\Entity\Stock\MouvementStock;
use App\Entity\Produit\Produit;
use App\Entity\User;
use App\Enum\TypeMouvement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MouvementStockFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $produits = $manager->getRepository(Produit::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

        if (empty($produits) || empty($users)) {
            // Ne rien faire si aucune dépendance n'est chargée
            return;
        }

        foreach ($produits as $produit) {
            // Simuler un ou plusieurs approvisionnements pour chaque produit
            $nombreApprovisionnements = random_int(1, 3);
            $stockActuel = 0;

            for ($i = 0; $i < $nombreApprovisionnements; $i++) {
                $quantite = $faker->numberBetween(50, 200);
                
                $mouvement = new MouvementStock();
                $mouvement->setProduit($produit);
                $mouvement->setUser($faker->randomElement($users));
                $mouvement->setType(TypeMouvement::ENTREE);
                $mouvement->setQuantite($quantite);
                $mouvement->setMotif('Approvisionnement initial');
                $mouvement->setStockAvant($stockActuel);
                
                $stockActuel += $quantite;
                $mouvement->setStockApres($stockActuel);
                
                $manager->persist($mouvement);
            }
            
            // Mettre à jour le stock initial du produit pour refléter la somme des approvisionnements
            // Note: Dans une application réelle, le stock serait calculé dynamiquement.
            // Pour les fixtures, nous mettons à jour le stock initial pour la simplicité.
            $produit->setStockInitial($stockActuel);
            $manager->persist($produit);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProduitFixtures::class,
            UserFixtures::class,
        ];
    }
}
