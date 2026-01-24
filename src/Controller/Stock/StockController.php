<?php

namespace App\Controller\Stock;

use App\Entity\Produit\Produit;
use App\Entity\Stock\Lot;
use App\Entity\Stock\MouvementStock;
use App\Enum\TypeMouvement;
use App\Form\Stock\EntreeType;
use App\Form\Stock\MouvementStockType;
use App\Repository\Produit\ProduitRepository;
use App\Repository\Stock\MouvementStockRepository;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
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
        private MouvementStockRepository $mouvementStockRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'app_stock_index')]
    public function index(): Response
    {
        $produits = $this->produitRepository->findAll();
        $stocksData = [];

        foreach ($produits as $produit) {
            $stockActuel = $produit->getQuantiteEnStock();
            $stockMinimum = $produit->getStockMinimum();
            
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

            // Péremption (on prend la date la plus proche)
            $lotPlusProchePeremption = null;
            $aujourdhui = new \DateTime();
            $joursRestantsMin = null;

            foreach($produit->getLots() as $lot) {
                if ($lot->getDatePeremption()) {
                    $interval = $aujourdhui->diff($lot->getDatePeremption());
                    $jours = $interval->invert ? -$interval->days : $interval->days;
                    if ($joursRestantsMin === null || $jours < $joursRestantsMin) {
                        $joursRestantsMin = $jours;
                        $lotPlusProchePeremption = $lot;
                    }
                }
            }

            $statutPeremption = null;
            $badgePeremption = 'secondary';

            if ($lotPlusProchePeremption) {
                if ($joursRestantsMin < 0) {
                    $statutPeremption = 'perime';
                    $badgePeremption = 'danger';
                } elseif ($joursRestantsMin <= 30) {
                    $statutPeremption = 'proche_peremption';
                    $badgePeremption = 'warning';
                } else {
                    $statutPeremption = 'ok';
                    $badgePeremption = 'success';
                }
            }


            $stocksData[] = [
                'produit' => $produit,
                'stockActuel' => $stockActuel,
                'stockMinimum' => $stockMinimum,
                'statut' => $statut,
                'badge' => $badge,
                'datePeremption' => $lotPlusProchePeremption?->getDatePeremption(),
                'statutPeremption' => $statutPeremption,
                'badgePeremption' => $badgePeremption,
                'joursRestants' => $joursRestantsMin,
            ];
        }

        return $this->render('stock/index.html.twig', [
            'stocksData' => $stocksData,
        ]);
    }

    #[Route('/dashboard', name: 'app_stock_dashboard')]
    public function dashboard(): Response
    {
        // This method will likely need refactoring as well since StockManager is probably outdated
        // For now, we leave it as is to avoid breaking too much at once.
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
        $form = $this->createForm(EntreeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $produit = $data['produit'];
            
            // Create the new Lot
            $lot = new Lot();
            $lot->setProduit($produit);
            $lot->setQuantite($data['quantite']);
            $lot->setPrixAchat($data['prixAchat']);
            $lot->setDatePeremption($data['datePeremption']);
            $lot->setNumeroLot($data['numeroLot']);
            
            $this->entityManager->persist($lot);

            // Create a MouvementStock for traceability
            $mouvement = new MouvementStock();
            $mouvement->setLot($lot);
            $mouvement->setType(TypeMouvement::ENTREE);
            $mouvement->setQuantite($lot->getQuantite());
            $mouvement->setUser($this->getUser());
            $mouvement->setMotif('Entrée de stock / Réapprovisionnement');
            // For a new lot, stockAvant is 0.
            $mouvement->setStockAvant(0);
            $mouvement->setStockApres($lot->getQuantite());
            
            $this->entityManager->persist($mouvement);
            
            try {
                $this->entityManager->flush();
                $this->addFlash('success', 'Entrée de stock enregistrée avec succès (nouveau lot créé).');
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
        $form = $this->createForm(\App\Form\Stock\SortieType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var Lot $lot */
            $lot = $data['lot'];
            $quantiteSortie = $data['quantite'];

            if ($lot->getQuantite() < $quantiteSortie) {
                $this->addFlash('error', sprintf('Le stock actuel du lot (%s) est insuffisant pour cette sortie (%s).', $lot->getQuantite(), $quantiteSortie));
            } else {
                $stockAvant = $lot->getQuantite();
                $lot->setQuantite($stockAvant - $quantiteSortie);

                $mouvement = new MouvementStock();
                $mouvement->setLot($lot);
                $mouvement->setType(TypeMouvement::SORTIE);
                $mouvement->setQuantite($quantiteSortie);
                $mouvement->setUser($this->getUser());
                $mouvement->setMotif($data['motif'] ?? 'Sortie manuelle de stock');
                $mouvement->setStockAvant($stockAvant);
                $mouvement->setStockApres($lot->getQuantite());

                $this->entityManager->persist($mouvement);
                
                try {
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Sortie de stock enregistrée avec succès.');
                    return $this->redirectToRoute('app_stock_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
                }
            }
        }

        return $this->render('stock/sortie.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ajustement', name: 'app_stock_ajustement')]
    public function ajustement(Request $request): Response
    {
        $form = $this->createForm(\App\Form\Stock\AjustementType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var Lot $lot */
            $lot = $data['lot'];
            $nouvelleQuantite = $data['nouvelleQuantite'];
            $stockAvant = $lot->getQuantite();
            $difference = $nouvelleQuantite - $stockAvant;

            if ($difference != 0) {
                $lot->setQuantite($nouvelleQuantite);

                $mouvement = new MouvementStock();
                $mouvement->setLot($lot);
                $mouvement->setType(TypeMouvement::AJUSTEMENT);
                $mouvement->setQuantite(abs($difference));
                $mouvement->setUser($this->getUser());
                $mouvement->setMotif($data['motif'] ?? 'Ajustement manuel de stock');
                $mouvement->setStockAvant($stockAvant);
                $mouvement->setStockApres($nouvelleQuantite);

                $this->entityManager->persist($mouvement);
                
                try {
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Ajustement de stock enregistré avec succès.');
                    return $this->redirectToRoute('app_stock_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
                }
            } else {
                $this->addFlash('info', 'Aucun changement de quantité détecté.');
                return $this->redirectToRoute('app_stock_index');
            }
        }

        return $this->render('stock/ajustement.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produit/{id}', name: 'app_stock_produit_historique')]
    public function historiqueProduit(Produit $produit): Response
    {
        // This needs to be adapted to show movements per lot.
        $mouvements = []; // This needs to be re-fetched per lot.
        $stockActuel = $produit->getQuantiteEnStock();

        return $this->render('stock/historique_produit.html.twig', [
            'produit' => $produit,
            'mouvements' => $mouvements,
            'stockActuel' => $stockActuel,
        ]);
    }
}
