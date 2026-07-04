import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Promotion } from "../types";
import { badgeClass, btnDanger, btnPrimary, errorText, link } from "../ui";

export default function PromotionList() {
    const [promotions, setPromotions] = useState<Promotion[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const load = async () => {
        setLoading(true);
        setError(null);
        try {
            setPromotions(await api.listPromotions());
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
            setError("Impossible de charger les promotions.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm("Supprimer cette promotion ?")) return;
        await api.deletePromotion(id);
        load();
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Promotions</h1>
                <Link to="/promotions/new" className={btnPrimary}>
                    + Nouvelle promotion
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
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Période</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Remise</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Statut</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {promotions.map((p) => (
                                <tr key={p.id}>
                                    <td className="px-4 py-2">
                                        <Link to={`/promotions/${p.id}`} className={link}>
                                            {p.nom}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-2">
                                        {p.dateDebut} → {p.dateFin}
                                    </td>
                                    <td className="px-4 py-2">{p.tauxRemise ? `${p.tauxRemise} %` : `${p.montantRemise} MGA`}</td>
                                    <td className="px-4 py-2">
                                        {p.isExpired ? (
                                            <span className={badgeClass("secondary")}>Expirée</span>
                                        ) : p.isCurrentlyActive ? (
                                            <span className={badgeClass("ok")}>Active</span>
                                        ) : (
                                            <span className={badgeClass("warning")}>À venir</span>
                                        )}
                                    </td>
                                    <td className="space-x-3 px-4 py-2 text-right">
                                        <Link to={`/promotions/${p.id}/edit`} className={link}>
                                            Modifier
                                        </Link>
                                        <button onClick={() => handleDelete(p.id)} className={btnDanger}>
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
