import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Vente } from "../types";
import { badgeClass, btnPrimary, errorText, link } from "../ui";

const statutVariant: Record<string, string> = { brouillon: "secondary", validee: "ok", annulee: "danger" };

export default function VenteList() {
    const [ventes, setVentes] = useState<Vente[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    useEffect(() => {
        (async () => {
            try {
                setVentes(await api.listVentes());
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
                setError("Impossible de charger les ventes.");
            } finally {
                setLoading(false);
            }
        })();
    }, []);

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Ventes</h1>
                <Link to="/ventes/new" className={btnPrimary}>
                    + Nouvelle vente
                </Link>
            </div>

            {loading && <p className="text-sm text-slate-500">Chargement...</p>}
            {error && <p className={errorText}>{error}</p>}

            {!loading && !error && (
                <div className="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Facture</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Date</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Client</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Total</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Statut</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {ventes.map((v) => (
                                <tr key={v.id}>
                                    <td className="px-4 py-2">
                                        <Link to={`/ventes/${v.id}`} className={link}>
                                            {v.numeroFacture}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-2 whitespace-nowrap">{new Date(v.dateVente).toLocaleDateString()}</td>
                                    <td className="px-4 py-2">{v.client?.nom ?? "-"}</td>
                                    <td className="px-4 py-2">{v.total}</td>
                                    <td className="px-4 py-2">
                                        <span className={badgeClass(statutVariant[v.statut])}>{v.statutLabel}</span>
                                    </td>
                                    <td className="px-4 py-2 text-right">
                                        {v.statut === "brouillon" && (
                                            <Link to={`/ventes/${v.id}/edit`} className={link}>
                                                Modifier
                                            </Link>
                                        )}
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
