<?php

namespace App\DataFixtures;

use App\Entity\Unite\Unite;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UniteFixtures extends Fixture
{
    public const UNITE_REFERENCE = 'unite_';

    public function load(ObjectManager $manager): void
    {
        $unitesData = [
            ['nom' => 'Milligramme', 'symbole' => 'mg'],
            ['nom' => 'Gramme', 'symbole' => 'g'],
            ['nom' => 'Kilogramme', 'symbole' => 'kg'],
            ['nom' => 'Millilitre', 'symbole' => 'ml'],
            ['nom' => 'Centilitre', 'symbole' => 'cl'],
            ['nom' => 'Litre', 'symbole' => 'L'],
            ['nom' => 'Unité', 'symbole' => 'U'],
            ['nom' => 'Comprimé', 'symbole' => 'cp'],
            ['nom' => 'Gélule', 'symbole' => 'gél'],
            ['nom' => 'Pipette', 'symbole' => 'pip'],
            ['nom' => 'Sachet', 'symbole' => 'sachet'],
            ['nom' => 'Flacon', 'symbole' => 'flacon'],
            ['nom' => 'Boîte', 'symbole' => 'boîte'],
            ['nom' => 'Tube', 'symbole' => 'tube'],
            ['nom' => 'Spray', 'symbole' => 'spray'],
        ];

        foreach ($unitesData as $data) {
            $unite = new Unite();
            $unite->setNom($data['nom']);
            $unite->setSymbole($data['symbole']);
            $manager->persist($unite);
            $this->addReference(self::UNITE_REFERENCE . strtolower($data['symbole']), $unite);
        }

        $manager->flush();
    }
}
