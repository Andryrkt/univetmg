import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { PromotionDetail } from "../types";
import { badgeClass, link } from "../ui";

export default function PromotionShow() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [promotion, setPromotion] = useState<PromotionDetail | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) return;
        (async () => {
            try {
                setPromotion(await api.getPromotion(id));
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id]);

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;
    if (!promotion) return <p className="text-sm text-slate-500">Promotion introuvable.</p>;

    return (
        <div className="mx-auto max-w-xl">
            <h1 className="text-2xl font-semibold text-slate-900">{promotion.nom}</h1>
            <p className="mt-1 text-sm text-slate-500">
                {promotion.dateDebut} → {promotion.dateFin}
            </p>
            <p className="mt-2">
                {promotion.isExpired ? (
                    <span className={badgeClass("secondary")}>Expirée</span>
                ) : promotion.isCurrentlyActive ? (
                    <span className={badgeClass("ok")}>Active</span>
                ) : (
                    <span className={badgeClass("warning")}>À venir</span>
                )}
            </p>
            <p className="mt-2 text-sm text-slate-500">
                Remise : {promotion.tauxRemise ? `${promotion.tauxRemise} %` : `${promotion.montantRemise} MGA`}
            </p>

            <h2 className="mt-6 mb-2 text-lg font-semibold text-slate-900">Produits concernés</h2>
            {promotion.produits.length === 0 ? (
                <p className="text-sm text-slate-500">Aucun produit.</p>
            ) : (
                <ul className="list-inside list-disc text-sm text-slate-700">
                    {promotion.produits.map((p) => (
                        <li key={p.id}>{p.nom}</li>
                    ))}
                </ul>
            )}

            <p className="mt-6 space-x-3">
                <Link to={`/promotions/${promotion.id}/edit`} className={link}>
                    Modifier
                </Link>
                <Link to="/promotions" className={link}>
                    Retour à la liste
                </Link>
            </p>
        </div>
    );
}
