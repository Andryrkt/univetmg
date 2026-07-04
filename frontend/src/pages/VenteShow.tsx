import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { VenteDetail } from "../types";
import { badgeClass, btnSecondary, card, link } from "../ui";

const statutVariant: Record<string, string> = { brouillon: "secondary", validee: "ok", annulee: "danger" };

function openBlob(blob: Blob) {
    const url = URL.createObjectURL(blob);
    window.open(url, "_blank");
    setTimeout(() => URL.revokeObjectURL(url), 60_000);
}

export default function VenteShow() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [vente, setVente] = useState<VenteDetail | null>(null);
    const [loading, setLoading] = useState(true);

    const load = async () => {
        if (!id) return;
        try {
            setVente(await api.getVente(id));
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
        }
        setLoading(false);
    };

    useEffect(() => {
        load();
    }, [id]);

    const handleCancel = async () => {
        if (!id || !confirm("Annuler cette vente ?")) return;
        await api.cancelVente(id);
        load();
    };

    const handlePdf = async () => {
        if (!id) return;
        openBlob(await api.getVentePdf(id));
    };

    const handleReceipt = async () => {
        if (!id) return;
        openBlob(await api.getVenteReceipt(id));
    };

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;
    if (!vente) return <p className="text-sm text-slate-500">Vente introuvable.</p>;

    return (
        <div className="mx-auto max-w-3xl">
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">{vente.numeroFacture}</h1>
                    <p className="text-sm text-slate-500">{new Date(vente.dateVente).toLocaleString()}</p>
                </div>
                <span className={badgeClass(statutVariant[vente.statut])}>{vente.statutLabel}</span>
            </div>

            <div className={card}>
                <p className="text-sm text-slate-600">Client : {vente.client?.nom ?? "-"}</p>
                <p className="text-sm text-slate-600">Vendeur : {vente.user?.email ?? "-"}</p>

                <table className="mt-4 min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr>
                            <th className="py-2 text-left font-medium text-slate-600">Produit</th>
                            <th className="py-2 text-left font-medium text-slate-600">Unité</th>
                            <th className="py-2 text-left font-medium text-slate-600">Quantité</th>
                            <th className="py-2 text-left font-medium text-slate-600">Prix unitaire</th>
                            <th className="py-2 text-left font-medium text-slate-600">Remise</th>
                            <th className="py-2 text-left font-medium text-slate-600">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {vente.lignes.map((l) => (
                            <tr key={l.id}>
                                <td className="py-2">{l.produit.nom}</td>
                                <td className="py-2">{l.unite?.nom ?? "-"}</td>
                                <td className="py-2">{l.quantite}</td>
                                <td className="py-2">{l.prixUnitaire}</td>
                                <td className="py-2">{l.tauxRemise ? `${l.tauxRemise}%` : "-"}</td>
                                <td className="py-2">{l.sousTotal}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                <p className="mt-4 text-right text-lg font-semibold text-slate-900">Total : {vente.total}</p>
            </div>

            <div className="mt-6 flex items-center gap-3">
                <button onClick={handlePdf} className={btnSecondary}>
                    Télécharger la facture (PDF)
                </button>
                <button onClick={handleReceipt} className={btnSecondary}>
                    Télécharger le ticket
                </button>
                {vente.statut !== "annulee" && (
                    <button onClick={handleCancel} className={btnSecondary}>
                        Annuler la vente
                    </button>
                )}
                <Link to="/ventes" className={link}>
                    Retour à la liste
                </Link>
            </div>
        </div>
    );
}
