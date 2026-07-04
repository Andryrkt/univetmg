import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Unite } from "../types";
import { btnDanger, btnPrimary, errorText, link } from "../ui";

export default function UniteList() {
    const [unites, setUnites] = useState<Unite[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const load = async () => {
        setLoading(true);
        setError(null);
        try {
            setUnites(await api.listUnites());
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
            setError("Impossible de charger les unités.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm("Supprimer cette unité ?")) return;
        await api.deleteUnite(id);
        load();
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Unités</h1>
                <Link to="/unites/new" className={btnPrimary}>
                    + Nouvelle unité
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
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Symbole</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Produits liés</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {unites.map((u) => (
                                <tr key={u.id}>
                                    <td className="px-4 py-2">
                                        <Link to={`/unites/${u.id}`} className={link}>
                                            {u.nom}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-2">{u.symbole}</td>
                                    <td className="px-4 py-2">{u.nbProduits}</td>
                                    <td className="space-x-3 px-4 py-2 text-right">
                                        <Link to={`/unites/${u.id}/edit`} className={link}>
                                            Modifier
                                        </Link>
                                        <button onClick={() => handleDelete(u.id)} className={btnDanger}>
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
