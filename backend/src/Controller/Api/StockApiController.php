<?php

namespace App\Controller\Api;

use App\Entity\Produit\Produit;
use App\Entity\Stock\Lot;
use App\Entity\Stock\MouvementStock;
use App\Enum\TypeMouvement;
use App\Repository\Produit\ProduitRepository;
use App\Repository\Stock\LotRepository;
use App\Repository\Stock\MouvementStockRepository;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/stock')]
class StockApiController extends AbstractController
{
    #[Route('', name: 'api_stock_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): JsonResponse
    {
        $aujourdhui = new \DateTime();
        $data = [];

        foreach ($produitRepository->findAll() as $produit) {
            $stockActuel = $produit->getQuantiteEnStock();
            $stockMinimum = $produit->getStockMinimum();

            if ($stockActuel <= 0) {
                $statut = 'rupture';
            } elseif ($stockActuel < $stockMinimum) {
                $statut = 'alerte';
            } else {
                $statut = 'ok';
            }

            $lotProche = null;
            $joursRestantsMin = null;
            foreach ($produit->getLots() as $lot) {
                if ($lot->getDatePeremption()) {
                    $interval = $aujourdhui->diff($lot->getDatePeremption());
                    $jours = $interval->invert ? -$interval->days : $interval->days;
                    if ($joursRestantsMin === null || $jours < $joursRestantsMin) {
                        $joursRestantsMin = $jours;
                        $lotProche = $lot;
                    }
                }
            }

            $statutPeremption = null;
            if ($lotProche) {
                if ($joursRestantsMin < 0) {
                    $statutPeremption = 'perime';
                } elseif ($joursRestantsMin <= 30) {
                    $statutPeremption = 'proche_peremption';
                } else {
                    $statutPeremption = 'ok';
                }
            }

            $data[] = [
                'produit' => ['id' => $produit->getId(), 'nom' => $produit->getNom(), 'code' => $produit->getCode()],
                'stockActuel' => $stockActuel,
                'stockMinimum' => $stockMinimum,
                'statut' => $statut,
                'datePeremption' => $lotProche?->getDatePeremption()?->format('Y-m-d'),
                'statutPeremption' => $statutPeremption,
                'joursRestants' => $joursRestantsMin,
            ];
        }

        return $this->json($data);
    }

    #[Route('/dashboard', name: 'api_stock_dashboard', methods: ['GET'])]
    public function dashboard(StockManager $stockManager, MouvementStockRepository $mouvementStockRepository): JsonResponse
    {
        $serializeProduit = fn (Produit $p) => ['id' => $p->getId(), 'nom' => $p->getNom(), 'code' => $p->getCode()];

        $valeurStock = $stockManager->calculerValeurStock();

        return $this->json([
            'produitsEnRupture' => array_map($serializeProduit, $stockManager->getProduitsEnRupture()),
            'produitsACommander' => array_map(
                fn ($item) => [
                    'produit' => $serializeProduit($item['produit']),
                    'stockActuel' => $item['stockActuel'],
                    'stockMinimum' => $item['stockMinimum'],
                    'manquant' => $item['manquant'],
                ],
                $stockManager->getProduitsACommander()
            ),
            'valeurTotale' => $valeurStock['valeurTotale'],
            'valeurDetails' => array_map(
                fn ($item) => ['produit' => $serializeProduit($item['produit']), 'valeurTotale' => $item['valeurTotale']],
                $valeurStock['details']
            ),
            'mouvementsRecents' => array_map($this->serializeMouvement(...), $mouvementStockRepository->findRecent(10)),
            'produitsPerimes' => array_map(
                fn ($item) => [
                    'produit' => $serializeProduit($item['produit']),
                    'lotId' => $item['lot']->getId(),
                    'datePeremption' => $item['datePeremption']->format('Y-m-d'),
                    'joursDepuisPeremption' => $item['joursDepuisPeremption'],
                ],
                $stockManager->getProduitsPerimes()
            ),
            'produitsProchesPeremption' => array_map(
                fn ($item) => [
                    'produit' => $serializeProduit($item['produit']),
                    'lotId' => $item['lot']->getId(),
                    'datePeremption' => $item['datePeremption']->format('Y-m-d'),
                    'joursRestants' => $item['joursRestants'],
                ],
                $stockManager->getProduitsProchesPeremption()
            ),
        ]);
    }

