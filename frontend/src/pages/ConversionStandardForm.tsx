import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { ConversionStandardPayload, Unite } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

const emptyForm: ConversionStandardPayload = { uniteOrigineId: "", uniteCibleId: "", facteur: "" };

export default function ConversionStandardForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [form, setForm] = useState<ConversionStandardPayload>(emptyForm);
    const [unites, setUnites] = useState<Unite[]>([]);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        (async () => {
            try {
                setUnites(await api.listUnites());

                if (isEdit && id) {
                    const conv = await api.getConversionStandard(id);
                    setForm({
                        uniteOrigineId: String(conv.uniteOrigine.id),
                        uniteCibleId: String(conv.uniteCible.id),
                        facteur: String(conv.facteur),
                    });
                }
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            if (isEdit && id) {
                await api.updateConversionStandard(id, form);
            } else {
                await api.createConversionStandard(form);
            }
            navigate("/conversion-standards");
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
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier la conversion" : "Nouvelle conversion"}</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Unité d'origine</label>
                        <select
                            className={input}
                            value={form.uniteOrigineId}
                            onChange={(e) => setForm({ ...form, uniteOrigineId: e.target.value })}
                        >
                            <option value="">-- Choisir --</option>
                            {unites.map((u) => (
                                <option key={u.id} value={u.id}>
                                    {u.nom}
                                </option>
                            ))}
                        </select>
                        {errors.uniteOrigineId && <p className={fieldError}>{errors.uniteOrigineId}</p>}
                    </div>

                    <div>
                        <label className={label}>Unité cible</label>
                        <select
                            className={input}
                            value={form.uniteCibleId}
                            onChange={(e) => setForm({ ...form, uniteCibleId: e.target.value })}
                        >
                            <option value="">-- Choisir --</option>
                            {unites.map((u) => (
                                <option key={u.id} value={u.id}>
                                    {u.nom}
                                </option>
                            ))}
                        </select>
                        {errors.uniteCibleId && <p className={fieldError}>{errors.uniteCibleId}</p>}
                    </div>

                    <div>
                        <label className={label}>Facteur</label>
                        <input
                            type="number"
                            step="0.0001"
                            className={input}
                            value={form.facteur}
                            onChange={(e) => setForm({ ...form, facteur: e.target.value })}
                        />
                        <p className="mt-1 text-xs text-slate-500">1 unité d'origine = [facteur] unité(s) cible</p>
                        {errors.facteur && <p className={fieldError}>{errors.facteur}</p>}
                    </div>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
