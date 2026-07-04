import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { ClientDetail } from "../types";
import { link } from "../ui";

export default function ClientShow() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [client, setClient] = useState<ClientDetail | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) return;
        (async () => {
            try {
                setClient(await api.getClient(id));
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id]);

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;
    if (!client) return <p className="text-sm text-slate-500">Client introuvable.</p>;

    return (
        <div className="mx-auto max-w-xl">
            <h1 className="text-2xl font-semibold text-slate-900">{client.nom}</h1>
            <p className="mt-1 text-sm text-slate-500">Téléphone : {client.telephone ?? "-"}</p>
            <p className="text-sm text-slate-500">Adresse : {client.adresse ?? "-"}</p>
            <p className="text-sm text-slate-500">Type : {client.typeClient?.nom ?? "-"}</p>
            <p className="text-sm text-slate-500">Ventes liées : {client.nbVentes}</p>

            <p className="mt-6 space-x-3">
                <Link to={`/clients/${client.id}/edit`} className={link}>
                    Modifier
                </Link>
                <Link to="/clients" className={link}>
                    Retour à la liste
                </Link>
            </p>
        </div>
    );
}