    #[Route('/mouvements', name: 'api_stock_mouvements', methods: ['GET'])]
    public function mouvements(MouvementStockRepository $mouvementStockRepository): JsonResponse
    {
        $mouvements = $mouvementStockRepository->findBy([], ['createdAt' => 'DESC'], 50);

        return $this->json(array_map($this->serializeMouvement(...), $mouvements));
    }

    #[Route('/lots', name: 'api_stock_lots', methods: ['GET'])]
    public function lots(Request $request, LotRepository $lotRepository): JsonResponse
    {
        $produitId = $request->query->get('produitId');
        $criteria = $produitId ? ['produit' => $produitId] : [];
        $lots = $lotRepository->findBy($criteria);

        return $this->json(array_map(
            fn (Lot $lot) => [
                'id' => $lot->getId(),
                'numeroLot' => $lot->getNumeroLot(),
                'quantite' => $lot->getQuantite(),
                'datePeremption' => $lot->getDatePeremption()?->format('Y-m-d'),
            ],
            $lots
        ));
    }

    #[Route('/produits/{id}/historique', name: 'api_stock_historique_produit', methods: ['GET'])]
    public function historiqueProduit(Produit $produit): JsonResponse
    {
        $mouvements = [];
        foreach ($produit->getLots() as $lot) {
            foreach ($lot->getMouvementsStock() as $mouvement) {
                $mouvements[] = $mouvement;
            }
        }
        usort($mouvements, fn (MouvementStock $a, MouvementStock $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

        return $this->json([
            'produit' => ['id' => $produit->getId(), 'nom' => $produit->getNom(), 'code' => $produit->getCode()],
            'stockActuel' => $produit->getQuantiteEnStock(),
            'mouvements' => array_map($this->serializeMouvement(...), $mouvements),
        ]);
    }

    #[Route('/entree', name: 'api_stock_entree', methods: ['POST'])]
    public function entree(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = [];
        $produit = !empty($data['produitId']) ? $entityManager->getRepository(Produit::class)->find($data['produitId']) : null;
        if (!$produit) {
            $errors['produitId'] = 'Le produit est obligatoire.';
        }
        if (!isset($data['quantite']) || !is_numeric($data['quantite']) || $data['quantite'] <= 0) {
            $errors['quantite'] = 'La quantité doit être un nombre positif.';
        }
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $lot = new Lot();
        $lot->setProduit($produit);
        $lot->setQuantite((float) $data['quantite']);
        $lot->setPrixAchat(isset($data['prixAchat']) && $data['prixAchat'] !== '' ? (float) $data['prixAchat'] : null);
        $lot->setNumeroLot($data['numeroLot'] ?? null);
        $lot->setDatePeremption(!empty($data['datePeremption']) ? new \DateTime($data['datePeremption']) : null);
        $entityManager->persist($lot);

        $mouvement = new MouvementStock();
        $mouvement->setLot($lot);
        $mouvement->setType(TypeMouvement::ENTREE);
        $mouvement->setQuantite($lot->getQuantite());
        $mouvement->setUser($this->getUser());
        $mouvement->setMotif('Entrée de stock / Réapprovisionnement');
        $mouvement->setStockAvant(0);
        $mouvement->setStockApres($lot->getQuantite());
        $entityManager->persist($mouvement);

        $entityManager->flush();

        return $this->json($this->serializeMouvement($mouvement), 201);
    }

    #[Route('/sortie', name: 'api_stock_sortie', methods: ['POST'])]
    public function sortie(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = [];
        $lot = !empty($data['lotId']) ? $entityManager->getRepository(Lot::class)->find($data['lotId']) : null;
        if (!$lot) {
            $errors['lotId'] = 'Le lot est obligatoire.';
        }
        $quantite = isset($data['quantite']) ? (float) $data['quantite'] : 0;
        if (!isset($data['quantite']) || !is_numeric($data['quantite']) || $quantite <= 0) {
            $errors['quantite'] = 'La quantité doit être un nombre positif.';
        } elseif ($lot && $lot->getQuantite() < $quantite) {
            $errors['quantite'] = sprintf('Le stock actuel du lot (%s) est insuffisant pour cette sortie (%s).', $lot->getQuantite(), $quantite);
        }
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $stockAvant = $lot->getQuantite();
        $lot->setQuantite($stockAvant - $quantite);

        $mouvement = new MouvementStock();
        $mouvement->setLot($lot);
        $mouvement->setType(TypeMouvement::SORTIE);
        $mouvement->setQuantite($quantite);
        $mouvement->setUser($this->getUser());
        $mouvement->setMotif($data['motif'] ?? 'Sortie manuelle de stock');
        $mouvement->setStockAvant($stockAvant);
        $mouvement->setStockApres($lot->getQuantite());
        $entityManager->persist($mouvement);

        $entityManager->flush();

        return $this->json($this->serializeMouvement($mouvement), 201);
    }

    #[Route('/ajustement', name: 'api_stock_ajustement', methods: ['POST'])]
    public function ajustement(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = [];
        $lot = !empty($data['lotId']) ? $entityManager->getRepository(Lot::class)->find($data['lotId']) : null;
        if (!$lot) {
            $errors['lotId'] = 'Le lot est obligatoire.';
        }
        if (!isset($data['nouvelleQuantite']) || !is_numeric($data['nouvelleQuantite']) || $data['nouvelleQuantite'] < 0) {
            $errors['nouvelleQuantite'] = 'La nouvelle quantité doit être un nombre positif ou nul.';
        }
        if (empty($data['motif'])) {
            $errors['motif'] = "Le motif de l'ajustement est obligatoire.";
        }
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $nouvelleQuantite = (float) $data['nouvelleQuantite'];
        $stockAvant = $lot->getQuantite();
        $difference = $nouvelleQuantite - $stockAvant;

        if ($difference == 0) {
            return $this->json(['errors' => ['nouvelleQuantite' => 'Aucun changement de quantité détecté.']], 422);
        }

        $lot->setQuantite($nouvelleQuantite);

        $mouvement = new MouvementStock();
        $mouvement->setLot($lot);
        $mouvement->setType(TypeMouvement::AJUSTEMENT);
        $mouvement->setQuantite(abs($difference));
        $mouvement->setUser($this->getUser());
        $mouvement->setMotif($data['motif']);
        $mouvement->setStockAvant($stockAvant);
        $mouvement->setStockApres($nouvelleQuantite);
        $entityManager->persist($mouvement);

        $entityManager->flush();

        return $this->json($this->serializeMouvement($mouvement), 201);
    }

    private function serializeMouvement(MouvementStock $mouvement): array
    {
        $produit = $mouvement->getProduit();

        return [
            'id' => $mouvement->getId(),
            'type' => $mouvement->getType()->value,
            'typeLabel' => $mouvement->getType()->getLabel(),
            'quantite' => $mouvement->getQuantite(),
            'motif' => $mouvement->getMotif(),
            'reference' => $mouvement->getReference(),
            'stockAvant' => $mouvement->getStockAvant(),
            'stockApres' => $mouvement->getStockApres(),
            'createdAt' => $mouvement->getCreatedAt()?->format(DATE_ATOM),
            'produit' => $produit ? ['id' => $produit->getId(), 'nom' => $produit->getNom()] : null,
            'lotId' => $mouvement->getLot()?->getId(),
            'user' => $mouvement->getUser() ? ['id' => $mouvement->getUser()->getId(), 'email' => $mouvement->getUser()->getEmail()] : null,
        ];
    }
}
