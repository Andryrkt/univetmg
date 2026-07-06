import { auth } from "./auth";
import type {
    AjustementPayload,
    ApiErrorBody,
    Categorie,
    CategorieDetail,
    CategoriePayload,
    CategorieRef,
    Client,
    ClientDetail,
    ClientPayload,
    ConversionStandard,
    ConversionStandardPayload,
    DocumentationIndex,
    DocumentationPage,
    EntreePayload,
    Fournisseur,
    FournisseurDetail,
    FournisseurPayload,
    Lot,
    MouvementStock,
    PricingPreview,
    Produit,
    ProduitHistorique,
    ProduitPayload,
    ProduitUniteOption,
    Promotion,
    PromotionDetail,
    PromotionPayload,
    RegisterPayload,
    SortiePayload,
    StockDashboard,
    StockItem,
    TypeClient,
    TypeClientDetail,
    TypeClientPayload,
    Unite,
    UniteDetail,
    UnitePayload,
    User,
    UserPayload,
    Vente,
    VenteDetail,
    VentePayload,
} from "../types";

const API_URL = import.meta.env.VITE_API_URL;

export class ApiError extends Error {
    status: number;
    body: ApiErrorBody | null;

    constructor(status: number, body: ApiErrorBody | null) {
        super(`Request failed: ${status}`);
        this.status = status;
        this.body = body;
    }
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
    const token = auth.getToken();

    const response = await fetch(`${API_URL}${path}`, {
        ...options,
        headers: {
            "Content-Type": "application/json",
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...options.headers,
        },
    });

    if (response.status === 401) {
        auth.clearToken();
    }

    if (!response.ok) {
        const body = await response.json().catch(() => null);
        throw new ApiError(response.status, body);
    }

    if (response.status === 204) {
        return null as T;
    }

    return response.json() as Promise<T>;
}

async function requestBlob(path: string): Promise<Blob> {
    const token = auth.getToken();

    const response = await fetch(`${API_URL}${path}`, {
        headers: token ? { Authorization: `Bearer ${token}` } : {},
    });

    if (response.status === 401) {
        auth.clearToken();
    }
    if (!response.ok) {
        throw new ApiError(response.status, null);
    }

    return response.blob();
}

