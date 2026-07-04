import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { UniteDetail } from "../types";
import { card, link } from "../ui";

export default function UniteShow() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [unite, setUnite] = useState<UniteDetail | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) return;
        (async () => {
            try {
                setUnite(await api.getUnite(id));
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id]);

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;
    if (!unite) return <p className="text-sm text-slate-500">Unité introuvable.</p>;

    return (
        <div className="mx-auto max-w-xl">
            <h1 className="text-2xl font-semibold text-slate-900">{unite.nom}</h1>
            <p className="mt-1 text-sm text-slate-500">Symbole : {unite.symbole}</p>

            <div className={`${card} mt-6`}>
                <h2 className="mb-3 text-lg font-semibold text-slate-900">Conditionnements</h2>
                {unite.conditionnements.length === 0 ? (
                    <p className="text-sm text-slate-500">Aucun conditionnement.</p>
                ) : (
                    <ul className="space-y-1 text-sm text-slate-700">
                        {unite.conditionnements.map((c) => (
                            <li key={c.id}>
                                {c.produit?.nom} — quantité {c.quantite}
                            </li>
                        ))}
                    </ul>
                )}
            </div>

            <div className={`${card} mt-4`}>
                <h2 className="mb-3 text-lg font-semibold text-slate-900">Conversions</h2>
                {unite.conversions.length === 0 ? (
                    <p className="text-sm text-slate-500">Aucune conversion.</p>
                ) : (
                    <ul className="space-y-1 text-sm text-slate-700">
                        {unite.conversions.map((c) => (
                            <li key={c.id}>
                                {c.uniteSource} → {c.uniteCible} (facteur {c.facteur})
                            </li>
                        ))}
                    </ul>
                )}
            </div>

            <p className="mt-6 space-x-3">
                <Link to={`/unites/${unite.id}/edit`} className={link}>
                    Modifier
                </Link>
                <Link to="/unites" className={link}>
                    Retour à la liste
                </Link>
            </p>
        </div>
    );
}
