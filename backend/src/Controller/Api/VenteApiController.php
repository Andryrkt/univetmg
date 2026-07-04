<?php

namespace App\Controller\Api;

use App\Entity\Produit\Produit;
use App\Entity\Stock\Lot;
use App\Entity\Stock\MouvementStock;
use App\Entity\Unite\Unite;
use App\Entity\User;
use App\Entity\Vente\Client;
use App\Entity\Vente\LigneVente;
use App\Entity\Vente\Vente;
use App\Enum\StatutVente;
use App\Enum\TypeMouvement;
use App\Repository\Vente\VenteRepository;
use App\Service\PdfGenerator;
use App\Service\PricingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ventes')]
class VenteApiController extends AbstractController
{
    #[Route('', name: 'api_ventes_index', methods: ['GET'])]
    public function index(VenteRepository $venteRepository): JsonResponse
    {
        $ventes = $venteRepository->findBy([], ['id' => 'DESC']);

        return $this->json(array_map($this->serializeVenteSummary(...), $ventes));
    }

    #[Route('/pricing', name: 'api_ventes_pricing', methods: ['POST'])]
    public function pricing(Request $request, EntityManagerInterface $entityManager, PricingService $pricingService): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $produit = !empty($data['produitId']) ? $entityManager->getRepository(Produit::class)->find($data['produitId']) : null;
        if (!$produit) {
            return $this->json(['errors' => ['produitId' => 'Produit introuvable.']], 422);
        }
        $unite = !empty($data['uniteId']) ? $entityManager->getRepository(Unite::class)->find($data['uniteId']) : null;
        $client = !empty($data['clientId']) ? $entityManager->getRepository(Client::class)->find($data['clientId']) : null;
        $quantite = isset($data['quantite']) ? (float) $data['quantite'] : 1.0;

        $result = $pricingService->calculatePrice($produit, $unite, $client, $quantite);

