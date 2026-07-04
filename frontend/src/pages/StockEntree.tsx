import { useEffect, useState, type FormEvent } from "react";
import { useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { EntreePayload, Produit } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

const emptyForm: EntreePayload = { produitId: "", quantite: "", numeroLot: "", datePeremption: "", prixAchat: "" };

export default function StockEntree() {
    const navigate = useNavigate();
    const [form, setForm] = useState<EntreePayload>(emptyForm);
    const [produits, setProduits] = useState<Produit[]>([]);
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

    const handleChange = (field: keyof EntreePayload) => (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        setForm({ ...form, [field]: e.target.value });
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            await api.stockEntree(form);
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
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">Entrée de stock</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Produit</label>
                        <select className={input} value={form.produitId} onChange={handleChange("produitId")}>
                            <option value="">-- Sélectionnez un produit --</option>
                            {produits.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.nom}
                                </option>
                            ))}
                        </select>
                        {errors.produitId && <p className={fieldError}>{errors.produitId}</p>}
                    </div>

                    <div>
                        <label className={label}>Numéro de lot</label>
                        <input type="text" className={input} value={form.numeroLot} onChange={handleChange("numeroLot")} />
                    </div>

                    <div>
                        <label className={label}>Quantité</label>
                        <input type="number" step="0.01" className={input} value={form.quantite} onChange={handleChange("quantite")} />
                        {errors.quantite && <p className={fieldError}>{errors.quantite}</p>}
                    </div>

                    <div>
                        <label className={label}>Date de péremption</label>
                        <input type="date" className={input} value={form.datePeremption} onChange={handleChange("datePeremption")} />
                    </div>

                    <div>
                        <label className={label}>Prix d'achat</label>
                        <input type="number" step="0.01" className={input} value={form.prixAchat} onChange={handleChange("prixAchat")} />
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer l'entrée
                    </button>
                </form>
            </div>
        </div>
    );
}
