import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { FournisseurPayload } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

const emptyForm: FournisseurPayload = { nom: "", telephone: "", adresse: "", email: "" };

export default function FournisseurForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [form, setForm] = useState<FournisseurPayload>(emptyForm);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(isEdit);

    useEffect(() => {
        if (!isEdit || !id) return;
        (async () => {
            try {
                const fournisseur = await api.getFournisseur(id);
                setForm({
                    nom: fournisseur.nom ?? "",
                    telephone: fournisseur.telephone ?? "",
                    adresse: fournisseur.adresse ?? "",
                    email: fournisseur.email ?? "",
                });
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const handleChange = (field: keyof FournisseurPayload) => (e: React.ChangeEvent<HTMLInputElement>) => {
        setForm({ ...form, [field]: e.target.value });
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            if (isEdit && id) {
                await api.updateFournisseur(id, form);
            } else {
                await api.createFournisseur(form);
            }
            navigate("/fournisseurs");
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
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier le fournisseur" : "Nouveau fournisseur"}</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Nom</label>
                        <input type="text" className={input} value={form.nom} onChange={handleChange("nom")} />
                        {errors.nom && <p className={fieldError}>{errors.nom}</p>}
                    </div>

                    <div>
                        <label className={label}>Téléphone</label>
                        <input type="text" className={input} value={form.telephone} onChange={handleChange("telephone")} />
                        {errors.telephone && <p className={fieldError}>{errors.telephone}</p>}
                    </div>

                    <div>
                        <label className={label}>Adresse</label>
                        <input type="text" className={input} value={form.adresse} onChange={handleChange("adresse")} />
                    </div>

                    <div>
                        <label className={label}>Email</label>
                        <input type="email" className={input} value={form.email} onChange={handleChange("email")} />
                        {errors.email && <p className={fieldError}>{errors.email}</p>}
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
