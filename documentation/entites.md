# Documentation des Entités

Ce document décrit les entités du projet, leurs propriétés et leurs relations, ainsi qu'un guide pour l'initialisation des données.

## Guide de Démarrage

Pour initialiser correctement la base de données, il est important de respecter un ordre logique de création des entités afin de satisfaire les dépendances.

### Ordre de Remplissage Recommandé

1.  **Unités (`Unite\Unite`)** : Commencez par définir les unités de base (ex: Kilogramme, Litre, Pièce). Elles sont utilisées partout.
2.  **Catégories (`Produit\Categorie`)** et **Fournisseurs (`Admin\Fournisseur`)** : Créez ensuite les catégories de produits et les fournisseurs. Ce sont des référentiels nécessaires pour créer un produit.
3.  **Produits (`Produit\Produit`)** : Une fois les unités, catégories et fournisseurs existants, vous pouvez créer les produits.
4.  **Conditionnements (`Unite\Conditionnement`)** et **Conversions (`Unite\ConversionStandard`)** : Enfin, définissez les conditionnements spécifiques (ex: Pack de 6) et les règles de conversion si nécessaire.

---

## Détails sur la Gestion des Unités

La gestion des unités est cruciale pour le stock. Voici comment utiliser les différentes entités liées aux unités.

### 1. Unite (`Unite\Unite`)
C'est l'unité de base.
-   **Nom** : Le nom complet (ex: "Kilogramme").
-   **Symbole** : L'abréviation affichée (ex: "kg").

### 2. Conditionnement (`Unite\Conditionnement`)
Permet de gérer des regroupements de produits.
-   **Produit** : Le produit concerné.
-   **Unite** : L'unité du conditionnement (ex: "Carton").
-   **Quantite** : Combien d'unités de base contient ce conditionnement.
    -   *Exemple* : Si le produit est "Eau minérale" (Unité de base = Litre), un conditionnement "Pack de 6x1.5L" aura une quantité de 9 (6 * 1.5).
    -   *Exemple 2* : Si le produit est "Canette 33cl" (Unité de base = Pièce), un conditionnement "Pack de 24" aura une quantité de 24.

### 3. ConversionStandard (`Unite\ConversionStandard`)
Permet de convertir automatiquement les stocks entre deux unités compatibles.
-   **UniteOrigine** : L'unité de départ (ex: "Litre").
-   **UniteCible** : L'unité d'arrivée (ex: "Millilitre").
-   **Facteur** : Le multiplicateur.
    -   *Formule* : `Quantité Cible = Quantité Origine * Facteur`
    -   *Exemple* : 1 Litre = 1000 Millilitres. Facteur = 1000.

---

## Table des matières

- [User](#user)
- [Admin\Fournisseur](#adminfournisseur)
- [Produit\Categorie](#produitcategorie)
- [Produit\Produit](#produitproduit)
- [Unite\Conditionnement](#uniteconditionnement)
- [Unite\ConversionStandard](#uniteconversionstandard)
- [Unite\Unite](#uniteunite)

---

## User

Représente un utilisateur de l'application.

**Classe** : `App\Entity\User`
**Table** : `users`

### Propriétés

| Propriété | Type | Description |
| :--- | :--- | :--- |
| `id` | `int` | Identifiant unique (clé primaire). |
| `email` | `string` | Adresse email de l'utilisateur (unique). |
| `roles` | `array` | Rôles de l'utilisateur (ex: `ROLE_USER`, `ROLE_ADMIN`). |
| `password` | `string` | Mot de passe haché. |
| `firstName` | `string` | Prénom de l'utilisateur. |
| `lastName` | `string` | Nom de l'utilisateur. |
| `isVerified` | `bool` | Indique si le compte est vérifié. |
| `createdAt` | `datetime` | Date de création du compte. |

---

## Admin\Fournisseur

Représente un fournisseur de produits.

**Classe** : `App\Entity\Admin\Fournisseur`

### Propriétés

| Propriété | Type | Description |
| :--- | :--- | :--- |
| `id` | `int` | Identifiant unique. |
| `nom` | `string` | Nom du fournisseur. |
| `telephone` | `string` | Numéro de téléphone. |
| `adresse` | `string` | Adresse physique. |
| `email` | `string` | Adresse email. |

### Relations

- **OneToMany** vers `Produit` : Un fournisseur peut fournir plusieurs produits.

---

## Produit\Categorie

Représente une catégorie de produits (structure hiérarchique).

**Classe** : `App\Entity\Produit\Categorie`

### Propriétés

| Propriété | Type | Description |
| :--- | :--- | :--- |
| `id` | `int` | Identifiant unique. |
| `nom` | `string` | Nom de la catégorie. |

### Relations

- **ManyToOne** vers `Categorie` (parent) : Catégorie parente.
- **OneToMany** vers `Categorie` (enfant) : Sous-catégories.
- **OneToMany** vers `Produit` : Produits appartenant à cette catégorie.

---

## Produit\Produit

Représente un produit en stock.

**Classe** : `App\Entity\Produit\Produit`

### Propriétés

| Propriété | Type | Description |
| :--- | :--- | :--- |
| `id` | `int` | Identifiant unique. |
| `nom` | `string` | Nom du produit. |
| `description` | `string` | Description détaillée. |
| `code` | `string` | Code produit (ex: code barre). |
| `stockInitial` | `float` | Stock initial. |
| `stockMinimum` | `float` | Seuil de stock minimum. |
| `prixAchat` | `float` | Prix d'achat unitaire. |
| `prixVente` | `float` | Prix de vente unitaire. |
| `datePeremption` | `datetime` | Date de péremption. |

### Relations

- **OneToMany** vers `Conditionnement` : Différents conditionnements du produit.
- **ManyToOne** vers `Unite` (`uniteDeBase`) : Unité de base du produit.
- **ManyToOne** vers `Categorie` : Catégorie du produit.
- **ManyToOne** vers `Fournisseur` : Fournisseur du produit.

---

## Unite\Conditionnement

Représente un conditionnement spécifique pour un produit (ex: Boîte de 10).

**Classe** : `App\Entity\Unite\Conditionnement`
**Table** : `conditionnement`

### Propriétés

| Propriété | Type | Description |
| :--- | :--- | :--- |
| `id` | `int` | Identifiant unique. |
| `quantite` | `float` | Quantité dans ce conditionnement. |

### Relations

- **ManyToOne** vers `Produit` : Le produit concerné.
- **ManyToOne** vers `Unite` : L'unité de ce conditionnement.

---

## Unite\ConversionStandard

Définit les taux de conversion entre unités.

**Classe** : `App\Entity\Unite\ConversionStandard`
**Table** : `conversion_standard`

### Propriétés

| Propriété | Type | Description |
| :--- | :--- | :--- |
| `id` | `int` | Identifiant unique. |
| `facteur` | `float` | Facteur de conversion. |

### Relations

- **ManyToOne** vers `Unite` (`uniteOrigine`) : Unité de départ.
- **ManyToOne** vers `Unite` (`uniteCible`) : Unité d'arrivée.

---

## Unite\Unite

Représente une unité de mesure (ex: kg, litre, pièce).

**Classe** : `App\Entity\Unite\Unite`

### Propriétés

| Propriété | Type | Description |
| :--- | :--- | :--- |
| `id` | `int` | Identifiant unique. |
| `nom` | `string` | Nom de l'unité. |
| `symbole` | `string` | Symbole (ex: kg, L). |

### Relations

- **OneToMany** vers `Produit` : Produits utilisant cette unité comme base.
