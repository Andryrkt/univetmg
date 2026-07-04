import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { MouvementStock } from "../types";
import { badgeClass, errorText, link } from "../ui";

export default function StockMouvements() {
    const [mouvements, setMouvements] = useState<MouvementStock[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    useEffect(() => {
        (async () => {
            try {
                setMouvements(await api.listMouvements());
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
                setError("Impossible de charger les mouvements.");
            } finally {
                setLoading(false);
            }
        })();
    }, []);

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Mouvements de stock</h1>
                <Link to="/stock" className={link}>
                    Retour à la liste
                </Link>
            </div>

            {loading && <p className="text-sm text-slate-500">Chargement...</p>}
            {error && <p className={errorText}>{error}</p>}

            {!loading && !error && (
                <div className="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Date</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Type</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Produit</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Quantité</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Stock avant/après</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Motif</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Utilisateur</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {mouvements.map((m) => (
                                <tr key={m.id}>
                                    <td className="px-4 py-2 whitespace-nowrap">{new Date(m.createdAt).toLocaleString()}</td>
                                    <td className="px-4 py-2">
                                        <span className={badgeClass(m.type === "sortie" ? "danger" : m.type === "entree" ? "success" : "warning")}>
                                            {m.typeLabel}
                                        </span>
                                    </td>
                                    <td className="px-4 py-2">{m.produit?.nom}</td>
                                    <td className="px-4 py-2">{m.quantite}</td>
                                    <td className="px-4 py-2">
                                        {m.stockAvant} → {m.stockApres}
                                    </td>
                                    <td className="px-4 py-2">{m.motif}</td>
                                    <td className="px-4 py-2">{m.user?.email}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}
