import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { StockItem } from "../types";
import { badgeClass, btnPrimary, errorText, link } from "../ui";

const statutLabels: Record<string, string> = { ok: "OK", alerte: "Alerte", rupture: "Rupture" };
const peremptionLabels: Record<string, string> = { ok: "OK", proche_peremption: "Proche péremption", perime: "Périmé" };

export default function StockIndex() {
    const [items, setItems] = useState<StockItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    useEffect(() => {
        (async () => {
            try {
                setItems(await api.listStock());
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
                setError("Impossible de charger le stock.");
            } finally {
                setLoading(false);
            }
        })();
    }, []);

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Stock</h1>
                <div className="flex gap-3">
                    <Link to="/stock/dashboard" className={link}>
                        Tableau de bord
                    </Link>
                    <Link to="/stock/mouvements" className={link}>
                        Mouvements
                    </Link>
                    <Link to="/stock/entree" className={btnPrimary}>
                        + Entrée
                    </Link>
                    <Link to="/stock/sortie" className={btnPrimary}>
                        Sortie
                    </Link>
                    <Link to="/stock/ajustement" className={btnPrimary}>
                        Ajustement
                    </Link>
                </div>
            </div>

            {loading && <p className="text-sm text-slate-500">Chargement...</p>}
            {error && <p className={errorText}>{error}</p>}

            {!loading && !error && (
                <div className="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Produit</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Stock</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Minimum</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Statut</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Péremption</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {items.map((item) => (
                                <tr key={item.produit.id}>
                                    <td className="px-4 py-2">{item.produit.nom}</td>
                                    <td className="px-4 py-2">{item.stockActuel}</td>
                                    <td className="px-4 py-2">{item.stockMinimum}</td>
                                    <td className="px-4 py-2">
                                        <span className={badgeClass(item.statut)}>{statutLabels[item.statut]}</span>
                                    </td>
                                    <td className="px-4 py-2">
                                        {item.statutPeremption && (
                                            <span className={badgeClass(item.statutPeremption)}>
                                                {peremptionLabels[item.statutPeremption]}
                                                {item.joursRestants != null && item.statutPeremption !== "ok" ? ` (${item.joursRestants}j)` : ""}
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-2 text-right">
                                        <Link to={`/stock/produits/${item.produit.id}`} className={link}>
                                            Historique
                                        </Link>
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
