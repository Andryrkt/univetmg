import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Produit, PromotionPayload } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

const emptyForm: PromotionPayload = {
    nom: "",
    dateDebut: "",
    dateFin: "",
    tauxRemise: "",
    montantRemise: "",
    actif: true,
    produitIds: [],
};

export default function PromotionForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [form, setForm] = useState<PromotionPayload>(emptyForm);
    const [produits, setProduits] = useState<Produit[]>([]);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        (async () => {
            try {
                setProduits(await api.listProduits());

                if (isEdit && id) {
                    const promotion = await api.getPromotion(id);
                    setForm({
                        nom: promotion.nom ?? "",
                        dateDebut: promotion.dateDebut ?? "",
                        dateFin: promotion.dateFin ?? "",
                        tauxRemise: promotion.tauxRemise ?? "",
                        montantRemise: promotion.montantRemise ?? "",
                        actif: promotion.actif,
                        produitIds: promotion.produits.map((p) => p.id),
                    });
                }
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const toggleProduit = (produitId: number) => (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.checked) {
            setForm({ ...form, produitIds: [...form.produitIds, produitId] });
        } else {
            setForm({ ...form, produitIds: form.produitIds.filter((id) => id !== produitId) });
        }
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            if (isEdit && id) {
                await api.updatePromotion(id, form);
            } else {
                await api.createPromotion(form);
            }
            navigate("/promotions");
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
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier la promotion" : "Nouvelle promotion"}</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Nom</label>
                        <input type="text" className={input} value={form.nom} onChange={(e) => setForm({ ...form, nom: e.target.value })} />
                        {errors.nom && <p className={fieldError}>{errors.nom}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className={label}>Date de début</label>
                            <input
                                type="date"
                                className={input}
                                value={form.dateDebut}
                                onChange={(e) => setForm({ ...form, dateDebut: e.target.value })}
                            />
                            {errors.dateDebut && <p className={fieldError}>{errors.dateDebut}</p>}
                        </div>
                        <div>
                            <label className={label}>Date de fin</label>
                            <input
                                type="date"
                                className={input}
                                value={form.dateFin}
                                onChange={(e) => setForm({ ...form, dateFin: e.target.value })}
                            />
                            {errors.dateFin && <p className={fieldError}>{errors.dateFin}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className={label}>Taux de remise (%)</label>
                            <input
                                type="number"
                                step="0.01"
                                className={input}
                                value={form.tauxRemise}
                                onChange={(e) => setForm({ ...form, tauxRemise: e.target.value, montantRemise: "" })}
                            />
                        </div>
                        <div>
                            <label className={label}>Montant de remise</label>
                            <input
                                type="number"
                                step="0.01"
                                className={input}
                                value={form.montantRemise}
                                onChange={(e) => setForm({ ...form, montantRemise: e.target.value, tauxRemise: "" })}
                            />
                        </div>
                    </div>
                    {errors.tauxRemise && <p className={fieldError}>{errors.tauxRemise}</p>}

                    <label className="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" checked={form.actif} onChange={(e) => setForm({ ...form, actif: e.target.checked })} />
                        Actif
                    </label>

                    <div>
                        <label className={label}>Produits concernés</label>
                        <div className="max-h-48 overflow-y-auto rounded-md border border-slate-200 p-2">
                            {produits.map((p) => (
                                <label key={p.id} className="flex items-center gap-2 py-1 text-sm text-slate-700">
                                    <input type="checkbox" checked={form.produitIds.includes(p.id)} onChange={toggleProduit(p.id)} />
                                    {p.nom}
                                </label>
                            ))}
                        </div>
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
