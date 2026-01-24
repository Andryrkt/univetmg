<?php

namespace App\Controller\Vente;

use App\Entity\Vente\Vente;
use App\Enum\StatutVente;
use App\Form\Vente\VenteType;
use App\Repository\Vente\VenteRepository;
use App\Service\StockManager;
use App\Service\PdfGenerator;
use App\Service\PricingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vente')]
class VenteController extends AbstractController
{
    #[Route('/', name: 'app_vente_index', methods: ['GET'])]
    public function index(VenteRepository $venteRepository): Response
    {
        return $this->render('vente/index.html.twig', [
            'ventes' => $venteRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, StockManager $stockManager, PricingService $pricingService): Response
    {
        $vente = new Vente();
        // Default Client? No, let user choose.
        
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vente->setUser($this->getUser());
            
            // Generate Facture Number
            // Format: V-{YmdHis}-{Random}
            $numeroFacture = sprintf('V-%s-%s', date('YmdHis'), substr(uniqid(), -4));
            $vente->setNumeroFacture($numeroFacture);

            // Calculate Conversion Factors and Pricing
            foreach ($vente->getLigneVentes() as $ligne) {
                $facteur = 1.0;
                $produit = $ligne->getProduit();
                $unite = $ligne->getUnite();

                if ($produit && $unite) {
                   // Check if unit is base unit
                   if ($product_base = $produit->getUniteDeBase()) {
                       if ($product_base->getId() === $unite->getId()) {
                           $facteur = 1.0;
                       } else {
                           // Check conditionnements
                           foreach ($produit->getConditionnements() as $cond) {
                               if ($cond->getUnite() && $cond->getUnite()->getId() === $unite->getId()) {
                                   $facteur = $cond->getQuantite();
                                   break;
                               }
                           }
                       }
                   }
                }
                $ligne->setFacteurConversion($facteur);

                // Calculate pricing with discounts
                if ($produit) {
                    $pricingResult = $pricingService->calculatePrice(
                        $produit,
                        $unite,
                        $vente->getClient(),
                        $ligne->getQuantite() ?? 1
                    );

                    $ligne->setPrixCatalogue((string) $pricingResult['prixCatalogue']);
                    $ligne->setTauxRemise($pricingResult['tauxRemise']);
                    $ligne->setMontantRemise((string) $pricingResult['montantRemise']);
                    $ligne->setTypeRemise($pricingResult['typeRemise']);
                    $ligne->setPrixUnitaire((string) $pricingResult['prixFinal']);
                }
            }

            $vente->recalculateTotal();

            // Process Stock if Validated
            if ($vente->getStatut() === StatutVente::VALIDEE) {
                try {
                    $stockManager->processVente($vente);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur de stock : ' . $e->getMessage());
                    // In real app, we might want to prevent save or revert.
                    // Here we just stop and render form again?
                    // Ideally, wrap in transaction.
                    // For now, let's catch and stop save.
                    return $this->render('vente/new.html.twig', [
                        'vente' => $vente,
                        'form' => $form,
                    ]);
                }
            }

            $entityManager->persist($vente);
            $entityManager->flush();

            return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vente/new.html.twig', [
            'vente' => $vente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vente_show', methods: ['GET'])]
    public function show(Vente $vente): Response
    {
        return $this->render('vente/show.html.twig', [
            'vente' => $vente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vente $vente, EntityManagerInterface $entityManager, StockManager $stockManager, PricingService $pricingService): Response
    {
        if ($vente->getStatut() !== StatutVente::BROUILLON) {
            $this->addFlash('warning', 'Seules les ventes en brouillon peuvent être modifiées. Veuillez annuler cette vente si nécessaire.');
            return $this->redirectToRoute('app_vente_show', ['id' => $vente->getId()]);
        }

        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Calculate Conversion Factors and Pricing
            foreach ($vente->getLigneVentes() as $ligne) {
                $facteur = 1.0;
                $produit = $ligne->getProduit();
                $unite = $ligne->getUnite();

                if ($produit && $unite) {
                   if ($product_base = $produit->getUniteDeBase()) {
                       if ($product_base->getId() === $unite->getId()) {
                           $facteur = 1.0;
                       } else {
                           foreach ($produit->getConditionnements() as $cond) {
                               if ($cond->getUnite() && $cond->getUnite()->getId() === $unite->getId()) {
                                   $facteur = $cond->getQuantite();
                                   break;
                               }
                           }
                       }
                   }
                }
                $ligne->setFacteurConversion($facteur);

                // Calculate pricing with discounts
                if ($produit) {
                    $pricingResult = $pricingService->calculatePrice(
                        $produit,
                        $unite,
                        $vente->getClient(),
                        $ligne->getQuantite() ?? 1
                    );

                    $ligne->setPrixCatalogue((string) $pricingResult['prixCatalogue']);
                    $ligne->setTauxRemise($pricingResult['tauxRemise']);
                    $ligne->setMontantRemise((string) $pricingResult['montantRemise']);
                    $ligne->setTypeRemise($pricingResult['typeRemise']);
                    $ligne->setPrixUnitaire((string) $pricingResult['prixFinal']);
                }
            }

            $vente->recalculateTotal();

            // Check if status changed to VALIDEE
            if ($vente->getStatut() === StatutVente::VALIDEE) {
                 try {
                    $stockManager->processVente($vente);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur de stock : ' . $e->getMessage());
                    // Revert status to brouillon to avoid invalid state if persisting
                     $vente->setStatut(StatutVente::BROUILLON); 
                     // We intentionally do not flush here to let user fix it, 
                     // but the form is already bound. 
                     // Simple way: render form with error.
                    return $this->render('vente/edit.html.twig', [
                        'vente' => $vente,
                        'form' => $form,
                    ]);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vente/edit.html.twig', [
            'vente' => $vente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_vente_cancel', methods: ['POST'])]
    public function cancel(Request $request, Vente $vente, EntityManagerInterface $entityManager, StockManager $stockManager): Response
    {
        if ($this->isCsrfTokenValid('cancel'.$vente->getId(), $request->getPayload()->getString('_token'))) {
            if ($vente->getStatut() === StatutVente::VALIDEE) {
                $stockManager->revertVente($vente);
                $vente->setStatut(StatutVente::ANNULEE);
                $entityManager->flush();
                $this->addFlash('success', 'La vente a été annulée et le stock a été rétabli.');
            } else {
                 // For drafts, just cancel status or delete? 
                 // Let's just set status to ANNULEE without stock movement
                 $vente->setStatut(StatutVente::ANNULEE);
                 $entityManager->flush();
                 $this->addFlash('success', 'La vente a été annulée.');
            }
        }

        return $this->redirectToRoute('app_vente_show', ['id' => $vente->getId()]);
    }

    #[Route('/{id}/pdf', name: 'app_vente_pdf', methods: ['GET'])]
    public function printPdf(Vente $vente, PdfGenerator $pdfGenerator): Response
    {
        $filename = sprintf('facture_%s.pdf', $vente->getNumeroFacture());
        
        return $pdfGenerator->generatePdfResponse(
            'vente/invoice_pdf.html.twig',
            ['vente' => $vente],
            $filename
        );
    }

    #[Route('/{id}/receipt', name: 'app_vente_receipt', methods: ['GET'])]
    public function printReceipt(Vente $vente, PdfGenerator $pdfGenerator): Response
    {
        $filename = sprintf('ticket_%s.pdf', $vente->getNumeroFacture());
        
        // Custom options for receipt: 80mm width. 80mm is approx 226.77pt.
        // We provide a custom paper size array [x_min, y_min, width, height].
        // Height can be large, dompdf will adjust to content.
        $pdfOptions = [
            'paper_size' => [0, 0, 226.77, 841.89],
            'paper_orientation' => 'portrait'
        ];

        return $pdfGenerator->generatePdfResponse(
            'vente/receipt_pdf.html.twig',
            ['vente' => $vente],
            $filename,
            $pdfOptions
        );
    }
}
