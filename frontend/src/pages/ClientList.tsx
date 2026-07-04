import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Client } from "../types";
import { btnDanger, btnPrimary, errorText, link } from "../ui";

export default function ClientList() {
    const [clients, setClients] = useState<Client[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const load = async () => {
        setLoading(true);
        setError(null);
        try {
            setClients(await api.listClients());
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
            setError("Impossible de charger les clients.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm("Supprimer ce client ?")) return;
        await api.deleteClient(id);
        load();
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Clients</h1>
                <Link to="/clients/new" className={btnPrimary}>
                    + Nouveau client
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
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Téléphone</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Type</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {clients.map((c) => (
                                <tr key={c.id}>
                                    <td className="px-4 py-2">
                                        <Link to={`/clients/${c.id}`} className={link}>
                                            {c.nom}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-2">{c.telephone ?? "-"}</td>
                                    <td className="px-4 py-2">{c.typeClient?.nom ?? "-"}</td>
                                    <td className="space-x-3 px-4 py-2 text-right">
                                        <Link to={`/clients/${c.id}/edit`} className={link}>
                                            Modifier
                                        </Link>
                                        <button onClick={() => handleDelete(c.id)} className={btnDanger}>
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
