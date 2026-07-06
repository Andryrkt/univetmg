import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { ConversionStandard } from "../types";
import { btnDanger, btnPrimary, errorText, link } from "../ui";

export default function ConversionStandardList() {
    const [items, setItems] = useState<ConversionStandard[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const load = async () => {
        setLoading(true);
        setError(null);
        try {
            setItems(await api.listConversionStandards());
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
            setError("Impossible de charger les conversions.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm("Supprimer cette conversion ?")) return;
        await api.deleteConversionStandard(id);
        load();
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Conversions standard</h1>
                <Link to="/conversion-standards/new" className={btnPrimary}>
                    + Nouvelle conversion
                </Link>
            </div>

            {loading && <p className="text-sm text-slate-500">Chargement...</p>}
            {error && <p className={errorText}>{error}</p>}

            {!loading && !error && (
                <div className="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Unité d'origine</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Unité cible</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Facteur</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {items.map((c) => (
                                <tr key={c.id}>
                                    <td className="px-4 py-2">{c.uniteOrigine.nom}</td>
                                    <td className="px-4 py-2">{c.uniteCible.nom}</td>
                                    <td className="px-4 py-2">{c.facteur}</td>
                                    <td className="space-x-3 px-4 py-2 text-right">
                                        <Link to={`/conversion-standards/${c.id}/edit`} className={link}>
                                            Modifier
                                        </Link>
                                        <button onClick={() => handleDelete(c.id)} className={btnDanger}>
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
