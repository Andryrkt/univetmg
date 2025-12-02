<?php

namespace App\Service;

use App\Entity\Produit\Produit;
use App\Entity\Stock\MouvementStock;
use App\Entity\User;
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
    public function getProduitsEnRupture(): array
    {
        $produits = $this->produitRepository->findAll();
        $produitsEnRupture = [];

        foreach ($produits as $produit) {
            $stockActuel = $this->getStockActuel($produit);
            if ($stockActuel <= 0) {
                $produitsEnRupture[] = $produit;
            }
        }

        return $produitsEnRupture;
    }

    /**
     * Récupère les produits à commander (stock < stock minimum)
     *
     * @return array<array{produit: Produit, stockActuel: float, stockMinimum: float}>
     */
    public function getProduitsACommander(): array
    {
        $produits = $this->produitRepository->findAll();
        $produitsACommander = [];

        foreach ($produits as $produit) {
            $stockActuel = $this->getStockActuel($produit);
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
     * Calcule la valeur totale du stock
     */
    public function calculerValeurStock(): array
    {
        $produits = $this->produitRepository->findAll();
        $valeurTotale = 0;
        $details = [];

        foreach ($produits as $produit) {
            $stockActuel = $this->getStockActuel($produit);
            $prixAchat = $produit->getPrixAchat() ?? 0;
            $valeurProduit = $stockActuel * $prixAchat;
            
            $valeurTotale += $valeurProduit;
            
            if ($stockActuel > 0) {
                $details[] = [
                    'produit' => $produit,
                    'quantite' => $stockActuel,
                    'prixUnitaire' => $prixAchat,
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
