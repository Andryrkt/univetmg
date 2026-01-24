<?php

namespace App\Controller\Vente;

use App\Entity\Vente\TypeClient;
use App\Form\Vente\TypeClientType;
use App\Repository\Vente\TypeClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type-client')]
class TypeClientController extends AbstractController
{
    #[Route('/', name: 'app_type_client_index', methods: ['GET'])]
    public function index(TypeClientRepository $typeClientRepository): Response
    {
        return $this->render('type_client/index.html.twig', [
            'type_clients' => $typeClientRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_type_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeClient = new TypeClient();
        $form = $this->createForm(TypeClientType::class, $typeClient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeClient);
            $entityManager->flush();

            $this->addFlash('success', 'Le type de client a été créé avec succès.');

            return $this->redirectToRoute('app_type_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_client/new.html.twig', [
            'type_client' => $typeClient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_client_show', methods: ['GET'])]
    public function show(TypeClient $typeClient): Response
    {
        return $this->render('type_client/show.html.twig', [
            'type_client' => $typeClient,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeClient $typeClient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeClientType::class, $typeClient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le type de client a été modifié avec succès.');

            return $this->redirectToRoute('app_type_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_client/edit.html.twig', [
            'type_client' => $typeClient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_client_delete', methods: ['POST'])]
    public function delete(Request $request, TypeClient $typeClient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typeClient->getId(), $request->getPayload()->getString('_token'))) {
            // Check if there are clients using this type
            if ($typeClient->getClients()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer ce type de client car il est utilisé par ' . $typeClient->getClients()->count() . ' client(s).');
                return $this->redirectToRoute('app_type_client_index', [], Response::HTTP_SEE_OTHER);
            }

            $entityManager->remove($typeClient);
            $entityManager->flush();

            $this->addFlash('success', 'Le type de client a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_type_client_index', [], Response::HTTP_SEE_OTHER);
    }
}
