import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Categorie } from "../types";
import { btnDanger, btnPrimary, errorText, link } from "../ui";

export default function CategorieList() {
    const [categories, setCategories] = useState<Categorie[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const load = async () => {
        setLoading(true);
        setError(null);
        try {
            setCategories(await api.listCategories());
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
            setError("Impossible de charger les catégories.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm("Supprimer cette catégorie ?")) return;
        await api.deleteCategorie(id);
        load();
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Catégories</h1>
                <Link to="/categories/new" className={btnPrimary}>
                    + Nouvelle catégorie
                </Link>
            </div>

            {loading && <p className="text-sm text-slate-500">Chargement...</p>}
            {error && <p className={errorText}>{error}</p>}

            {!loading && !error && (
                <ul className="space-y-3">
                    {categories.map((c) => (
                        <li key={c.id} className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div className="flex items-center justify-between">
                                <Link to={`/categories/${c.id}`} className="font-medium text-slate-900 hover:text-indigo-600">
                                    {c.nom}
                                </Link>
                                <div className="space-x-3">
                                    <Link to={`/categories/${c.id}/edit`} className={link}>
                                        Modifier
                                    </Link>
                                    <button onClick={() => handleDelete(c.id)} className={btnDanger}>
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                            {c.enfants.length > 0 && (
                                <ul className="mt-3 ml-4 space-y-2 border-l border-slate-200 pl-4">
                                    {c.enfants.map((e) => (
                                        <li key={e.id} className="flex items-center justify-between text-sm">
                                            <Link to={`/categories/${e.id}`} className="text-slate-700 hover:text-indigo-600">
                                                {e.nom}
                                            </Link>
                                            <div className="space-x-3">
                                                <Link to={`/categories/${e.id}/edit`} className={link}>
                                                    Modifier
                                                </Link>
                                                <button onClick={() => handleDelete(e.id)} className={btnDanger}>
                                                    Supprimer
                                                </button>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
