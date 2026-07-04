export interface Unite {
    id: number;
    nom: string;
    symbole: string | null;
    nbProduits: number;
}

export interface UniteDetail extends Unite {
    conditionnements: Array<{
        id: number;
        quantite: number;
        produit: { id: number; nom: string } | null;
    }>;
    conversions: Array<{
        id: number;
        uniteSource: string | null;
        uniteCible: string | null;
        facteur: number | null;
    }>;
}

export interface CategorieRef {
    id: number;
    nom: string;
}

export interface Categorie {
    id: number;
    nom: string;
    abbreviation: string | null;
    parent: CategorieRef | null;
    enfants: CategorieRef[];
}

export interface CategorieDetail extends Categorie {
    path: CategorieRef[];
    nbProduits: number;
}

export interface FournisseurRef {
    id: number;
    nom: string;
}

export interface Fournisseur extends FournisseurRef {
    telephone: string | null;
    adresse: string | null;
    email: string | null;
}

export interface FournisseurDetail extends Fournisseur {
    nbProduits: number;
}

export interface FournisseurPayload {
    nom: string;
    telephone: string;
    adresse: string;
    email: string;
}

export type Role = "ROLE_USER" | "ROLE_ADMIN";

export interface User {
    id: number;
    email: string;
    firstName: string | null;
    lastName: string | null;
    roles: Role[];
    isVerified: boolean;
}

export interface UserPayload {
    email: string;
    firstName: string;
    lastName: string;
    roles: Role[];
    isVerified: boolean;
    plainPassword: string;
}

export interface Produit {
    id: number;
    nom: string;
    description: string | null;
    code: string;
    stockMinimum: number;
    prixVente: number | null;
    quantiteEnStock: number;
    uniteDeBase: { id: number; nom: string; symbole: string | null } | null;
    categorie: CategorieRef | null;
    fournisseur: FournisseurRef | null;
}

export interface ProduitPayload {
    nom: string;
    description: string;
    stockMinimum: string;
    prixVente: string;
    uniteDeBaseId: string;
    categorieId: string;
    fournisseurId: string;
}

export interface UnitePayload {
    nom: string;
    symbole: string;
}

export interface CategoriePayload {
    nom: string;
    abbreviation: string;
    parentId: string;
}

export interface TypeClientRef {
    id: number;
    nom: string;
}

export interface TypeClient extends TypeClientRef {
    tauxRemise: string;
    description: string | null;
    actif: boolean;
}

export interface TypeClientDetail extends TypeClient {
    nbClients: number;
}

export interface TypeClientPayload {
    nom: string;
    tauxRemise: string;
    description: string;
    actif: boolean;
}

export interface Client {
    id: number;
    nom: string;
    telephone: string | null;
    adresse: string | null;
    typeClient: TypeClientRef | null;
}

export interface ClientDetail extends Client {
    nbVentes: number;
}

export interface ClientPayload {
    nom: string;
    telephone: string;
    adresse: string;
    typeClientId: string;
}

export interface ProduitRef {
    id: number;
    nom: string;
    code?: string;
}

export type StatutStock = "rupture" | "alerte" | "ok";
export type StatutPeremption = "perime" | "proche_peremption" | "ok" | null;

export interface StockItem {
    produit: ProduitRef;
    stockActuel: number;
    stockMinimum: number;
    statut: StatutStock;
    datePeremption: string | null;
    statutPeremption: StatutPeremption;
    joursRestants: number | null;
}

export type TypeMouvement = "entree" | "sortie" | "ajustement" | "retour";

export interface MouvementStock {
    id: number;
    type: TypeMouvement;
    typeLabel: string;
    quantite: number;
    motif: string | null;
    reference: string | null;
    stockAvant: number;
    stockApres: number;
    createdAt: string;
    produit: { id: number; nom: string } | null;
    lotId: number | null;
    user: { id: number; email: string } | null;
}

export interface Lot {
    id: number;
    numeroLot: string | null;
    quantite: number;
    datePeremption: string | null;
}

export interface ProduitUniteOption {
    id: number;
    nom: string;
    symbole: string | null;
    facteur: number;
    isBase: boolean;
    prix?: number | null;
}

export interface StockDashboard {
    produitsEnRupture: ProduitRef[];
    produitsACommander: Array<{ produit: ProduitRef; stockActuel: number; stockMinimum: number; manquant: number }>;
    valeurTotale: number;
    valeurDetails: Array<{ produit: ProduitRef; valeurTotale: number }>;
    mouvementsRecents: MouvementStock[];
    produitsPerimes: Array<{ produit: ProduitRef; lotId: number; datePeremption: string; joursDepuisPeremption: number }>;
    produitsProchesPeremption: Array<{ produit: ProduitRef; lotId: number; datePeremption: string; joursRestants: number }>;
}

export interface ProduitHistorique {
    produit: ProduitRef;
    stockActuel: number;
    mouvements: MouvementStock[];
}

export interface EntreePayload {
    produitId: string;
    quantite: string;
    numeroLot: string;
    datePeremption: string;
    prixAchat: string;
}

export interface SortiePayload {
    lotId: string;
    quantite: string;
    motif: string;
}

export interface AjustementPayload {
    lotId: string;
    nouvelleQuantite: string;
    motif: string;
}

export interface Promotion {
    id: number;
    nom: string;
    dateDebut: string;
    dateFin: string;
    tauxRemise: string | null;
    montantRemise: string | null;
    actif: boolean;
    isCurrentlyActive: boolean;
    isExpired: boolean;
    nbProduits: number;
}

export interface PromotionDetail extends Promotion {
    produits: ProduitRef[];
}

export interface PromotionPayload {
    nom: string;
    dateDebut: string;
    dateFin: string;
    tauxRemise: string;
    montantRemise: string;
    actif: boolean;
    produitIds: number[];
}

export type StatutVente = "brouillon" | "validee" | "annulee";

export interface VenteLigne {
    id: number;
    produit: { id: number; nom: string };
    unite: { id: number; nom: string; symbole: string | null } | null;
    facteurConversion: number;
    quantite: number;
    prixUnitaire: string;
    prixCatalogue: string | null;
    tauxRemise: number | null;
    montantRemise: string | null;
    typeRemise: string | null;
    sousTotal: string;
}

export interface Vente {
    id: number;
    numeroFacture: string;
    dateVente: string;
    client: { id: number; nom: string } | null;
    total: string;
    statut: StatutVente;
    statutLabel: string;
    nbLignes: number;
}

export interface VenteDetail extends Vente {
    user: { id: number; email: string } | null;
    lignes: VenteLigne[];
}

export interface VenteLignePayload {
    produitId: string;
    uniteId: string;
    quantite: string;
}

export interface VentePayload {
    dateVente: string;
    clientId: string;
    statut: "brouillon" | "validee";
    lignes: VenteLignePayload[];
}

export interface PricingPreview {
    prixCatalogue: number;
    tauxRemise: number;
    montantRemise: number;
    prixFinal: number;
    typeRemise: string | null;
    facteurConversion: number;
}

export type FieldErrors = Record<string, string>;

export interface ApiErrorBody {
    errors?: FieldErrors;
    message?: string;
}