export const api = {
    login: async (email: string, password: string): Promise<string> => {
        const { token } = await request<{ token: string }>(`/login_check`, {
            method: "POST",
            body: JSON.stringify({ email, password }),
        });
        auth.setToken(token);
        return token;
    },
    register: (data: RegisterPayload) => request<{ message: string }>(`/register`, { method: "POST", body: JSON.stringify(data) }),
    me: () => request<User>(`/me`),

    listDocumentation: () => request<DocumentationIndex>(`/documentation`),
    getDocumentation: (slug: string) => request<DocumentationPage>(`/documentation/${slug}`),

    listConversionStandards: () => request<ConversionStandard[]>(`/conversion-standards`),
    getConversionStandard: (id: string | number) => request<ConversionStandard>(`/conversion-standards/${id}`),
    createConversionStandard: (data: ConversionStandardPayload) =>
        request<ConversionStandard>(`/conversion-standards`, { method: "POST", body: JSON.stringify(data) }),
    updateConversionStandard: (id: string | number, data: ConversionStandardPayload) =>
        request<ConversionStandard>(`/conversion-standards/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    deleteConversionStandard: (id: string | number) => request<null>(`/conversion-standards/${id}`, { method: "DELETE" }),

    listProduits: (q?: string) => request<Produit[]>(`/produits${q ? `?q=${encodeURIComponent(q)}` : ""}`),
    getProduit: (id: string | number) => request<Produit>(`/produits/${id}`),
    createProduit: (data: ProduitPayload) => request<Produit>(`/produits`, { method: "POST", body: JSON.stringify(data) }),
    updateProduit: (id: string | number, data: ProduitPayload) =>
        request<Produit>(`/produits/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    deleteProduit: (id: string | number) => request<null>(`/produits/${id}`, { method: "DELETE" }),
    getProduitUnites: (id: string | number) => request<ProduitUniteOption[]>(`/produits/${id}/unites`),

    listUnites: () => request<Unite[]>(`/unites`),
    getUnite: (id: string | number) => request<UniteDetail>(`/unites/${id}`),
    createUnite: (data: UnitePayload) => request<Unite>(`/unites`, { method: "POST", body: JSON.stringify(data) }),
    updateUnite: (id: string | number, data: UnitePayload) =>
        request<Unite>(`/unites/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    deleteUnite: (id: string | number) => request<null>(`/unites/${id}`, { method: "DELETE" }),

    listCategories: () => request<Categorie[]>(`/categories`),
    getCategorie: (id: string | number) => request<CategorieDetail>(`/categories/${id}`),
    createCategorie: (data: CategoriePayload) => request<Categorie>(`/categories`, { method: "POST", body: JSON.stringify(data) }),
    updateCategorie: (id: string | number, data: CategoriePayload) =>
        request<Categorie>(`/categories/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    deleteCategorie: (id: string | number) => request<null>(`/categories/${id}`, { method: "DELETE" }),

    listFournisseurs: () => request<Fournisseur[]>(`/fournisseurs`),
    getFournisseur: (id: string | number) => request<FournisseurDetail>(`/fournisseurs/${id}`),
    createFournisseur: (data: FournisseurPayload) => request<Fournisseur>(`/fournisseurs`, { method: "POST", body: JSON.stringify(data) }),
    updateFournisseur: (id: string | number, data: FournisseurPayload) =>
        request<Fournisseur>(`/fournisseurs/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    deleteFournisseur: (id: string | number) => request<null>(`/fournisseurs/${id}`, { method: "DELETE" }),

    listUsers: () => request<User[]>(`/users`),
    getUser: (id: string | number) => request<User>(`/users/${id}`),
    createUser: (data: UserPayload) => request<User>(`/users`, { method: "POST", body: JSON.stringify(data) }),
    updateUser: (id: string | number, data: UserPayload) => request<User>(`/users/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    deleteUser: (id: string | number) => request<null>(`/users/${id}`, { method: "DELETE" }),

    listTypeClients: () => request<TypeClient[]>(`/type-clients`),
    getTypeClient: (id: string | number) => request<TypeClientDetail>(`/type-clients/${id}`),
    createTypeClient: (data: TypeClientPayload) => request<TypeClient>(`/type-clients`, { method: "POST", body: JSON.stringify(data) }),
    updateTypeClient: (id: string | number, data: TypeClientPayload) =>
        request<TypeClient>(`/type-clients/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    deleteTypeClient: (id: string | number) => request<null>(`/type-clients/${id}`, { method: "DELETE" }),

    listClients: () => request<Client[]>(`/clients`),
    getClient: (id: string | number) => request<ClientDetail>(`/clients/${id}`),
    createClient: (data: ClientPayload) => request<Client>(`/clients`, { method: "POST", body: JSON.stringify(data) }),
    updateClient: (id: string | number, data: ClientPayload) =>
        request<Client>(`/clients/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    deleteClient: (id: string | number) => request<null>(`/clients/${id}`, { method: "DELETE" }),

    listStock: () => request<StockItem[]>(`/stock`),
    getStockDashboard: () => request<StockDashboard>(`/stock/dashboard`),
    listMouvements: () => request<MouvementStock[]>(`/stock/mouvements`),
    listLots: (produitId?: string | number) => request<Lot[]>(`/stock/lots${produitId ? `?produitId=${produitId}` : ""}`),
    getHistoriqueProduit: (id: string | number) => request<ProduitHistorique>(`/stock/produits/${id}/historique`),
    stockEntree: (data: EntreePayload) => request<MouvementStock>(`/stock/entree`, { method: "POST", body: JSON.stringify(data) }),
    stockSortie: (data: SortiePayload) => request<MouvementStock>(`/stock/sortie`, { method: "POST", body: JSON.stringify(data) }),
    stockAjustement: (data: AjustementPayload) =>
        request<MouvementStock>(`/stock/ajustement`, { method: "POST", body: JSON.stringify(data) }),

    listPromotions: () => request<Promotion[]>(`/promotions`),
    getPromotion: (id: string | number) => request<PromotionDetail>(`/promotions/${id}`),
    createPromotion: (data: PromotionPayload) => request<Promotion>(`/promotions`, { method: "POST", body: JSON.stringify(data) }),
    updatePromotion: (id: string | number, data: PromotionPayload) =>
        request<Promotion>(`/promotions/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    deletePromotion: (id: string | number) => request<null>(`/promotions/${id}`, { method: "DELETE" }),

    listVentes: () => request<Vente[]>(`/ventes`),
    getVente: (id: string | number) => request<VenteDetail>(`/ventes/${id}`),
    createVente: (data: VentePayload) => request<VenteDetail>(`/ventes`, { method: "POST", body: JSON.stringify(data) }),
    updateVente: (id: string | number, data: VentePayload) =>
        request<VenteDetail>(`/ventes/${id}`, { method: "PUT", body: JSON.stringify(data) }),
    cancelVente: (id: string | number) => request<VenteDetail>(`/ventes/${id}/cancel`, { method: "POST" }),
    previewPricing: (produitId: string | number, uniteId: string | number, quantite: string | number, clientId?: string | number) =>
        request<PricingPreview>(`/ventes/pricing`, {
            method: "POST",
            body: JSON.stringify({ produitId, uniteId, quantite, clientId: clientId || undefined }),
        }),
    getVentePdf: (id: string | number) => requestBlob(`/ventes/${id}/pdf`),
    getVenteReceipt: (id: string | number) => requestBlob(`/ventes/${id}/receipt`),
};

// Aplati l'arbre de catégories (racines + enfants) en une liste plate, utile pour les <select>.
export function flattenCategories(tree: Categorie[]): CategorieRef[] {
    const result: CategorieRef[] = [];
    for (const node of tree) {
        result.push({ id: node.id, nom: node.nom });
        for (const child of node.enfants ?? []) {
            result.push({ id: child.id, nom: `${node.nom} > ${child.nom}` });
        }
    }
    return result;
}
