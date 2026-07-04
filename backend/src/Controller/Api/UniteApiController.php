<?php

namespace App\Controller\Api;

use App\Entity\Unite\Unite;
use App\Repository\Unite\ConditionnementRepository;
use App\Repository\Unite\ConversionStandardRepository;
use App\Repository\Unite\UniteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/unites')]
class UniteApiController extends AbstractController
{
    #[Route('', name: 'api_unites_index', methods: ['GET'])]
    public function index(UniteRepository $uniteRepository): JsonResponse
    {
        $unites = $uniteRepository->findBy([], ['id' => 'DESC']);

        return $this->json(array_map($this->serializeUnite(...), $unites));
    }

    #[Route('/{id}', name: 'api_unites_show', methods: ['GET'])]
    public function show(
        Unite $unite,
        ConditionnementRepository $conditionnementRepository,
        ConversionStandardRepository $conversionStandardRepository
    ): JsonResponse {
        $conditionnements = $conditionnementRepository->findBy(['unite' => $unite]);
        $conversions = $conversionStandardRepository->findInvolvingUnit($unite);

        return $this->json([
            ...$this->serializeUnite($unite),
            'conditionnements' => array_map(
                fn ($c) => [
                    'id' => $c->getId(),
                    'quantite' => $c->getQuantite(),
                    'produit' => $c->getProduit() ? ['id' => $c->getProduit()->getId(), 'nom' => $c->getProduit()->getNom()] : null,
                ],
                $conditionnements
            ),
            'conversions' => array_map(
                fn ($c) => [
                    'id' => $c->getId(),
                    'uniteSource' => $c->getUniteOrigine()?->getNom(),
                    'uniteCible' => $c->getUniteCible()?->getNom(),
                    'facteur' => $c->getFacteur(),
                ],
                $conversions
            ),
        ]);
    }

    #[Route('', name: 'api_unites_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $unite = new Unite();
        $unite->setNom($data['nom']);
        $unite->setSymbole($data['symbole'] ?? null);

        $entityManager->persist($unite);
        $entityManager->flush();

        return $this->json($this->serializeUnite($unite), 201);
    }

    #[Route('/{id}', name: 'api_unites_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Unite $unite, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $unite->setNom($data['nom']);
        $unite->setSymbole($data['symbole'] ?? null);
        $entityManager->flush();

        return $this->json($this->serializeUnite($unite));
    }

    #[Route('/{id}', name: 'api_unites_delete', methods: ['DELETE'])]
    public function delete(Unite $unite, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($unite);
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
        if (empty($data['symbole'])) {
            $errors['symbole'] = 'Le symbole est obligatoire.';
        }

        return $errors;
    }

    private function serializeUnite(Unite $unite): array
    {
        return [
            'id' => $unite->getId(),
            'nom' => $unite->getNom(),
            'symbole' => $unite->getSymbole(),
            'nbProduits' => count($unite->getProduits()),
        ];
    }
}