        return $this->json([
            'prixCatalogue' => $result['prixCatalogue'],
            'tauxRemise' => $result['tauxRemise'],
            'montantRemise' => $result['montantRemise'],
            'prixFinal' => $result['prixFinal'],
            'typeRemise' => $result['typeRemise'],
            'facteurConversion' => $this->calculerFacteurConversion($produit, $unite),
        ]);
    }

    #[Route('/{id}', name: 'api_ventes_show', methods: ['GET'])]
    public function show(Vente $vente): JsonResponse
    {
        return $this->json($this->serializeVente($vente));
    }

    #[Route('', name: 'api_ventes_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        PricingService $pricingService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $vente = new Vente();
        $vente->setUser($this->getUser());
        $vente->setNumeroFacture(sprintf('V-%s-%s', date('YmdHis'), substr(uniqid(), -4)));

        $this->applyPayload($vente, $data, $entityManager, $pricingService);

        $statut = StatutVente::from($data['statut']);
        if ($statut === StatutVente::VALIDEE) {
            $stockError = $this->validerEtDeduireStock($vente, $entityManager);
            if ($stockError) {
                return $this->json(['errors' => ['lignes' => $stockError]], 422);
            }
        }
        $vente->setStatut($statut);

        $entityManager->persist($vente);
        $entityManager->flush();

        return $this->json($this->serializeVente($vente), 201);
    }

    #[Route('/{id}', name: 'api_ventes_update', methods: ['PUT', 'PATCH'])]
    public function update(
        Request $request,
        Vente $vente,
        EntityManagerInterface $entityManager,
        PricingService $pricingService
    ): JsonResponse {
        if ($vente->getStatut() !== StatutVente::BROUILLON) {
            return $this->json(['errors' => ['global' => 'Seules les ventes en brouillon peuvent être modifiées.']], 409);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->applyPayload($vente, $data, $entityManager, $pricingService);

        $statut = StatutVente::from($data['statut']);
        if ($statut === StatutVente::VALIDEE) {
            $stockError = $this->validerEtDeduireStock($vente, $entityManager);
            if ($stockError) {
                return $this->json(['errors' => ['lignes' => $stockError]], 422);
            }
        }
        $vente->setStatut($statut);

        $entityManager->flush();

        return $this->json($this->serializeVente($vente));
    }

    #[Route('/{id}/cancel', name: 'api_ventes_cancel', methods: ['POST'])]
    public function cancel(Vente $vente, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($vente->getStatut() === StatutVente::VALIDEE) {
            foreach ($vente->getLigneVentes() as $ligne) {
                $quantiteReelle = $ligne->getQuantite() * ($ligne->getFacteurConversion() ?? 1.0);

                $lot = new Lot();
                $lot->setProduit($ligne->getProduit());
                $lot->setQuantite($quantiteReelle);
                $lot->setNumeroLot(sprintf('RETOUR-%s', $vente->getNumeroFacture()));
                $entityManager->persist($lot);

                $mouvement = new MouvementStock();
                $mouvement->setLot($lot);
                $mouvement->setType(TypeMouvement::RETOUR);
                $mouvement->setQuantite($quantiteReelle);
                $mouvement->setUser($this->getUser());
                $mouvement->setMotif(sprintf('Annulation Vente - Facture N°%s', $vente->getNumeroFacture()));
                $mouvement->setReference($vente->getNumeroFacture());
                $mouvement->setStockAvant(0);
                $mouvement->setStockApres($quantiteReelle);
                $entityManager->persist($mouvement);
            }
        }

        $vente->setStatut(StatutVente::ANNULEE);
        $entityManager->flush();

        return $this->json($this->serializeVente($vente));
    }

    #[Route('/{id}/pdf', name: 'api_ventes_pdf', methods: ['GET'])]
    public function pdf(Vente $vente, PdfGenerator $pdfGenerator): Response
    {
        return $pdfGenerator->generatePdfResponse(
            'vente/invoice_pdf.html.twig',
            ['vente' => $vente],
            sprintf('facture_%s.pdf', $vente->getNumeroFacture())
        );
    }

    #[Route('/{id}/receipt', name: 'api_ventes_receipt', methods: ['GET'])]
    public function receipt(Vente $vente, PdfGenerator $pdfGenerator): Response
    {
        return $pdfGenerator->generatePdfResponse(
            'vente/receipt_pdf.html.twig',
            ['vente' => $vente],
            sprintf('ticket_%s.pdf', $vente->getNumeroFacture()),
            ['paper_size' => [0, 0, 226.77, 841.89], 'paper_orientation' => 'portrait']
        );
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(array $data, EntityManagerInterface $entityManager): array
    {
        $errors = [];

        if (!empty($data['clientId']) && !$entityManager->getRepository(Client::class)->find($data['clientId'])) {
            $errors['clientId'] = 'Client introuvable.';
        }

        if (empty($data['statut']) || !in_array($data['statut'], ['brouillon', 'validee'], true)) {
            $errors['statut'] = 'Statut invalide.';
        }

        if (empty($data['lignes']) || !is_array($data['lignes'])) {
            $errors['lignes'] = 'Au moins une ligne de vente est requise.';
        } else {
            foreach ($data['lignes'] as $i => $ligne) {
                if (empty($ligne['produitId']) || !$entityManager->getRepository(Produit::class)->find($ligne['produitId'])) {
                    $errors["lignes[$i].produitId"] = 'Produit introuvable.';
                }
                if (empty($ligne['uniteId']) || !$entityManager->getRepository(Unite::class)->find($ligne['uniteId'])) {
                    $errors["lignes[$i].uniteId"] = 'Unité introuvable.';
                }
                if (!isset($ligne['quantite']) || !is_numeric($ligne['quantite']) || $ligne['quantite'] <= 0) {
                    $errors["lignes[$i].quantite"] = 'Quantité invalide.';
                }
            }
        }

        return $errors;
    }

    private function applyPayload(Vente $vente, array $data, EntityManagerInterface $entityManager, PricingService $pricingService): void
    {
        $vente->setDateVente(!empty($data['dateVente']) ? new \DateTimeImmutable($data['dateVente']) : new \DateTimeImmutable());
        $vente->setClient(!empty($data['clientId']) ? $entityManager->getRepository(Client::class)->find($data['clientId']) : null);

        foreach ($vente->getLigneVentes()->toArray() as $ligne) {
            $vente->removeLigneVente($ligne);
            $entityManager->remove($ligne);
        }

        foreach ($data['lignes'] as $ligneData) {
            $produit = $entityManager->getRepository(Produit::class)->find($ligneData['produitId']);
            $unite = $entityManager->getRepository(Unite::class)->find($ligneData['uniteId']);
            $quantite = (float) $ligneData['quantite'];

            $facteur = $this->calculerFacteurConversion($produit, $unite);
            $pricing = $pricingService->calculatePrice($produit, $unite, $vente->getClient(), $quantite);

            $ligne = new LigneVente();
            $ligne->setProduit($produit);
            $ligne->setUnite($unite);
            $ligne->setFacteurConversion($facteur);
            $ligne->setPrixCatalogue((string) $pricing['prixCatalogue']);
            $ligne->setTauxRemise($pricing['tauxRemise']);
            $ligne->setMontantRemise((string) $pricing['montantRemise']);
            $ligne->setTypeRemise($pricing['typeRemise']);
            $ligne->setPrixUnitaire((string) $pricing['prixFinal']);
            $ligne->setQuantite($quantite);

            $vente->addLigneVente($ligne);
        }

        $vente->recalculateTotal();
    }

    private function calculerFacteurConversion(Produit $produit, ?Unite $unite): float
    {
        if (!$unite) {
            return 1.0;
        }

        $uniteBase = $produit->getUniteDeBase();
        if ($uniteBase && $uniteBase->getId() === $unite->getId()) {
            return 1.0;
        }

        foreach ($produit->getConditionnements() as $conditionnement) {
            if ($conditionnement->getUnite() && $conditionnement->getUnite()->getId() === $unite->getId()) {
                return (float) $conditionnement->getQuantite();
            }
        }

        return 1.0;
    }

    /**
     * Vérifie le stock disponible pour toutes les lignes puis déduit via consommation FIFO des lots.
     * Retourne un message d'erreur si le stock est insuffisant, sinon null.
     */
    private function validerEtDeduireStock(Vente $vente, EntityManagerInterface $entityManager): ?string
    {
        $besoinsParProduit = [];
        foreach ($vente->getLigneVentes() as $ligne) {
            $quantiteReelle = $ligne->getQuantite() * ($ligne->getFacteurConversion() ?? 1.0);
            $produitId = $ligne->getProduit()->getId();
            $besoinsParProduit[$produitId] ??= ['produit' => $ligne->getProduit(), 'quantite' => 0.0];
            $besoinsParProduit[$produitId]['quantite'] += $quantiteReelle;
        }

        foreach ($besoinsParProduit as $besoin) {
            if ($besoin['produit']->getQuantiteEnStock() < $besoin['quantite']) {
                return sprintf(
                    'Stock insuffisant pour "%s". Stock actuel : %s, requis : %s.',
                    $besoin['produit']->getNom(),
                    $besoin['produit']->getQuantiteEnStock(),
                    $besoin['quantite']
                );
            }
        }

        /** @var User $user */
        $user = $this->getUser();

        foreach ($besoinsParProduit as $besoin) {
            $this->consommerStockFifo($besoin['produit'], $besoin['quantite'], $vente, $user, $entityManager);
        }

        return null;
    }

    private function consommerStockFifo(Produit $produit, float $quantite, Vente $vente, User $user, EntityManagerInterface $entityManager): void
    {
        $lots = $produit->getLots()->toArray();
        usort($lots, function (Lot $a, Lot $b) {
            $dateA = $a->getDatePeremption();
            $dateB = $b->getDatePeremption();
            if ($dateA === null && $dateB === null) {
                return $a->getId() <=> $b->getId();
            }
            if ($dateA === null) {
                return 1;
            }
            if ($dateB === null) {
                return -1;
            }

            return $dateA <=> $dateB ?: $a->getId() <=> $b->getId();
        });

        $restant = $quantite;
        foreach ($lots as $lot) {
            if ($restant <= 0) {
                break;
            }
            if ($lot->getQuantite() <= 0) {
                continue;
            }

            $aConsommer = min($lot->getQuantite(), $restant);
            $stockAvant = $lot->getQuantite();
            $lot->setQuantite($stockAvant - $aConsommer);

            $mouvement = new MouvementStock();
            $mouvement->setLot($lot);
            $mouvement->setType(TypeMouvement::SORTIE);
            $mouvement->setQuantite($aConsommer);
            $mouvement->setUser($user);
            $mouvement->setMotif(sprintf('Vente - Facture N°%s', $vente->getNumeroFacture()));
            $mouvement->setReference($vente->getNumeroFacture());
            $mouvement->setStockAvant($stockAvant);
            $mouvement->setStockApres($lot->getQuantite());
            $entityManager->persist($mouvement);

            $restant -= $aConsommer;
        }
    }

    private function serializeVenteSummary(Vente $vente): array
    {
        return [
            'id' => $vente->getId(),
            'numeroFacture' => $vente->getNumeroFacture(),
            'dateVente' => $vente->getDateVente()?->format('Y-m-d\TH:i:s'),
            'client' => $vente->getClient() ? ['id' => $vente->getClient()->getId(), 'nom' => $vente->getClient()->getNom()] : null,
            'total' => $vente->getTotal(),
            'statut' => $vente->getStatut()->value,
            'statutLabel' => $vente->getStatut()->label(),
            'nbLignes' => count($vente->getLigneVentes()),
        ];
    }

    private function serializeVente(Vente $vente): array
    {
        return [
            ...$this->serializeVenteSummary($vente),
            'user' => $vente->getUser() ? ['id' => $vente->getUser()->getId(), 'email' => $vente->getUser()->getEmail()] : null,
            'lignes' => array_map(
                fn (LigneVente $l) => [
                    'id' => $l->getId(),
                    'produit' => ['id' => $l->getProduit()->getId(), 'nom' => $l->getProduit()->getNom()],
                    'unite' => $l->getUnite() ? ['id' => $l->getUnite()->getId(), 'nom' => $l->getUnite()->getNom(), 'symbole' => $l->getUnite()->getSymbole()] : null,
                    'facteurConversion' => $l->getFacteurConversion(),
                    'quantite' => $l->getQuantite(),
                    'prixUnitaire' => $l->getPrixUnitaire(),
                    'prixCatalogue' => $l->getPrixCatalogue(),
                    'tauxRemise' => $l->getTauxRemise(),
                    'montantRemise' => $l->getMontantRemise(),
                    'typeRemise' => $l->getTypeRemise(),
                    'sousTotal' => $l->getSousTotal(),
                ],
                $vente->getLigneVentes()->toArray()
            ),
        ];
    }
}
