import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Client, PricingPreview, Produit, ProduitUniteOption } from "../types";

type EditableStatut = "brouillon" | "validee";
import { btnDanger, btnPrimary, btnSecondary, card, errorText, fieldError, input, label } from "../ui";

interface LigneState {
    produitId: string;
    uniteId: string;
    quantite: string;
    uniteOptions: ProduitUniteOption[];
    preview: PricingPreview | null;
}

const emptyLigne: LigneState = { produitId: "", uniteId: "", quantite: "1", uniteOptions: [], preview: null };

export default function VenteForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [dateVente, setDateVente] = useState("");
    const [clientId, setClientId] = useState("");
    const [statut, setStatut] = useState<EditableStatut>("brouillon");
    const [lignes, setLignes] = useState<LigneState[]>([{ ...emptyLigne }]);
    const [produits, setProduits] = useState<Produit[]>([]);
    const [clients, setClients] = useState<Client[]>([]);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        (async () => {
            try {
                const [produitsData, clientsData] = await Promise.all([api.listProduits(), api.listClients()]);
                setProduits(produitsData);
                setClients(clientsData);

                if (isEdit && id) {
                    const vente = await api.getVente(id);
                    setDateVente(vente.dateVente.slice(0, 10));
                    setClientId(vente.client ? String(vente.client.id) : "");
                    setStatut(vente.statut === "annulee" ? "brouillon" : vente.statut);

                    const loadedLignes = await Promise.all(
                        vente.lignes.map(async (l): Promise<LigneState> => {
                            const uniteOptions = await api.getProduitUnites(l.produit.id);
                            return {
                                produitId: String(l.produit.id),
                                uniteId: l.unite ? String(l.unite.id) : "",
                                quantite: String(l.quantite),
                                uniteOptions,
                                preview: {
                                    prixCatalogue: Number(l.prixCatalogue ?? 0),
                                    tauxRemise: l.tauxRemise ?? 0,
                                    montantRemise: Number(l.montantRemise ?? 0),
                                    prixFinal: Number(l.prixUnitaire),
                                    typeRemise: l.typeRemise,
                                    facteurConversion: l.facteurConversion,
                                },
                            };
                        })
                    );
                    setLignes(loadedLignes);
                }
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const refreshPreview = async (index: number, next: LigneState[]) => {
        const l = next[index];
        if (!l.produitId || !l.uniteId || !l.quantite) return;
        try {
            const preview = await api.previewPricing(l.produitId, l.uniteId, l.quantite, clientId || undefined);
            setLignes((current) => {
                const copy = [...current];
                copy[index] = { ...copy[index], preview };
                return copy;
            });
        } catch {
            // ignore preview errors, server will validate on submit
        }
    };

    const handleProduitChange = async (index: number, produitId: string) => {
        const uniteOptions = produitId ? await api.getProduitUnites(produitId) : [];
        setLignes((current) => {
            const copy = [...current];
            copy[index] = { ...copy[index], produitId, uniteId: "", preview: null, uniteOptions };
            return copy;
        });
    };

    const handleLigneField = (index: number, field: "uniteId" | "quantite", value: string) => {
        setLignes((current) => {
            const copy = [...current];
            copy[index] = { ...copy[index], [field]: value };
            refreshPreview(index, copy);
            return copy;
        });
    };

    const addLigne = () => setLignes((current) => [...current, { ...emptyLigne }]);
    const removeLigne = (index: number) => setLignes((current) => current.filter((_, i) => i !== index));

    const total = lignes.reduce((sum, l) => {
        if (!l.preview || !l.quantite) return sum;
        return sum + Number(l.quantite) * l.preview.prixFinal;
    }, 0);

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});

        const payload = {
            dateVente,
            clientId,
            statut,
            lignes: lignes.map((l) => ({ produitId: l.produitId, uniteId: l.uniteId, quantite: l.quantite })),
        };

        try {
            if (isEdit && id) {
                await api.updateVente(id, payload);
            } else {
                await api.createVente(payload);
            }
            navigate("/ventes");
        } catch (err) {
            if (err instanceof ApiError && err.status === 422 && err.body?.errors) {
                setErrors(err.body.errors);
            } else {
                setErrors({ global: "Une erreur est survenue." });
            }
        }
    };

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;

    return (
        <div className="mx-auto max-w-3xl">
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier la vente" : "Nouvelle vente"}</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}
                {errors.lignes && <p className={`${errorText} mb-4`}>{errors.lignes}</p>}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-3 gap-4">
                        <div>
                            <label className={label}>Date de vente</label>
                            <input type="date" className={input} value={dateVente} onChange={(e) => setDateVente(e.target.value)} />
                        </div>
                        <div>
                            <label className={label}>Client</label>
                            <select
                                className={input}
                                value={clientId}
                                onChange={(e) => {
                                    setClientId(e.target.value);
                                    lignes.forEach((_, i) => refreshPreview(i, lignes));
                                }}
                            >
                                <option value="">-- Aucun --</option>
                                {clients.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.nom}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className={label}>Statut</label>
                            <select className={input} value={statut} onChange={(e) => setStatut(e.target.value as EditableStatut)}>
                                <option value="brouillon">Brouillon</option>
                                <option value="validee">Validée</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <div className="mb-2 flex items-center justify-between">
                            <label className={label}>Lignes de vente</label>
                            <button type="button" onClick={addLigne} className={btnSecondary}>
                                + Ajouter une ligne
                            </button>
                        </div>

                        <div className="space-y-3">
                            {lignes.map((l, index) => (
                                <div key={index} className="grid grid-cols-12 items-start gap-2 rounded-md border border-slate-200 p-3">
                                    <div className="col-span-4">
                                        <select
                                            className={input}
                                            value={l.produitId}
                                            onChange={(e) => handleProduitChange(index, e.target.value)}
                                        >
                                            <option value="">-- Produit --</option>
                                            {produits.map((p) => (
                                                <option key={p.id} value={p.id}>
                                                    {p.nom}
                                                </option>
                                            ))}
                                        </select>
                                        {errors[`lignes[${index}].produitId`] && (
                                            <p className={fieldError}>{errors[`lignes[${index}].produitId`]}</p>
                                        )}
                                    </div>
                                    <div className="col-span-3">
                                        <select
                                            className={input}
                                            value={l.uniteId}
                                            onChange={(e) => handleLigneField(index, "uniteId", e.target.value)}
                                            disabled={!l.produitId}
                                        >
                                            <option value="">-- Unité --</option>
                                            {l.uniteOptions.map((u) => (
                                                <option key={u.id} value={u.id}>
                                                    {u.nom} {u.symbole ? `(${u.symbole})` : ""}
                                                </option>
                                            ))}
                                        </select>
                                        {errors[`lignes[${index}].uniteId`] && <p className={fieldError}>{errors[`lignes[${index}].uniteId`]}</p>}
                                    </div>
                                    <div className="col-span-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            className={input}
                                            value={l.quantite}
                                            onChange={(e) => handleLigneField(index, "quantite", e.target.value)}
                                        />
                                        {errors[`lignes[${index}].quantite`] && (
                                            <p className={fieldError}>{errors[`lignes[${index}].quantite`]}</p>
                                        )}
                                    </div>
                                    <div className="col-span-2 pt-2 text-sm text-slate-600">
                                        {l.preview && l.quantite ? (Number(l.quantite) * l.preview.prixFinal).toFixed(2) : "-"}
                                    </div>
                                    <div className="col-span-1 pt-1 text-right">
                                        {lignes.length > 1 && (
                                            <button type="button" onClick={() => removeLigne(index)} className={btnDanger}>
                                                ✕
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>

                        <p className="mt-3 text-right text-lg font-semibold text-slate-900">Total : {total.toFixed(2)}</p>
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
