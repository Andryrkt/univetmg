import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Fournisseur } from "../types";
import { btnDanger, btnPrimary, errorText, link } from "../ui";

export default function FournisseurList() {
    const [fournisseurs, setFournisseurs] = useState<Fournisseur[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const load = async () => {
        setLoading(true);
        setError(null);
        try {
            setFournisseurs(await api.listFournisseurs());
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
            setError("Impossible de charger les fournisseurs.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm("Supprimer ce fournisseur ?")) return;
        await api.deleteFournisseur(id);
        load();
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Fournisseurs</h1>
                <Link to="/fournisseurs/new" className={btnPrimary}>
                    + Nouveau fournisseur
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
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Email</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {fournisseurs.map((f) => (
                                <tr key={f.id}>
                                    <td className="px-4 py-2">
                                        <Link to={`/fournisseurs/${f.id}`} className={link}>
                                            {f.nom}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-2">{f.telephone}</td>
                                    <td className="px-4 py-2">{f.email ?? "-"}</td>
                                    <td className="space-x-3 px-4 py-2 text-right">
                                        <Link to={`/fournisseurs/${f.id}/edit`} className={link}>
                                            Modifier
                                        </Link>
                                        <button onClick={() => handleDelete(f.id)} className={btnDanger}>
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
