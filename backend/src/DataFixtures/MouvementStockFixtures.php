<?php

namespace App\DataFixtures;

use App\Entity\Stock\MouvementStock;
use App\Entity\Stock\Lot; // Added
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
        $lots = $manager->getRepository(Lot::class)->findAll(); // Changed from Produit to Lot
        $users = $manager->getRepository(User::class)->findAll();

        if (empty($lots) || empty($users)) {
            // Ne rien faire si aucune dépendance n'est chargée
            return;
        }

        foreach ($lots as $lot) { // Iterate over lots
            $nombreMouvements = random_int(1, 3);
            $currentLotQuantity = $lot->getQuantite(); // Get current quantity of the lot

            for ($i = 0; $i < $nombreMouvements; $i++) {
                $quantite = $faker->numberBetween(5, 50); // Smaller movements for lots
                $type = $faker->randomElement([TypeMouvement::ENTREE, TypeMouvement::SORTIE]);

                $mouvement = new MouvementStock();
                $mouvement->setLot($lot); // Set Lot instead of Produit
                $mouvement->setUser($faker->randomElement($users));
                $mouvement->setType($type);
                $mouvement->setQuantite($quantite);
                $mouvement->setMotif($type === TypeMouvement::ENTREE ? 'Réapprovisionnement' : 'Vente');
                
                // Simplified stockAvant/Apres logic for fixture
                $mouvement->setStockAvant($currentLotQuantity);
                if ($type === TypeMouvement::ENTREE) {
                    $currentLotQuantity += $quantite;
                } else {
                    $currentLotQuantity -= $quantite;
                    if ($currentLotQuantity < 0) $currentLotQuantity = 0; // Prevent negative stock in fixture
                }
                $mouvement->setStockApres($currentLotQuantity);
                
                // Also update the lot's quantity, if this is how the stock manager would work
                $lot->setQuantite($currentLotQuantity);

                $manager->persist($mouvement);
            }
            $manager->persist($lot); // Persist the updated lot quantity
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProduitFixtures::class, // Need ProduitFixtures to create Lots
            UserFixtures::class,
        ];
    }
}
