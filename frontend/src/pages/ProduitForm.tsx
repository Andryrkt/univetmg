import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError, flattenCategories } from "../api/client";
import type { CategorieRef, Fournisseur, ProduitPayload, Unite } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

const emptyForm: ProduitPayload = {
    nom: "",
    description: "",
    stockMinimum: "",
    prixVente: "",
    uniteDeBaseId: "",
    categorieId: "",
    fournisseurId: "",
};

export default function ProduitForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [form, setForm] = useState<ProduitPayload>(emptyForm);
    const [unites, setUnites] = useState<Unite[]>([]);
    const [categories, setCategories] = useState<CategorieRef[]>([]);
    const [fournisseurs, setFournisseurs] = useState<Fournisseur[]>([]);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        (async () => {
            try {
                const [unitesData, categoriesData, fournisseursData] = await Promise.all([
                    api.listUnites(),
                    api.listCategories(),
                    api.listFournisseurs(),
                ]);
                setUnites(unitesData);
                setCategories(flattenCategories(categoriesData));
                setFournisseurs(fournisseursData);

                if (isEdit && id) {
                    const produit = await api.getProduit(id);
                    setForm({
                        nom: produit.nom ?? "",
                        description: produit.description ?? "",
                        stockMinimum: String(produit.stockMinimum ?? ""),
                        prixVente: produit.prixVente != null ? String(produit.prixVente) : "",
                        uniteDeBaseId: produit.uniteDeBase ? String(produit.uniteDeBase.id) : "",
                        categorieId: produit.categorie ? String(produit.categorie.id) : "",
                        fournisseurId: produit.fournisseur ? String(produit.fournisseur.id) : "",
                    });
                }
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const handleChange = (field: keyof ProduitPayload) => (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        setForm({ ...form, [field]: e.target.value });
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            if (isEdit && id) {
                await api.updateProduit(id, form);
            } else {
                await api.createProduit(form);
            }
            navigate("/produits");
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
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier le produit" : "Nouveau produit"}</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Nom</label>
                        <input type="text" className={input} value={form.nom} onChange={handleChange("nom")} />
                        {errors.nom && <p className={fieldError}>{errors.nom}</p>}
                    </div>

                    <div>
                        <label className={label}>Description</label>
                        <textarea className={input} rows={3} value={form.description} onChange={handleChange("description")} />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className={label}>Stock minimum</label>
                            <input
                                type="number"
                                step="0.01"
                                className={input}
                                value={form.stockMinimum}
                                onChange={handleChange("stockMinimum")}
                            />
                            {errors.stockMinimum && <p className={fieldError}>{errors.stockMinimum}</p>}
                        </div>

                        <div>
                            <label className={label}>Prix de vente</label>
                            <input type="number" step="0.01" className={input} value={form.prixVente} onChange={handleChange("prixVente")} />
                        </div>
                    </div>

                    <div>
                        <label className={label}>Unité de base</label>
                        <select className={input} value={form.uniteDeBaseId} onChange={handleChange("uniteDeBaseId")}>
                            <option value="">-- Choisir --</option>
                            {unites.map((u) => (
                                <option key={u.id} value={u.id}>
                                    {u.nom} ({u.symbole})
                                </option>
                            ))}
                        </select>
                        {errors.uniteDeBaseId && <p className={fieldError}>{errors.uniteDeBaseId}</p>}
                    </div>

                    <div>
                        <label className={label}>Catégorie</label>
                        <select className={input} value={form.categorieId} onChange={handleChange("categorieId")}>
                            <option value="">-- Aucune --</option>
                            {categories.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.nom}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className={label}>Fournisseur</label>
                        <select className={input} value={form.fournisseurId} onChange={handleChange("fournisseurId")}>
                            <option value="">-- Aucun --</option>
                            {fournisseurs.map((f) => (
                                <option key={f.id} value={f.id}>
                                    {f.nom}
                                </option>
                            ))}
                        </select>
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
