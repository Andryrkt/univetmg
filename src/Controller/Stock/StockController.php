<?php

namespace App\Controller\Stock;

use App\Entity\Produit\Produit;
use App\Entity\Stock\MouvementStock;
use App\Enum\TypeMouvement;
use App\Form\Stock\MouvementStockType;
use App\Repository\Produit\ProduitRepository;
use App\Repository\Stock\MouvementStockRepository;
use App\Service\StockManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stock')]
class StockController extends AbstractController
{
    public function __construct(
        private StockManager $stockManager,
        private ProduitRepository $produitRepository,
        private MouvementStockRepository $mouvementStockRepository
    ) {
    }

    #[Route('/', name: 'app_stock_index')]
    public function index(): Response
    {
        $produits = $this->produitRepository->findAll();
        $stocksData = [];

        foreach ($produits as $produit) {
            $stockActuel = $this->stockManager->getStockActuel($produit);
            $stockMinimum = $produit->getStockMinimum();
            $datePeremption = $produit->getDatePeremption();
            
            // Déterminer le statut du stock
            if ($stockActuel <= 0) {
                $statut = 'rupture';
                $badge = 'danger';
            } elseif ($stockActuel < $stockMinimum) {
                $statut = 'alerte';
                $badge = 'warning';
            } else {
                $statut = 'ok';
                $badge = 'success';
            }

            // Déterminer le statut de péremption
            $statutPeremption = null;
            $badgePeremption = 'secondary';
            $joursRestants = null;
            
            if ($datePeremption) {
                $aujourdhui = new \DateTime();
                if ($datePeremption < $aujourdhui) {
                    $statutPeremption = 'perime';
                    $badgePeremption = 'danger';
                    $interval = $aujourdhui->diff($datePeremption);
                    $joursRestants = -$interval->days; // Négatif car périmé
                } else {
                    $interval = $aujourdhui->diff($datePeremption);
                    $joursRestants = $interval->days;
                    
                    if ($joursRestants <= 30) {
                        $statutPeremption = 'proche_peremption';
                        $badgePeremption = 'warning';
                    } else {
                        $statutPeremption = 'ok';
                        $badgePeremption = 'success';
                    }
                }
            }

            $stocksData[] = [
                'produit' => $produit,
                'stockActuel' => $stockActuel,
                'stockMinimum' => $stockMinimum,
                'statut' => $statut,
                'badge' => $badge,
                'datePeremption' => $datePeremption,
                'statutPeremption' => $statutPeremption,
                'badgePeremption' => $badgePeremption,
                'joursRestants' => $joursRestants,
            ];
        }

        return $this->render('stock/index.html.twig', [
            'stocksData' => $stocksData,
        ]);
    }

    #[Route('/dashboard', name: 'app_stock_dashboard')]
    public function dashboard(): Response
    {
        $produitsEnRupture = $this->stockManager->getProduitsEnRupture();
        $produitsACommander = $this->stockManager->getProduitsACommander();
        $valeurStock = $this->stockManager->calculerValeurStock();
        $mouvementsRecents = $this->mouvementStockRepository->findRecent(10);
        $produitsPerimes = $this->stockManager->getProduitsPerimes();
        $produitsProchesPeremption = $this->stockManager->getProduitsProchesPeremption();

        return $this->render('stock/dashboard.html.twig', [
            'produitsEnRupture' => $produitsEnRupture,
            'produitsACommander' => $produitsACommander,
            'valeurStock' => $valeurStock,
            'mouvementsRecents' => $mouvementsRecents,
            'produitsPerimes' => $produitsPerimes,
            'produitsProchesPeremption' => $produitsProchesPeremption,
        ]);
    }

    #[Route('/mouvements', name: 'app_stock_mouvements')]
    public function mouvements(Request $request): Response
    {
        $mouvements = $this->mouvementStockRepository->findBy(
            [],
            ['createdAt' => 'DESC'],
            50
        );

        return $this->render('stock/mouvements.html.twig', [
            'mouvements' => $mouvements,
        ]);
    }

    #[Route('/entree', name: 'app_stock_entree')]
    public function entree(Request $request): Response
    {
        $mouvement = new MouvementStock();
        $mouvement->setType(TypeMouvement::ENTREE);
        
        $form = $this->createForm(MouvementStockType::class, $mouvement);
        $form->remove('type'); // On fixe le type à ENTREE
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->stockManager->ajouterEntree(
                    $mouvement->getProduit(),
                    $mouvement->getQuantite(),
                    $this->getUser(),
                    $mouvement->getMotif(),
                    $mouvement->getReference()
                );

                $this->addFlash('success', 'Entrée de stock enregistrée avec succès');
                return $this->redirectToRoute('app_stock_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('stock/entree.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/sortie', name: 'app_stock_sortie')]
    public function sortie(Request $request): Response
    {
        $mouvement = new MouvementStock();
        $mouvement->setType(TypeMouvement::SORTIE);
        
        $form = $this->createForm(MouvementStockType::class, $mouvement);
        $form->remove('type'); // On fixe le type à SORTIE
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->stockManager->ajouterSortie(
                    $mouvement->getProduit(),
                    $mouvement->getQuantite(),
                    $this->getUser(),
                    $mouvement->getMotif(),
                    $mouvement->getReference()
                );

                $this->addFlash('success', 'Sortie de stock enregistrée avec succès');
                return $this->redirectToRoute('app_stock_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('stock/sortie.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ajustement', name: 'app_stock_ajustement')]
    public function ajustement(Request $request): Response
    {
        $mouvement = new MouvementStock();
        $mouvement->setType(TypeMouvement::AJUSTEMENT);
        
        $form = $this->createForm(MouvementStockType::class, $mouvement);
        $form->remove('type'); // On fixe le type à AJUSTEMENT
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->stockManager->ajusterStock(
                    $mouvement->getProduit(),
                    $mouvement->getQuantite(), // Ici quantité = nouveau stock
                    $this->getUser(),
                    $mouvement->getMotif()
                );

                $this->addFlash('success', 'Ajustement de stock effectué avec succès');
                return $this->redirectToRoute('app_stock_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('stock/ajustement.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produit/{id}', name: 'app_stock_produit_historique')]
    public function historiqueProduit(Produit $produit): Response
    {
        $mouvements = $this->mouvementStockRepository->findByProduit($produit);
        $stockActuel = $this->stockManager->getStockActuel($produit);

        return $this->render('stock/historique_produit.html.twig', [
            'produit' => $produit,
            'mouvements' => $mouvements,
            'stockActuel' => $stockActuel,
        ]);
    }
}
