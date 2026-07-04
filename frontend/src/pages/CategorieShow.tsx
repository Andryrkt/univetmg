import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { CategorieDetail } from "../types";
import { card, link } from "../ui";

export default function CategorieShow() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [categorie, setCategorie] = useState<CategorieDetail | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) return;
        (async () => {
            try {
                setCategorie(await api.getCategorie(id));
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id]);

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;
    if (!categorie) return <p className="text-sm text-slate-500">Catégorie introuvable.</p>;

    return (
        <div className="mx-auto max-w-xl">
            <p className="text-sm text-slate-500">{categorie.path.map((c) => c.nom).join(" > ")}</p>
            <h1 className="text-2xl font-semibold text-slate-900">{categorie.nom}</h1>
            <p className="mt-1 text-sm text-slate-500">Abréviation : {categorie.abbreviation ?? "-"}</p>
            <p className="text-sm text-slate-500">Produits liés : {categorie.nbProduits}</p>

            <div className={`${card} mt-6`}>
                <h2 className="mb-3 text-lg font-semibold text-slate-900">Sous-catégories</h2>
                {categorie.enfants.length === 0 ? (
                    <p className="text-sm text-slate-500">Aucune sous-catégorie.</p>
                ) : (
                    <ul className="space-y-1 text-sm">
                        {categorie.enfants.map((e) => (
                            <li key={e.id}>
                                <Link to={`/categories/${e.id}`} className={link}>
                                    {e.nom}
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>

            <p className="mt-6 space-x-3">
                <Link to={`/categories/${categorie.id}/edit`} className={link}>
                    Modifier
                </Link>
                <Link to="/categories" className={link}>
                    Retour à la liste
                </Link>
            </p>
        </div>
    );
}
