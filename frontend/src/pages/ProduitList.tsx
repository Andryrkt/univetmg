import { useEffect, useState, type FormEvent } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Produit } from "../types";
import { btnDanger, btnPrimary, errorText, input, link } from "../ui";

export default function ProduitList() {
    const [produits, setProduits] = useState<Produit[]>([]);
    const [search, setSearch] = useState("");
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const load = async (q?: string) => {
        setLoading(true);
        setError(null);
        try {
            setProduits(await api.listProduits(q));
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
            setError("Impossible de charger les produits.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        load(search);
    };

    const handleDelete = async (id: number) => {
        if (!confirm("Supprimer ce produit ?")) return;
        await api.deleteProduit(id);
        load(search);
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Produits</h1>
                <Link to="/produits/new" className={btnPrimary}>
                    + Nouveau produit
                </Link>
            </div>

            <form onSubmit={handleSearch} className="mb-6 flex gap-2">
                <input
                    type="text"
                    className={`${input} max-w-sm`}
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Rechercher par nom ou code"
                />
                <button type="submit" className={btnPrimary}>
                    Rechercher
                </button>
            </form>

            {loading && <p className="text-sm text-slate-500">Chargement...</p>}
            {error && <p className={errorText}>{error}</p>}

            {!loading && !error && (
                <div className="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Code</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Nom</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Catégorie</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Stock</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Prix</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {produits.map((p) => (
                                <tr key={p.id}>
                                    <td className="px-4 py-2">{p.code}</td>
                                    <td className="px-4 py-2">{p.nom}</td>
                                    <td className="px-4 py-2">{p.categorie?.nom ?? "-"}</td>
                                    <td className="px-4 py-2">{p.quantiteEnStock}</td>
                                    <td className="px-4 py-2">{p.prixVente ?? "-"}</td>
                                    <td className="space-x-3 px-4 py-2 text-right">
                                        <Link to={`/produits/${p.id}/edit`} className={link}>
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
