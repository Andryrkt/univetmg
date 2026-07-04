import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { TypeClient } from "../types";
import { btnDanger, btnPrimary, errorText, link } from "../ui";

export default function TypeClientList() {
    const [typeClients, setTypeClients] = useState<TypeClient[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const load = async () => {
        setLoading(true);
        setError(null);
        try {
            setTypeClients(await api.listTypeClients());
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
            setError("Impossible de charger les types de client.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm("Supprimer ce type de client ?")) return;
        try {
            await api.deleteTypeClient(id);
            load();
        } catch (err) {
            if (err instanceof ApiError && err.status === 409) {
                alert(err.body?.message ?? "Suppression impossible.");
            }
        }
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Types de client</h1>
                <Link to="/type-clients/new" className={btnPrimary}>
                    + Nouveau type de client
                </Link>
            </div>

            {loading && <p className="text-sm text-slate-500">Chargement...</p>}
            {error && <p className={errorText}>{error}</p>}

            {!loading && !error && (
                <div className="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Nom</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Taux de remise</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Actif</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {typeClients.map((t) => (
                                <tr key={t.id}>
                                    <td className="px-4 py-2">
                                        <Link to={`/type-clients/${t.id}`} className={link}>
                                            {t.nom}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-2">{t.tauxRemise} %</td>
                                    <td className="px-4 py-2">{t.actif ? "Oui" : "Non"}</td>
                                    <td className="space-x-3 px-4 py-2 text-right">
                                        <Link to={`/type-clients/${t.id}/edit`} className={link}>
                                            Modifier
                                        </Link>
                                        <button onClick={() => handleDelete(t.id)} className={btnDanger}>
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}
