<?php

namespace App\Controller;

use App\Entity\Unite\ConversionStandard;
use App\Form\Unite\ConversionStandardType;
use App\Repository\Unite\ConversionStandardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/conversion/standard')]
class ConversionStandardController extends AbstractController
{
    #[Route('/', name: 'app_conversion_standard_index', methods: ['GET'])]
    public function index(ConversionStandardRepository $conversionStandardRepository): Response
    {
        return $this->render('conversion_standard/index.html.twig', [
            'conversion_standards' => $conversionStandardRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_conversion_standard_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $conversionStandard = new ConversionStandard();
        $form = $this->createForm(ConversionStandardType::class, $conversionStandard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($conversionStandard);
            $entityManager->flush();

            return $this->redirectToRoute('app_conversion_standard_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conversion_standard/new.html.twig', [
            'conversion_standard' => $conversionStandard,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conversion_standard_show', methods: ['GET'])]
    public function show(ConversionStandard $conversionStandard): Response
    {
        return $this->render('conversion_standard/show.html.twig', [
            'conversion_standard' => $conversionStandard,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conversion_standard_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ConversionStandard $conversionStandard, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConversionStandardType::class, $conversionStandard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_conversion_standard_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('conversion_standard/edit.html.twig', [
            'conversion_standard' => $conversionStandard,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conversion_standard_delete', methods: ['POST'])]
    public function delete(Request $request, ConversionStandard $conversionStandard, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conversionStandard->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($conversionStandard);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_conversion_standard_index', [], Response::HTTP_SEE_OTHER);
    }
}