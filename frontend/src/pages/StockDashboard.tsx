import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { StockDashboard as StockDashboardData } from "../types";
import { card, errorText, link } from "../ui";

export default function StockDashboard() {
    const [data, setData] = useState<StockDashboardData | null>(null);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    useEffect(() => {
        (async () => {
            try {
                setData(await api.getStockDashboard());
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
                setError("Impossible de charger le tableau de bord.");
            }
        })();
    }, []);

    if (error) return <p className={errorText}>{error}</p>;
    if (!data) return <p className="text-sm text-slate-500">Chargement...</p>;

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Tableau de bord stock</h1>
                <Link to="/stock" className={link}>
                    Retour à la liste
                </Link>
            </div>

            <div className="mb-6 grid grid-cols-3 gap-4">
                <div className={card}>
                    <p className="text-sm text-slate-500">Valeur totale du stock</p>
                    <p className="mt-1 text-2xl font-semibold text-slate-900">{data.valeurTotale.toFixed(2)}</p>
                </div>
                <div className={card}>
                    <p className="text-sm text-slate-500">Produits en rupture</p>
                    <p className="mt-1 text-2xl font-semibold text-red-600">{data.produitsEnRupture.length}</p>
                </div>
                <div className={card}>
                    <p className="text-sm text-slate-500">Produits à commander</p>
                    <p className="mt-1 text-2xl font-semibold text-amber-600">{data.produitsACommander.length}</p>
                </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className={card}>
                    <h2 className="mb-3 text-lg font-semibold text-slate-900">Produits à commander</h2>
                    {data.produitsACommander.length === 0 ? (
                        <p className="text-sm text-slate-500">Aucun produit à commander.</p>
                    ) : (
                        <ul className="space-y-1 text-sm">
                            {data.produitsACommander.map((item) => (
                                <li key={item.produit.id}>
                                    {item.produit.nom} — manque {item.manquant} (stock {item.stockActuel}/{item.stockMinimum})
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <div className={card}>
                    <h2 className="mb-3 text-lg font-semibold text-slate-900">Lots périmés</h2>
                    {data.produitsPerimes.length === 0 ? (
                        <p className="text-sm text-slate-500">Aucun lot périmé.</p>
                    ) : (
                        <ul className="space-y-1 text-sm">
                            {data.produitsPerimes.map((item) => (
                                <li key={item.lotId}>
                                    {item.produit.nom} — périmé depuis {item.joursDepuisPeremption}j ({item.datePeremption})
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <div className={card}>
                    <h2 className="mb-3 text-lg font-semibold text-slate-900">Proches de la péremption</h2>
                    {data.produitsProchesPeremption.length === 0 ? (
                        <p className="text-sm text-slate-500">Aucun lot proche de la péremption.</p>
                    ) : (
                        <ul className="space-y-1 text-sm">
                            {data.produitsProchesPeremption.map((item) => (
                                <li key={item.lotId}>
                                    {item.produit.nom} — dans {item.joursRestants}j ({item.datePeremption})
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <div className={card}>
                    <h2 className="mb-3 text-lg font-semibold text-slate-900">Mouvements récents</h2>
                    {data.mouvementsRecents.length === 0 ? (
                        <p className="text-sm text-slate-500">Aucun mouvement récent.</p>
                    ) : (
                        <ul className="space-y-1 text-sm">
                            {data.mouvementsRecents.map((m) => (
                                <li key={m.id}>
                                    {m.typeLabel} — {m.produit?.nom} ({m.quantite})
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </div>
    );
}
