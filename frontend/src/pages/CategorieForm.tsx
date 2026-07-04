import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError, flattenCategories } from "../api/client";
import type { CategoriePayload, CategorieRef } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

export default function CategorieForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [form, setForm] = useState<CategoriePayload>({ nom: "", abbreviation: "", parentId: "" });
    const [categories, setCategories] = useState<CategorieRef[]>([]);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        (async () => {
            try {
                const tree = await api.listCategories();
                setCategories(flattenCategories(tree).filter((c) => String(c.id) !== id));

                if (isEdit && id) {
                    const categorie = await api.getCategorie(id);
                    setForm({
                        nom: categorie.nom ?? "",
                        abbreviation: categorie.abbreviation ?? "",
                        parentId: categorie.parent ? String(categorie.parent.id) : "",
                    });
                }
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const handleChange = (field: keyof CategoriePayload) => (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        setForm({ ...form, [field]: e.target.value });
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            if (isEdit && id) {
                await api.updateCategorie(id, form);
            } else {
                await api.createCategorie(form);
            }
            navigate("/categories");
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
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier la catégorie" : "Nouvelle catégorie"}</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Nom</label>
                        <input type="text" className={input} value={form.nom} onChange={handleChange("nom")} />
                        {errors.nom && <p className={fieldError}>{errors.nom}</p>}
                    </div>

                    <div>
                        <label className={label}>Abréviation</label>
                        <input type="text" className={input} value={form.abbreviation} onChange={handleChange("abbreviation")} />
                    </div>

                    <div>
                        <label className={label}>Catégorie parente</label>
                        <select className={input} value={form.parentId} onChange={handleChange("parentId")}>
                            <option value="">-- Aucune (racine) --</option>
                            {categories.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.nom}
                                </option>
                            ))}
                        </select>
                        {errors.parentId && <p className={fieldError}>{errors.parentId}</p>}
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
