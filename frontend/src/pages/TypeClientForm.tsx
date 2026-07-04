import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { TypeClientPayload } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

const emptyForm: TypeClientPayload = { nom: "", tauxRemise: "0", description: "", actif: true };

export default function TypeClientForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [form, setForm] = useState<TypeClientPayload>(emptyForm);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(isEdit);

    useEffect(() => {
        if (!isEdit || !id) return;
        (async () => {
            try {
                const typeClient = await api.getTypeClient(id);
                setForm({
                    nom: typeClient.nom ?? "",
                    tauxRemise: typeClient.tauxRemise ?? "0",
                    description: typeClient.description ?? "",
                    actif: typeClient.actif,
                });
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const handleChange = (field: "nom" | "tauxRemise" | "description") => (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        setForm({ ...form, [field]: e.target.value });
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            if (isEdit && id) {
                await api.updateTypeClient(id, form);
            } else {
                await api.createTypeClient(form);
            }
            navigate("/type-clients");
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
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier le type de client" : "Nouveau type de client"}</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Nom</label>
                        <input type="text" className={input} value={form.nom} onChange={handleChange("nom")} />
                        {errors.nom && <p className={fieldError}>{errors.nom}</p>}
                    </div>

                    <div>
                        <label className={label}>Taux de remise (%)</label>
                        <input type="number" step="0.01" min="0" max="100" className={input} value={form.tauxRemise} onChange={handleChange("tauxRemise")} />
                        {errors.tauxRemise && <p className={fieldError}>{errors.tauxRemise}</p>}
                    </div>

                    <div>
                        <label className={label}>Description</label>
                        <textarea className={input} rows={3} value={form.description} onChange={handleChange("description")} />
                    </div>

                    <label className="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" checked={form.actif} onChange={(e) => setForm({ ...form, actif: e.target.checked })} />
                        Actif
                    </label>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
