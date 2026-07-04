<?php

namespace App\Service;

use App\Entity\Produit\Produit;
use App\Entity\Stock\MouvementStock;
use App\Entity\User;
use App\Entity\Vente\Vente;
use App\Enum\TypeMouvement;
use App\Repository\Produit\ProduitRepository;
use App\Repository\Stock\MouvementStockRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MouvementStockRepository $mouvementStockRepository,
        private ProduitRepository $produitRepository
    ) {
    }

    /**
     * Enregistre une entrée de stock
     */
    public function ajouterEntree(
        Produit $produit,
        float $quantite,
        User $user,
        ?string $motif = null,
        ?string $reference = null
    ): MouvementStock {
        return $this->creerMouvement(
            $produit,
            TypeMouvement::ENTREE,
            $quantite,
            $user,
            $motif,
            $reference
        );
    }

    /**
     * Enregistre une sortie de stock
     */
    public function ajouterSortie(
        Produit $produit,
        float $quantite,
        User $user,
        ?string $motif = null,
        ?string $reference = null
    ): MouvementStock {
        $stockActuel = $this->getStockActuel($produit);
        
        if ($stockActuel < $quantite) {
            throw new \Exception(sprintf(
                'Stock insuffisant pour le produit "%s". Stock actuel: %.2f, Quantité demandée: %.2f',
                $produit->getNom(),
                $stockActuel,
                $quantite
            ));
        }

        return $this->creerMouvement(
            $produit,
            TypeMouvement::SORTIE,
            $quantite,
            $user,
            $motif,
            $reference
        );
    }

    /**
     * Ajuste le stock (pour inventaire)
     */
    public function ajusterStock(
        Produit $produit,
        float $nouveauStock,
        User $user,
        ?string $motif = null
    ): MouvementStock {
        $stockActuel = $this->getStockActuel($produit);
        $difference = $nouveauStock - $stockActuel;
        
        if ($difference == 0) {
            throw new \Exception('Le nouveau stock est identique au stock actuel');
        }

        $mouvement = new MouvementStock();
        $mouvement->setType(TypeMouvement::AJUSTEMENT);
        $mouvement->setProduit($produit);
        $mouvement->setQuantite(abs($difference));
        $mouvement->setStockAvant($stockActuel);
        $mouvement->setStockApres($nouveauStock);
        $mouvement->setUser($user);
        $mouvement->setMotif($motif ?? sprintf(
            'Ajustement de stock: %s%.2f',
            $difference > 0 ? '+' : '',
            $difference
        ));

        $this->entityManager->persist($mouvement);
        $this->entityManager->flush();

        return $mouvement;
    }

    /**
     * Enregistre un retour de produit
     */
    public function ajouterRetour(
        Produit $produit,
        float $quantite,
        User $user,
        ?string $motif = null,
        ?string $reference = null
    ): MouvementStock {
        return $this->creerMouvement(
            $produit,
            TypeMouvement::RETOUR,
            $quantite,
            $user,
            $motif,
            $reference
        );
    }

    /**
     * Obtient le stock actuel d'un produit
     */
    public function getStockActuel(Produit $produit): float
    {
        return $this->mouvementStockRepository->getStockActuel($produit);
    }

    /**
     * Récupère les produits en rupture de stock
     *
     * @return array<Produit>
     */
    /**
     * Récupère les produits en rupture de stock (total stock des lots <= 0)
     *
     * @return array<Produit>
     */
    public function getProduitsEnRupture(): array
    {
        $produits = $this->produitRepository->findAll();
        $produitsEnRupture = [];

        foreach ($produits as $produit) {
            if ($produit->getQuantiteEnStock() <= 0) {
                $produitsEnRupture[] = $produit;
            }
        }

        return $produitsEnRupture;
    }

    /**
     * Récupère les produits à commander (stock < stock minimum)
     *
     * @return array<array{produit: Produit, stockActuel: float, stockMinimum: float, manquant: float}>
     */
    public function getProduitsACommander(): array
    {
        $produits = $this->produitRepository->findAll();
        $produitsACommander = [];

        foreach ($produits as $produit) {
            $stockActuel = $produit->getQuantiteEnStock();
            if ($stockActuel < $produit->getStockMinimum()) {
                $produitsACommander[] = [
                    'produit' => $produit,
                    'stockActuel' => $stockActuel,
                    'stockMinimum' => $produit->getStockMinimum(),
                    'manquant' => $produit->getStockMinimum() - $stockActuel
                ];
            }
        }

        return $produitsACommander;
    }

    /**
     * Calcule la valeur totale du stock basée sur le prix d'achat des lots
     */
    public function calculerValeurStock(): array
    {
        $produits = $this->produitRepository->findAll();
        $valeurTotale = 0;
        $details = [];

        foreach ($produits as $produit) {
            $valeurProduit = 0;
            foreach ($produit->getLots() as $lot) {
                $valeurProduit += $lot->getQuantite() * ($lot->getPrixAchat() ?? 0);
            }
            
            $valeurTotale += $valeurProduit;
            
            if ($valeurProduit > 0) {
                $details[] = [
                    'produit' => $produit,
                    'valeurTotale' => $valeurProduit
                ];
            }
        }

        return [
            'valeurTotale' => $valeurTotale,
            'details' => $details
        ];
    }

    /**
     * Récupère les lots périmés
     *
     * @return array<array{produit: Produit, datePeremption: \DateTime, joursDepuisPeremption: int}>
     */
    public function getProduitsPerimes(): array
    {
        $produits = $this->produitRepository->findAll();
        $perimes = [];
        $aujourdhui = new \DateTime();

        foreach ($produits as $produit) {
            foreach ($produit->getLots() as $lot) {
                $datePeremption = $lot->getDatePeremption();
                if ($datePeremption && $datePeremption < $aujourdhui && $lot->getQuantite() > 0) {
                    $interval = $aujourdhui->diff($datePeremption);
                    $perimes[] = [
                        'produit' => $produit,
                        'lot' => $lot,
                        'datePeremption' => $datePeremption,
                        'joursDepuisPeremption' => $interval->days
                    ];
                }
            }
        }

        return $perimes;
    }

    /**
     * Récupère les lots proches de la péremption
     */
    public function getProduitsProchesPeremption(int $joursAvant = 30): array
    {
        $produits = $this->produitRepository->findAll();
        $proches = [];
        $aujourdhui = new \DateTime();
        $dateLimit = (clone $aujourdhui)->modify("+{$joursAvant} days");

        foreach ($produits as $produit) {
            foreach ($produit->getLots() as $lot) {
                $datePeremption = $lot->getDatePeremption();
                if ($datePeremption && $datePeremption > $aujourdhui && $datePeremption <= $dateLimit && $lot->getQuantite() > 0) {
                    $interval = $aujourdhui->diff($datePeremption);
                    $proches[] = [
                        'produit' => $produit,
                        'lot' => $lot,
                        'datePeremption' => $datePeremption,
                        'joursRestants' => $interval->days
                    ];
                }
            }
        }

        return $proches;
    }

    /**
     * Traite les mouvements de stock pour une vente validée
     */
    /**
     * Traite les mouvements de stock pour une vente validée
     */
    public function processVente(Vente $vente): void
    {
        foreach ($vente->getLigneVentes() as $ligne) {
            // Calculate real quantity to deduct based on conversion factor
            $quantiteReelle = $ligne->getQuantite() * ($ligne->getFacteurConversion() ?? 1.0);
            
            $this->ajouterSortie(
                $ligne->getProduit(),
                $quantiteReelle,
                $vente->getUser(),
                sprintf('Vente - Facture N°%s (Qté: %s %s)', $vente->getNumeroFacture(), $ligne->getQuantite(), $ligne->getUnite() ? $ligne->getUnite()->getNom() : ''),
                $vente->getNumeroFacture()
            );
        }
    }

    /**
     * Annule une vente validée (remet le stock)
     */
    public function revertVente(Vente $vente): void
    {
        foreach ($vente->getLigneVentes() as $ligne) {
             // Calculate real quantity to return based on conversion factor
            $quantiteReelle = $ligne->getQuantite() * ($ligne->getFacteurConversion() ?? 1.0);

            $this->ajouterRetour(
                $ligne->getProduit(),
                $quantiteReelle,
                $vente->getUser(), // Ou l'utilisateur courant si passé en paramètre, mais ici on utilise le vendeur original ou on devrait passer l'user qui annule.
                sprintf('Annulation Vente - Facture N°%s', $vente->getNumeroFacture()),
                $vente->getNumeroFacture()
            );
        }
    }

    /**
     * Méthode privée pour créer un mouvement de stock
     */
    private function creerMouvement(
        Produit $produit,
        TypeMouvement $type,
        float $quantite,
        User $user,
        ?string $motif,
        ?string $reference
    ): MouvementStock {
        $stockAvant = $this->getStockActuel($produit);
        
        // Calcul du stock après selon le type de mouvement
        $stockApres = match($type) {
            TypeMouvement::ENTREE, TypeMouvement::RETOUR => $stockAvant + $quantite,
            TypeMouvement::SORTIE => $stockAvant - $quantite,
            TypeMouvement::AJUSTEMENT => $quantite, // Pour ajustement, quantite = nouveau stock
        };

        $mouvement = new MouvementStock();
        $mouvement->setType($type);
        $mouvement->setProduit($produit);
        $mouvement->setQuantite($quantite);
        $mouvement->setStockAvant($stockAvant);
        $mouvement->setStockApres($stockApres);
        $mouvement->setUser($user);
        $mouvement->setMotif($motif);
        $mouvement->setReference($reference);

        $this->entityManager->persist($mouvement);
        $this->entityManager->flush();

        return $mouvement;
    }
}
