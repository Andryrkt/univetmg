import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { TypeClientDetail } from "../types";
import { link } from "../ui";

export default function TypeClientShow() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [typeClient, setTypeClient] = useState<TypeClientDetail | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) return;
        (async () => {
            try {
                setTypeClient(await api.getTypeClient(id));
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id]);

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;
    if (!typeClient) return <p className="text-sm text-slate-500">Type de client introuvable.</p>;

    return (
        <div className="mx-auto max-w-xl">
            <h1 className="text-2xl font-semibold text-slate-900">{typeClient.nom}</h1>
            <p className="mt-1 text-sm text-slate-500">Taux de remise : {typeClient.tauxRemise} %</p>
            <p className="text-sm text-slate-500">Actif : {typeClient.actif ? "Oui" : "Non"}</p>
            <p className="text-sm text-slate-500">Description : {typeClient.description ?? "-"}</p>
            <p className="text-sm text-slate-500">Clients liés : {typeClient.nbClients}</p>

            <p className="mt-6 space-x-3">
                <Link to={`/type-clients/${typeClient.id}/edit`} className={link}>
                    Modifier
                </Link>
                <Link to="/type-clients" className={link}>
                    Retour à la liste
                </Link>
            </p>
        </div>
    );
}
