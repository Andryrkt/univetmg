import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { FournisseurDetail } from "../types";
import { link } from "../ui";

export default function FournisseurShow() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [fournisseur, setFournisseur] = useState<FournisseurDetail | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) return;
        (async () => {
            try {
                setFournisseur(await api.getFournisseur(id));
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id]);

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;
    if (!fournisseur) return <p className="text-sm text-slate-500">Fournisseur introuvable.</p>;

    return (
        <div className="mx-auto max-w-xl">
            <h1 className="text-2xl font-semibold text-slate-900">{fournisseur.nom}</h1>
            <p className="mt-1 text-sm text-slate-500">Téléphone : {fournisseur.telephone}</p>
            <p className="text-sm text-slate-500">Adresse : {fournisseur.adresse ?? "-"}</p>
            <p className="text-sm text-slate-500">Email : {fournisseur.email ?? "-"}</p>
            <p className="text-sm text-slate-500">Produits liés : {fournisseur.nbProduits}</p>

            <p className="mt-6 space-x-3">
                <Link to={`/fournisseurs/${fournisseur.id}/edit`} className={link}>
                    Modifier
                </Link>
                <Link to="/fournisseurs" className={link}>
                    Retour à la liste
                </Link>
            </p>
        </div>
    );
}
