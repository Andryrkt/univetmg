import { useState, type FormEvent } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api } from "../api/client";
import { btnPrimary, card, errorText, input, label, link } from "../ui";

export default function Login() {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState<string | null>(null);
    const navigate = useNavigate();

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setError(null);
        try {
            await api.login(email, password);
            navigate("/produits");
        } catch {
            setError("Email ou mot de passe incorrect.");
        }
    };

    return (
        <div className="mx-auto max-w-sm">
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">Connexion</h1>

            <div className={card}>
                {error && <p className={`${errorText} mb-4`}>{error}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className={label}>Email</label>
                        <input type="email" className={input} value={email} onChange={(e) => setEmail(e.target.value)} required />
                    </div>
                    <div>
                        <label className={label}>Mot de passe</label>
                        <input
                            type="password"
                            className={input}
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                        />
                    </div>
                    <button type="submit" className={`${btnPrimary} w-full`}>
                        Se connecter
                    </button>
                </form>

                <p className="mt-4 text-center text-sm text-slate-600">
                    Pas encore de compte ?{" "}
                    <Link to="/register" className={link}>
                        S'inscrire
                    </Link>
                </p>
            </div>
        </div>
    );
}
