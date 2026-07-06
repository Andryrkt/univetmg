import { useState, type FormEvent } from "react";
import { Link } from "react-router-dom";
import { api, ApiError } from "../api/client";
import { btnPrimary, card, errorText, fieldError, input, label, link } from "../ui";

export default function Register() {
    const [form, setForm] = useState({ email: "", password: "", firstName: "", lastName: "" });
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [message, setMessage] = useState<string | null>(null);

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setErrors({});
        setMessage(null);
        try {
            const res = await api.register(form);
            setMessage(res.message);
        } catch (err) {
            if (err instanceof ApiError && err.status === 422 && err.body?.errors) {
                setErrors(err.body.errors);
            } else {
                setErrors({ global: "Une erreur est survenue." });
            }
        }
    };

    if (message) {
        return (
            <div className="mx-auto max-w-sm">
                <h1 className="mb-6 text-2xl font-semibold text-slate-900">Inscription</h1>
                <div className={card}>
                    <p className="text-sm text-slate-700">{message}</p>
                    <p className="mt-4 text-center text-sm">
                        <Link to="/login" className={link}>
                            Retour à la connexion
                        </Link>
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="mx-auto max-w-sm">
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">Inscription</h1>

            <div className={card}>
                {errors.global && <p className={`${errorText} mb-4`}>{errors.global}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Email</label>
                        <input
                            type="email"
                            className={input}
                            value={form.email}
                            onChange={(e) => setForm({ ...form, email: e.target.value })}
                            required
                        />
                        {errors.email && <p className={fieldError}>{errors.email}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className={label}>Prénom</label>
                            <input
                                type="text"
                                className={input}
                                value={form.firstName}
                                onChange={(e) => setForm({ ...form, firstName: e.target.value })}
                            />
                        </div>
                        <div>
                            <label className={label}>Nom</label>
                            <input
                                type="text"
                                className={input}
                                value={form.lastName}
                                onChange={(e) => setForm({ ...form, lastName: e.target.value })}
                            />
                        </div>
                    </div>

                    <div>
                        <label className={label}>Mot de passe</label>
                        <input
                            type="password"
                            className={input}
                            value={form.password}
                            onChange={(e) => setForm({ ...form, password: e.target.value })}
                            autoComplete="new-password"
                            required
                        />
                        {errors.password && <p className={fieldError}>{errors.password}</p>}
                    </div>

                    <button type="submit" className={`${btnPrimary} w-full`}>
                        S'inscrire
                    </button>
                </form>

                <p className="mt-4 text-center text-sm text-slate-600">
                    Déjà un compte ?{" "}
                    <Link to="/login" className={link}>
                        Se connecter
                    </Link>
                </p>
            </div>
        </div>
    );
}
