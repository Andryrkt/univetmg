<?php

namespace App\Controller\Api;

use App\Entity\Unite\ConversionStandard;
use App\Entity\Unite\Unite;
use App\Repository\Unite\ConversionStandardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/conversion-standards')]
class ConversionStandardApiController extends AbstractController
{
    #[Route('', name: 'api_conversion_standards_index', methods: ['GET'])]
    public function index(ConversionStandardRepository $conversionStandardRepository): JsonResponse
    {
        return $this->json(array_map($this->serialize(...), $conversionStandardRepository->findAll()));
    }

    #[Route('/{id}', name: 'api_conversion_standards_show', methods: ['GET'])]
    public function show(ConversionStandard $conversionStandard): JsonResponse
    {
        return $this->json($this->serialize($conversionStandard));
    }

    #[Route('', name: 'api_conversion_standards_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, null, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $conversionStandard = new ConversionStandard();
        $this->applyPayload($conversionStandard, $data, $entityManager);

        $entityManager->persist($conversionStandard);
        $entityManager->flush();

        return $this->json($this->serialize($conversionStandard), 201);
    }

    #[Route('/{id}', name: 'api_conversion_standards_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ConversionStandard $conversionStandard, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, $conversionStandard, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->applyPayload($conversionStandard, $data, $entityManager);
        $entityManager->flush();

        return $this->json($this->serialize($conversionStandard));
    }

    #[Route('/{id}', name: 'api_conversion_standards_delete', methods: ['DELETE'])]
    public function delete(ConversionStandard $conversionStandard, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($conversionStandard);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(array $data, ?ConversionStandard $current, EntityManagerInterface $entityManager): array
    {
        $errors = [];

        $uniteOrigine = !empty($data['uniteOrigineId']) ? $entityManager->getRepository(Unite::class)->find($data['uniteOrigineId']) : null;
        $uniteCible = !empty($data['uniteCibleId']) ? $entityManager->getRepository(Unite::class)->find($data['uniteCibleId']) : null;

        if (!$uniteOrigine) {
            $errors['uniteOrigineId'] = "L'unité d'origine est obligatoire.";
        }
        if (!$uniteCible) {
            $errors['uniteCibleId'] = "L'unité cible est obligatoire.";
        }
        if ($uniteOrigine && $uniteCible && $uniteOrigine->getId() === $uniteCible->getId()) {
            $errors['uniteCibleId'] = "L'unité cible doit être différente de l'unité d'origine.";
        }
        if (!isset($data['facteur']) || !is_numeric($data['facteur']) || $data['facteur'] <= 0) {
            $errors['facteur'] = 'Le facteur doit être un nombre positif.';
        }

        if ($uniteOrigine && $uniteCible && !$errors) {
            $existing = $entityManager->getRepository(ConversionStandard::class)->findOneBy([
                'uniteOrigine' => $uniteOrigine,
                'uniteCible' => $uniteCible,
            ]);
            if ($existing && (!$current || $existing->getId() !== $current->getId())) {
                $errors['uniteCibleId'] = 'Cette conversion existe déjà.';
            }
        }

        return $errors;
    }

    private function applyPayload(ConversionStandard $conversionStandard, array $data, EntityManagerInterface $entityManager): void
    {
        $conversionStandard->setUniteOrigine($entityManager->getRepository(Unite::class)->find($data['uniteOrigineId']));
        $conversionStandard->setUniteCible($entityManager->getRepository(Unite::class)->find($data['uniteCibleId']));
        $conversionStandard->setFacteur((float) $data['facteur']);
    }

    private function serialize(ConversionStandard $conversionStandard): array
    {
        return [
            'id' => $conversionStandard->getId(),
            'uniteOrigine' => [
                'id' => $conversionStandard->getUniteOrigine()->getId(),
                'nom' => $conversionStandard->getUniteOrigine()->getNom(),
            ],
            'uniteCible' => [
                'id' => $conversionStandard->getUniteCible()->getId(),
                'nom' => $conversionStandard->getUniteCible()->getNom(),
            ],
            'facteur' => $conversionStandard->getFacteur(),
        ];
    }
}
