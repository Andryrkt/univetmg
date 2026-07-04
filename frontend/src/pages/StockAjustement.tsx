import { useEffect, useState, type FormEvent } from "react";
import { useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { AjustementPayload, Lot, Produit } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

const emptyForm: AjustementPayload = { lotId: "", nouvelleQuantite: "", motif: "" };

export default function StockAjustement() {
    const navigate = useNavigate();
    const [produitId, setProduitId] = useState("");
    const [produits, setProduits] = useState<Produit[]>([]);
    const [lots, setLots] = useState<Lot[]>([]);
    const [form, setForm] = useState<AjustementPayload>(emptyForm);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        (async () => {
            try {
                setProduits(await api.listProduits());
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, []);

    useEffect(() => {
        if (!produitId) {
            setLots([]);
            return;
        }
        (async () => {
            setLots(await api.listLots(produitId));
            setForm((f) => ({ ...f, lotId: "" }));
        })();
    }, [produitId]);

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            await api.stockAjustement(form);
            navigate("/stock");
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
        <div className="mx-auto max-w-xl">
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">Ajustement de stock</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Produit</label>
                        <select className={input} value={produitId} onChange={(e) => setProduitId(e.target.value)}>
                            <option value="">-- Sélectionnez un produit --</option>
                            {produits.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.nom}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className={label}>Lot</label>
                        <select
                            className={input}
                            value={form.lotId}
                            onChange={(e) => setForm({ ...form, lotId: e.target.value })}
                            disabled={!produitId}
                        >
                            <option value="">-- Sélectionnez un lot --</option>
                            {lots.map((l) => (
                                <option key={l.id} value={l.id}>
                                    {l.numeroLot ?? "Sans numéro"} (Stock actuel: {l.quantite})
                                </option>
                            ))}
                        </select>
                        {errors.lotId && <p className={fieldError}>{errors.lotId}</p>}
                    </div>

                    <div>
                        <label className={label}>Nouvelle quantité en stock</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            className={input}
                            value={form.nouvelleQuantite}
                            onChange={(e) => setForm({ ...form, nouvelleQuantite: e.target.value })}
                        />
                        {errors.nouvelleQuantite && <p className={fieldError}>{errors.nouvelleQuantite}</p>}
                    </div>

                    <div>
                        <label className={label}>Motif de l'ajustement</label>
                        <textarea
                            className={input}
                            rows={2}
                            placeholder="Ex: Inventaire, Correction erreur, Perte..."
                            value={form.motif}
                            onChange={(e) => setForm({ ...form, motif: e.target.value })}
                        />
                        {errors.motif && <p className={fieldError}>{errors.motif}</p>}
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer l'ajustement
                    </button>
                </form>
            </div>
        </div>
    );
}
