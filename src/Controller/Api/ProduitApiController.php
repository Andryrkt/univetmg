<?php

namespace App\Controller\Api;

use App\Entity\Produit\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/produit')]
class ProduitApiController extends AbstractController
{
    #[Route('/{id}/unites', name: 'api_produit_unites', methods: ['GET'])]
    public function getUnites(Produit $produit): JsonResponse
    {
        $unites = [];

        // Unité de base
        if ($produit->getUniteDeBase()) {
            $unites[] = [
                'id' => $produit->getUniteDeBase()->getId(),
                'nom' => $produit->getUniteDeBase()->getNom(),
                'symbole' => $produit->getUniteDeBase()->getSymbole(),
                'facteur' => 1.0,
                'isBase' => true
            ];
        }

        // Conditionnements
        foreach ($produit->getConditionnements() as $conditionnement) {
            if ($conditionnement->getUnite()) {
                $unites[] = [
                    'id' => $conditionnement->getUnite()->getId(),
                    'nom' => $conditionnement->getUnite()->getNom(),
                    'symbole' => $conditionnement->getUnite()->getSymbole(),
                    'facteur' => $conditionnement->getQuantite(), // Si 1 Caisse = 12 bouteilles, facteur = 12
                    'prix' => $conditionnement->getPrixVente(), // specific price if set
                    'isBase' => false
                ];
            }
        }

        return $this->json($unites);
    }
}
