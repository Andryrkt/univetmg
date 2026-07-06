import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { DocumentationIndex } from "../types";
import { card, errorText, link } from "../ui";

export default function DocumentationList() {
    const [index, setIndex] = useState<DocumentationIndex | null>(null);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    useEffect(() => {
        (async () => {
            try {
                setIndex(await api.listDocumentation());
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
                setError("Impossible de charger la documentation.");
            }
        })();
    }, []);

    if (error) return <p className={errorText}>{error}</p>;
    if (!index) return <p className="text-sm text-slate-500">Chargement...</p>;

    return (
        <div>
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">Documentation</h1>

            <div className="space-y-6">
                {Object.entries(index).map(([catKey, cat]) => (
                    <div key={catKey} className={card}>
                        <h2 className="mb-3 text-lg font-semibold text-slate-900">{cat.label}</h2>
                        <ul className="space-y-2">
                            {cat.items.map((item) => (
                                <li key={item.slug}>
                                    <Link to={`/documentation/${item.slug}`} className={link}>
                                        {item.title}
                                    </Link>
                                    <p className="text-sm text-slate-500">{item.description}</p>
                                </li>
                            ))}
                        </ul>
                    </div>
                ))}
            </div>
        </div>
    );
}
