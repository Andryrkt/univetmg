import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { UnitePayload } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

export default function UniteForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [form, setForm] = useState<UnitePayload>({ nom: "", symbole: "" });
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(isEdit);

    useEffect(() => {
        if (!isEdit || !id) return;
        (async () => {
            try {
                const unite = await api.getUnite(id);
                setForm({ nom: unite.nom ?? "", symbole: unite.symbole ?? "" });
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const handleChange = (field: keyof UnitePayload) => (e: React.ChangeEvent<HTMLInputElement>) => {
        setForm({ ...form, [field]: e.target.value });
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            if (isEdit && id) {
                await api.updateUnite(id, form);
            } else {
                await api.createUnite(form);
            }
            navigate("/unites");
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
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier l'unité" : "Nouvelle unité"}</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Nom</label>
                        <input type="text" className={input} value={form.nom} onChange={handleChange("nom")} />
                        {errors.nom && <p className={fieldError}>{errors.nom}</p>}
                    </div>

                    <div>
                        <label className={label}>Symbole</label>
                        <input type="text" className={input} value={form.symbole} onChange={handleChange("symbole")} />
                        {errors.symbole && <p className={fieldError}>{errors.symbole}</p>}
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
