<?php

namespace App\Controller\Api;

use App\Entity\Produit\Produit;
use App\Entity\Vente\Promotion;
use App\Repository\Vente\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/promotions')]
class PromotionApiController extends AbstractController
{
    #[Route('', name: 'api_promotions_index', methods: ['GET'])]
    public function index(PromotionRepository $promotionRepository): JsonResponse
    {
        $promotions = $promotionRepository->findBy([], ['dateDebut' => 'DESC']);

        return $this->json(array_map($this->serializePromotion(...), $promotions));
    }

    #[Route('/{id}', name: 'api_promotions_show', methods: ['GET'])]
    public function show(Promotion $promotion): JsonResponse
    {
        return $this->json([
            ...$this->serializePromotion($promotion),
            'produits' => array_map(
                fn (Produit $p) => ['id' => $p->getId(), 'nom' => $p->getNom()],
                $promotion->getProduits()->toArray()
            ),
        ]);
    }

    #[Route('', name: 'api_promotions_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $promotion = new Promotion();
        $this->applyPayload($promotion, $data, $entityManager);

        $entityManager->persist($promotion);
        $entityManager->flush();

        return $this->json($this->serializePromotion($promotion), 201);
    }

    #[Route('/{id}', name: 'api_promotions_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Promotion $promotion, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->applyPayload($promotion, $data, $entityManager);
        $entityManager->flush();

        return $this->json($this->serializePromotion($promotion));
    }

    #[Route('/{id}', name: 'api_promotions_delete', methods: ['DELETE'])]
    public function delete(Promotion $promotion, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($promotion);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(array $data): array
    {
        $errors = [];

        if (empty($data['nom'])) {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if (empty($data['dateDebut'])) {
            $errors['dateDebut'] = 'La date de début est obligatoire.';
        }
        if (empty($data['dateFin'])) {
            $errors['dateFin'] = 'La date de fin est obligatoire.';
        } elseif (!empty($data['dateDebut']) && $data['dateFin'] < $data['dateDebut']) {
            $errors['dateFin'] = 'La date de fin doit être postérieure à la date de début.';
        }
        if (empty($data['tauxRemise']) && empty($data['montantRemise'])) {
            $errors['tauxRemise'] = 'Un taux de remise ou un montant de remise est obligatoire.';
        }

        return $errors;
    }

    private function applyPayload(Promotion $promotion, array $data, EntityManagerInterface $entityManager): void
    {
        $promotion->setNom($data['nom']);
        $promotion->setDateDebut(new \DateTime($data['dateDebut']));
        $promotion->setDateFin(new \DateTime($data['dateFin']));
        $promotion->setTauxRemise(!empty($data['tauxRemise']) ? (string) $data['tauxRemise'] : null);
        $promotion->setMontantRemise(!empty($data['montantRemise']) ? (string) $data['montantRemise'] : null);
        $promotion->setActif((bool) ($data['actif'] ?? true));

        foreach ($promotion->getProduits()->toArray() as $produit) {
            $promotion->removeProduit($produit);
        }
        foreach ($data['produitIds'] ?? [] as $produitId) {
            $produit = $entityManager->getRepository(Produit::class)->find($produitId);
            if ($produit) {
                $promotion->addProduit($produit);
            }
        }
    }

    private function serializePromotion(Promotion $promotion): array
    {
        return [
            'id' => $promotion->getId(),
            'nom' => $promotion->getNom(),
            'dateDebut' => $promotion->getDateDebut()?->format('Y-m-d'),
            'dateFin' => $promotion->getDateFin()?->format('Y-m-d'),
            'tauxRemise' => $promotion->getTauxRemise(),
            'montantRemise' => $promotion->getMontantRemise(),
            'actif' => $promotion->isActif(),
            'isCurrentlyActive' => $promotion->isCurrentlyActive(),
            'isExpired' => $promotion->isExpired(),
            'nbProduits' => count($promotion->getProduits()),
        ];
    }
}
