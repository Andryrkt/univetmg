import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { ProduitHistorique } from "../types";
import { badgeClass, link } from "../ui";

export default function StockHistoriqueProduit() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [data, setData] = useState<ProduitHistorique | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) return;
        (async () => {
            try {
                setData(await api.getHistoriqueProduit(id));
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id]);

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;
    if (!data) return <p className="text-sm text-slate-500">Produit introuvable.</p>;

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">{data.produit.nom}</h1>
                    <p className="text-sm text-slate-500">Stock actuel : {data.stockActuel}</p>
                </div>
                <Link to="/stock" className={link}>
                    Retour à la liste
                </Link>
            </div>

            <div className="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50">
                        <tr>
                            <th className="px-4 py-2 text-left font-medium text-slate-600">Date</th>
                            <th className="px-4 py-2 text-left font-medium text-slate-600">Type</th>
                            <th className="px-4 py-2 text-left font-medium text-slate-600">Quantité</th>
                            <th className="px-4 py-2 text-left font-medium text-slate-600">Stock avant/après</th>
                            <th className="px-4 py-2 text-left font-medium text-slate-600">Motif</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {data.mouvements.map((m) => (
                            <tr key={m.id}>
                                <td className="px-4 py-2 whitespace-nowrap">{new Date(m.createdAt).toLocaleString()}</td>
                                <td className="px-4 py-2">
                                    <span className={badgeClass(m.type === "sortie" ? "danger" : m.type === "entree" ? "success" : "warning")}>
                                        {m.typeLabel}
                                    </span>
                                </td>
                                <td className="px-4 py-2">{m.quantite}</td>
                                <td className="px-4 py-2">
                                    {m.stockAvant} → {m.stockApres}
                                </td>
                                <td className="px-4 py-2">{m.motif}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
