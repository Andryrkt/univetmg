import { Link, useSearchParams } from "react-router-dom";
import { card, link } from "../ui";

export default function VerifyEmail() {
    const [searchParams] = useSearchParams();
    const status = searchParams.get("status");
    const message = searchParams.get("message");

    return (
        <div className="mx-auto max-w-sm">
            <h1 className="mb-6 text-2xl font-semibold text-slate-900">Vérification de l'email</h1>

            <div className={card}>
                {status === "success" ? (
                    <p className="text-sm text-green-700">Votre adresse email a été vérifiée avec succès.</p>
                ) : (
                    <p className="text-sm text-red-600">{message ?? "Le lien de vérification est invalide ou a expiré."}</p>
                )}

                <p className="mt-4 text-center text-sm">
                    <Link to="/login" className={link}>
                        Retour à la connexion
                    </Link>
                </p>
            </div>
        </div>
    );
}
