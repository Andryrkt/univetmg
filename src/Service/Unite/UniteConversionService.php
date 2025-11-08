<?php

namespace App\Service\Unite;

use App\Entity\Unite\Unite;
use App\Entity\Produit\Produit;



class UniteConversionService
{
    /**
     * Convertit une quantité d'un produit d'une unité source vers une unité cible.
     * Gère les conversions directes et indirectes.
     */
    public function convertir(
        Produit $produit,
        Unite $source,
        Unite $cible,
        float $quantite
    ): ?float {
        if ($source === $cible) {
            return $quantite;
        }

        $visited = [];
        return $this->recurseConversion($produit, $source, $cible, $quantite, $visited);
    }

    private function recurseConversion(
        Produit $produit,
        Unite $courante,
        Unite $cible,
        float $quantite,
        array &$visited
    ): ?float {
        // éviter les boucles infinies
        if (in_array($courante->getId(), $visited)) {
            return null;
        }
        $visited[] = $courante->getId();

        foreach ($produit->getUniteConversions() as $conversion) {
            // Conversion directe : source → cible
            if ($conversion->getUniteSource() === $courante) {
                $nouvelleQuantite = $quantite * $conversion->getFacteur();

                if ($conversion->getUniteCible() === $cible) {
                    return $nouvelleQuantite;
                }

                // Conversion indirecte (ex: boîte → plaquette → comprimé)
                $resultat = $this->recurseConversion(
                    $produit,
                    $conversion->getUniteCible(),
                    $cible,
                    $nouvelleQuantite,
                    $visited
                );
                if ($resultat !== null) {
                    return $resultat;
                }
            }

            // Conversion inverse (ex : plaquette → boîte)
            if ($conversion->getUniteCible() === $courante) {
                $nouvelleQuantite = $quantite / $conversion->getFacteur();

                if ($conversion->getUniteSource() === $cible) {
                    return $nouvelleQuantite;
                }

                $resultat = $this->recurseConversion(
                    $produit,
                    $conversion->getUniteSource(),
                    $cible,
                    $nouvelleQuantite,
                    $visited
                );
                if ($resultat !== null) {
                    return $resultat;
                }
            }
        }

        return null;
    }
}
