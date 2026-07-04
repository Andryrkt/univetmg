import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { User } from "../types";
import { btnDanger, btnPrimary, errorText, link } from "../ui";

export default function UserList() {
    const [users, setUsers] = useState<User[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const load = async () => {
        setLoading(true);
        setError(null);
        try {
            setUsers(await api.listUsers());
        } catch (err) {
            if (err instanceof ApiError && err.status === 401) return navigate("/login");
            setError("Impossible de charger les utilisateurs.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm("Supprimer cet utilisateur ?")) return;
        await api.deleteUser(id);
        load();
    };

    return (
        <div>
            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-slate-900">Utilisateurs</h1>
                <Link to="/users/new" className={btnPrimary}>
                    + Nouvel utilisateur
                </Link>
            </div>

            {loading && <p className="text-sm text-slate-500">Chargement...</p>}
            {error && <p className={errorText}>{error}</p>}

            {!loading && !error && (
                <div className="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Email</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Nom</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Rôles</th>
                                <th className="px-4 py-2 text-left font-medium text-slate-600">Vérifié</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {users.map((u) => (
                                <tr key={u.id}>
                                    <td className="px-4 py-2">{u.email}</td>
                                    <td className="px-4 py-2">
                                        {u.firstName} {u.lastName}
                                    </td>
                                    <td className="px-4 py-2">
                                        {u.roles.includes("ROLE_ADMIN") ? (
                                            <span className="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                                Admin
                                            </span>
                                        ) : (
                                            <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                                Utilisateur
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-2">{u.isVerified ? "Oui" : "Non"}</td>
                                    <td className="space-x-3 px-4 py-2 text-right">
                                        <Link to={`/users/${u.id}/edit`} className={link}>
                                            Modifier
                                        </Link>
                                        <button onClick={() => handleDelete(u.id)} className={btnDanger}>
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
