import { useEffect, useState, type FormEvent } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { api, ApiError } from "../api/client";
import type { Role, UserPayload } from "../types";
import { btnPrimary, card, errorText, fieldError, input, label } from "../ui";

const emptyForm: UserPayload = {
    email: "",
    firstName: "",
    lastName: "",
    roles: ["ROLE_USER"],
    isVerified: false,
    plainPassword: "",
};

export default function UserForm() {
    const { id } = useParams();
    const isEdit = Boolean(id);
    const navigate = useNavigate();

    const [form, setForm] = useState<UserPayload>(emptyForm);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(isEdit);

    useEffect(() => {
        if (!isEdit || !id) return;
        (async () => {
            try {
                const user = await api.getUser(id);
                setForm({
                    email: user.email ?? "",
                    firstName: user.firstName ?? "",
                    lastName: user.lastName ?? "",
                    roles: user.roles,
                    isVerified: user.isVerified,
                    plainPassword: "",
                });
            } catch (err) {
                if (err instanceof ApiError && err.status === 401) return navigate("/login");
            }
            setLoading(false);
        })();
    }, [id, isEdit]);

    const handleChange = (field: "email" | "firstName" | "lastName" | "plainPassword") => (e: React.ChangeEvent<HTMLInputElement>) => {
        setForm({ ...form, [field]: e.target.value });
    };

    const toggleRole = (role: Role) => (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.checked) {
            setForm({ ...form, roles: [...form.roles, role] });
        } else {
            setForm({ ...form, roles: form.roles.filter((r) => r !== role) });
        }
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        try {
            if (isEdit && id) {
                await api.updateUser(id, form);
            } else {
                await api.createUser(form);
            }
            navigate("/users");
        } catch (err) {
            if (err instanceof ApiError && err.status === 422 && err.body?.errors) {
                setErrors(err.body.errors);
            } else {
                setErrors({ global: "Une erreur est survenue." });
            }
        }
    };

    if (loading) return <p className="text-sm text-slate-500">Chargement...</p>;

    return (
        <div className="mx-auto max-w-xl">
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">{isEdit ? "Modifier l'utilisateur" : "Nouvel utilisateur"}</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Email</label>
                        <input type="email" className={input} value={form.email} onChange={handleChange("email")} />
                        {errors.email && <p className={fieldError}>{errors.email}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className={label}>Prénom</label>
                            <input type="text" className={input} value={form.firstName} onChange={handleChange("firstName")} />
                        </div>
                        <div>
                            <label className={label}>Nom</label>
                            <input type="text" className={input} value={form.lastName} onChange={handleChange("lastName")} />
                        </div>
                    </div>

                    <div>
                        <label className={label}>Rôles</label>
                        <div className="flex gap-4">
                            <label className="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" checked={form.roles.includes("ROLE_USER")} onChange={toggleRole("ROLE_USER")} />
                                Utilisateur
                            </label>
                            <label className="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" checked={form.roles.includes("ROLE_ADMIN")} onChange={toggleRole("ROLE_ADMIN")} />
                                Administrateur
                            </label>
                        </div>
                        {errors.roles && <p className={fieldError}>{errors.roles}</p>}
                    </div>

                    <div>
                        <label className={label}>{isEdit ? "Nouveau mot de passe (laisser vide pour ne pas changer)" : "Mot de passe"}</label>
                        <input
                            type="password"
                            className={input}
                            value={form.plainPassword}
                            onChange={handleChange("plainPassword")}
                            autoComplete="new-password"
                        />
                        {errors.plainPassword && <p className={fieldError}>{errors.plainPassword}</p>}
                    </div>

                    <label className="flex items-center gap-2 text-sm text-slate-700">
                        <input
                            type="checkbox"
                            checked={form.isVerified}
                            onChange={(e) => setForm({ ...form, isVerified: e.target.checked })}
                        />
                        Vérifié
                    </label>

                    <button type="submit" className={btnPrimary}>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>
    );
}
