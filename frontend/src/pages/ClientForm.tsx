import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { ClientPayload, TypeClientRef } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

const emptyForm: ClientPayload = { nom: "", telephone: "", adresse: "", typeClientId: "" };

export default function ClientForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [form, setForm] = useState<ClientPayload>(emptyForm);
    const [typeClients, setTypeClients] = useState<TypeClientRef[]>([]);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        (async () => {
            try {
                setTypeClients(await api.listTypeClients());

                if (isEdit && id) {
                    const client = await api.getClient(id);
                    setForm({
                        nom: client.nom ?? "",
                        telephone: client.telephone ?? "",
                        adresse: client.adresse ?? "",
                        typeClientId: client.typeClient ? String(client.typeClient.id) : "",
                    });
                }
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const handleChange = (field: keyof ClientPayload) => (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        setForm({ ...form, [field]: e.target.value });
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            if (isEdit && id) {
                await api.updateClient(id, form);
            } else {
                await api.createClient(form);
            }
            navigate("/clients");
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
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier le client" : "Nouveau client"}</h1>

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
                    </div>

                    <div>
                        <label className={label}>Adresse</label>
                        <input type="text" className={input} value={form.adresse} onChange={handleChange("adresse")} />
                    </div>

                    <div>
                        <label className={label}>Type de client</label>
                        <select className={input} value={form.typeClientId} onChange={handleChange("typeClientId")}>
                            <option value="">-- Aucun --</option>
                            {typeClients.map((t) => (
                                <option key={t.id} value={t.id}>
                                    {t.nom}
                                </option>
                            ))}
                        </select>
                        {errors.typeClientId && <p className={fieldError}>{errors.typeClientId}</p>}
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
