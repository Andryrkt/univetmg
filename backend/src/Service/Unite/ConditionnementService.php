<?php

namespace App\Service\Unite;

use App\Entity\Produit\Produit;
use App\Entity\Unite\Unite;

class ConditionnementService
{
    /**
     * Convertit une quantité d'un produit d'une unité d'origine vers une unité cible,
     * en utilisant l'unité de base du produit comme pivot.
     *
     * @param Produit $produit Le produit concerné.
     * @param float $quantite La quantité à convertir.
     * @param Unite $uniteOrigine L'unité de la quantité fournie.
     * @param Unite $uniteCible L'unité vers laquelle convertir la quantité.
     * @return float|null La quantité convertie, ou null si la conversion est impossible.
     */
    public function convertir(Produit $produit, float $quantite, Unite $uniteOrigine, Unite $uniteCible): ?float
    {
        if ($uniteOrigine === $uniteCible) {
            return $quantite;
        }

        $uniteDeBase = $produit->getUniteDeBase();

        // Étape 1: Convertir la quantité de l'unité d'origine vers l'unité de base.
        $quantiteEnUniteDeBase = $this->versUniteDeBase($produit, $quantite, $uniteOrigine);

        if ($quantiteEnUniteDeBase === null) {
            return null; // Conversion vers l'unité de base impossible.
        }

        // Étape 2: Convertir la quantité de l'unité de base vers l'unité cible.
        return $this->depuisUniteDeBase($produit, $quantiteEnUniteDeBase, $uniteCible);
    }

    /**
     * Convertit une quantité d'une unité donnée vers l'unité de base du produit.
     */
    private function versUniteDeBase(Produit $produit, float $quantite, Unite $uniteOrigine): ?float
    {
        $uniteDeBase = $produit->getUniteDeBase();

        if ($uniteOrigine === $uniteDeBase) {
            return $quantite;
        }

        // Chercher un conditionnement où l'unité d'origine est l'unité du conditionnement.
        // Exemple: convertir 2 "boîtes" en "pièces" (unité de base).
        foreach ($produit->getConditionnements() as $conditionnement) {
            if ($conditionnement->getUnite() === $uniteOrigine) {
                return $quantite * $conditionnement->getQuantite();
            }
        }

        return null; // Conversion non trouvée.
    }

    /**
     * Convertit une quantité depuis l'unité de base du produit vers une unité cible.
     */
    private function depuisUniteDeBase(Produit $produit, float $quantiteEnUniteDeBase, Unite $uniteCible): ?float
    {
        $uniteDeBase = $produit->getUniteDeBase();

        if ($uniteCible === $uniteDeBase) {
            return $quantiteEnUniteDeBase;
        }

        // Chercher un conditionnement où l'unité cible est l'unité du conditionnement.
        // Exemple: convertir 24 "pièces" (unité de base) en "boîtes".
        foreach ($produit->getConditionnements() as $conditionnement) {
            if ($conditionnement->getUnite() === $uniteCible) {
                if ($conditionnement->getQuantite() == 0) return null; // Éviter la division par zéro.
                return $quantiteEnUniteDeBase / $conditionnement->getQuantite();
            }
        }

        return null; // Conversion non trouvée.
    }
}
