<?php

namespace App\Service;

use App\Entity\Produit\Produit;
use App\Entity\Unite\Unite;
use App\Entity\Vente\Client;
use App\Repository\Vente\PromotionRepository;

class PricingService
{
    public function __construct(
        private PromotionRepository $promotionRepository
    ) {
    }

    /**
     * Calculate the final price for a product with all applicable discounts
     *
     * @param Produit $produit
     * @param Unite|null $unite
     * @param Client|null $client
     * @param float $quantite
     * @return array{prixCatalogue: float, tauxRemise: float, montantRemise: float, prixFinal: float, typeRemise: string|null}
     */
    public function calculatePrice(
        Produit $produit,
        ?Unite $unite,
        ?Client $client,
        float $quantite = 1
    ): array {
        // Step 1: Determine base catalog price
        $prixCatalogue = $this->getConditionnementPrice($produit, $unite);
        
        if ($prixCatalogue === null) {
            $prixCatalogue = $produit->getPrixVente() ?? 0.0;
        }

        // Step 2: Find all applicable discounts
        $discounts = [];

        // Check for promotions
        $promotions = $this->getApplicablePromotions($produit);
        foreach ($promotions as $promotion) {
            if ($promotion->getTauxRemise() !== null) {
                $discounts[] = [
                    'type' => 'promotion',
                    'taux' => (float) $promotion->getTauxRemise(),
                    'montant' => null,
                    'source' => $promotion->getNom()
                ];
            } elseif ($promotion->getMontantRemise() !== null) {
                $discounts[] = [
                    'type' => 'promotion',
                    'taux' => null,
                    'montant' => (float) $promotion->getMontantRemise(),
                    'source' => $promotion->getNom()
                ];
            }
        }

        // Check for client type discount
        $clientDiscount = $this->getClientDiscount($client);
        if ($clientDiscount !== null && $clientDiscount > 0) {
            $discounts[] = [
                'type' => 'client',
                'taux' => $clientDiscount,
                'montant' => null,
                'source' => $client?->getTypeClient()?->getNom() ?? 'Client'
            ];
        }

        // Step 3: Apply the best discount (most advantageous for the client)
        $bestDiscount = $this->selectBestDiscount($discounts, $prixCatalogue);

        if ($bestDiscount === null) {
            return [
                'prixCatalogue' => $prixCatalogue,
                'tauxRemise' => 0.0,
                'montantRemise' => 0.0,
                'prixFinal' => $prixCatalogue,
                'typeRemise' => null
            ];
        }

        // Calculate final price
        $montantRemise = 0.0;
        $tauxRemise = 0.0;

        if ($bestDiscount['taux'] !== null) {
            $tauxRemise = $bestDiscount['taux'];
            $montantRemise = $prixCatalogue * ($tauxRemise / 100);
        } elseif ($bestDiscount['montant'] !== null) {
            $montantRemise = $bestDiscount['montant'];
            $tauxRemise = ($montantRemise / $prixCatalogue) * 100;
        }

        $prixFinal = max(0, $prixCatalogue - $montantRemise);

        return [
            'prixCatalogue' => $prixCatalogue,
            'tauxRemise' => $tauxRemise,
            'montantRemise' => $montantRemise,
            'prixFinal' => $prixFinal,
            'typeRemise' => $bestDiscount['type']
        ];
    }

    /**
     * Get applicable promotions for a product
     *
     * @param Produit $produit
     * @param \DateTimeInterface|null $date
     * @return array
     */
    public function getApplicablePromotions(Produit $produit, ?\DateTimeInterface $date = null): array
    {
        return $this->promotionRepository->findPromotionsForProduct($produit, $date);
    }

    /**
     * Get client discount rate
     *
     * @param Client|null $client
     * @return float|null
     */
    public function getClientDiscount(?Client $client): ?float
    {
        if ($client === null) {
            return null;
        }

        $typeClient = $client->getTypeClient();
        if ($typeClient === null || !$typeClient->isActif()) {
            return null;
        }

        return (float) $typeClient->getTauxRemise();
    }

    /**
     * Get conditionnement-specific price
     *
     * @param Produit $produit
     * @param Unite|null $unite
     * @return float|null
     */
    public function getConditionnementPrice(Produit $produit, ?Unite $unite): ?float
    {
        if ($unite === null) {
            return null;
        }

        // Check if the unite is the base unit
        if ($produit->getUniteDeBase()?->getId() === $unite->getId()) {
            return null; // Use product base price
        }

        // Check conditionnements for this unit
        foreach ($produit->getConditionnements() as $conditionnement) {
            if ($conditionnement->getUnite()?->getId() === $unite->getId()) {
                // If specific price is defined, use it
                if ($conditionnement->getPrixVente() !== null) {
                    return $conditionnement->getPrixVente();
                }

                // If no specific price, calculate based on base unit price * quantity
                $basePrice = $produit->getPrixVente();
                if ($basePrice !== null) {
                    return $basePrice * $conditionnement->getQuantite();
                }
            }
        }

        return null;
    }

    /**
     * Select the best discount from available options
     *
     * @param array $discounts
     * @param float $prixCatalogue
     * @return array|null
     */
    private function selectBestDiscount(array $discounts, float $prixCatalogue): ?array
    {
        if (empty($discounts)) {
            return null;
        }

        $bestDiscount = null;
        $maxSavings = 0.0;

        foreach ($discounts as $discount) {
            $savings = 0.0;

            if ($discount['taux'] !== null) {
                $savings = $prixCatalogue * ($discount['taux'] / 100);
            } elseif ($discount['montant'] !== null) {
                $savings = $discount['montant'];
            }

            if ($savings > $maxSavings) {
                $maxSavings = $savings;
                $bestDiscount = $discount;
            }
        }

        return $bestDiscount;
    }
}
