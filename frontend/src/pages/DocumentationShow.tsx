import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { DocumentationPage } from "../types";
import { link } from "../ui";

export default function DocumentationShow() {
    const { slug } = useParams();
    const navigate = useNavigate();
    const [doc, setDoc] = useState<DocumentationPage | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!slug) return;
        (async () => {
            try {
                setDoc(await api.getDocumentation(slug));
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [slug]);

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;
    if (!doc) return <p className="text-sm text-slate-500">Document introuvable.</p>;

    return (
        <div>
            <p className="mb-4">
                <Link to="/documentation" className={link}>
                    ← Retour à la documentation
                </Link>
            </p>
            <article
                className="markdown-content rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
                dangerouslySetInnerHTML={{ __html: doc.contentHtml }}
            />
        </div>
    );
}
