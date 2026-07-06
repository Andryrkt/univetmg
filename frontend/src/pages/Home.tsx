import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { User } from "../types";
import { card, link } from "../ui";

const modules = [
    { to: "/produits", label: "Produits" },
    { to: "/unites", label: "Unités" },
    { to: "/categories", label: "Catégories" },
    { to: "/fournisseurs", label: "Fournisseurs" },
    { to: "/stock", label: "Stock" },
    { to: "/ventes", label: "Ventes" },
    { to: "/clients", label: "Clients" },
    { to: "/type-clients", label: "Types de client" },
    { to: "/promotions", label: "Promotions" },
    { to: "/conversion-standards", label: "Conversions standard" },
    { to: "/documentation", label: "Documentation" },
];

export default function Home() {
    const [user, setUser] = useState<User | null>(null);
    const navigate = useNavigate();

    useEffect(() => {
        (async () => {
            try {
                setUser(await api.me());
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
        })();
    }, []);

    const isAdmin = user?.roles.includes("ROLE_ADMIN");

    return (
        <div>
            <h1 className="text-2xl font-semibold text-slate-900">Bienvenue{user ? `, ${user.firstName ?? user.email}` : ""}</h1>
            <p className="mt-1 text-sm text-slate-500">Accédez rapidement aux différents modules de gestion.</p>

            <div className="mt-6 grid grid-cols-3 gap-4">
                {modules.map((m) => (
                    <Link key={m.to} to={m.to} className={`${card} block hover:border-indigo-300 hover:shadow-md`}>
                        <span className={link}>{m.label}</span>
                    </Link>
                ))}
                {isAdmin && (
                    <Link to="/users" className={`${card} block hover:border-indigo-300 hover:shadow-md`}>
                        <span className={link}>Utilisateurs</span>
                    </Link>
                )}
            </div>
        </div>
    );
}
