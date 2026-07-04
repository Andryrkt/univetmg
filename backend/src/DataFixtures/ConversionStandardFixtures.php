<?php

namespace App\DataFixtures;

use App\Entity\Unite\ConversionStandard;
use App\Entity\Unite\Unite;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConversionStandardFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $conversions = [
            ['source' => 'g', 'cible' => 'mg', 'facteur' => 1000],
            ['source' => 'kg', 'cible' => 'g', 'facteur' => 1000],
            ['source' => 'L', 'cible' => 'ml', 'facteur' => 1000],
            ['source' => 'kg', 'cible' => 'mg', 'facteur' => 1000000],
            ['source' => 'cl', 'cible' => 'ml', 'facteur' => 10],
            ['source' => 'L', 'cible' => 'cl', 'facteur' => 100],
        ];

        foreach ($conversions as $data) {
            $sourceRef = UniteFixtures::UNITE_REFERENCE . strtolower($data['source']);
            $cibleRef = UniteFixtures::UNITE_REFERENCE . strtolower($data['cible']);

            if ($this->hasReference($sourceRef, Unite::class) && $this->hasReference($cibleRef, Unite::class)) {
                $conversion = new ConversionStandard();
                $conversion->setUniteOrigine($this->getReference($sourceRef, Unite::class));
                $conversion->setUniteCible($this->getReference($cibleRef, Unite::class));
                $conversion->setFacteur($data['facteur']);
                $manager->persist($conversion);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UniteFixtures::class,
        ];
    }
}
