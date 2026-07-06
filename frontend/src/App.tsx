import { Link, Route, Routes, useNavigate } from "react-router-dom";
import ProduitList from "./pages/ProduitList";
import ProduitForm from "./pages/ProduitForm";
import UniteList from "./pages/UniteList";
import UniteForm from "./pages/UniteForm";
import UniteShow from "./pages/UniteShow";
import CategorieList from "./pages/CategorieList";
import CategorieForm from "./pages/CategorieForm";
import CategorieShow from "./pages/CategorieShow";
import FournisseurList from "./pages/FournisseurList";
import FournisseurForm from "./pages/FournisseurForm";
import FournisseurShow from "./pages/FournisseurShow";
import UserList from "./pages/UserList";
import UserForm from "./pages/UserForm";
import TypeClientList from "./pages/TypeClientList";
import TypeClientForm from "./pages/TypeClientForm";
import TypeClientShow from "./pages/TypeClientShow";
import ClientList from "./pages/ClientList";
import ClientForm from "./pages/ClientForm";
import ClientShow from "./pages/ClientShow";
import StockIndex from "./pages/StockIndex";
import StockDashboard from "./pages/StockDashboard";
import StockMouvements from "./pages/StockMouvements";
import StockHistoriqueProduit from "./pages/StockHistoriqueProduit";
import StockEntree from "./pages/StockEntree";
import StockSortie from "./pages/StockSortie";
import StockAjustement from "./pages/StockAjustement";
import PromotionList from "./pages/PromotionList";
import PromotionForm from "./pages/PromotionForm";
import PromotionShow from "./pages/PromotionShow";
import VenteList from "./pages/VenteList";
import VenteForm from "./pages/VenteForm";
import VenteShow from "./pages/VenteShow";
import ConversionStandardList from "./pages/ConversionStandardList";
import ConversionStandardForm from "./pages/ConversionStandardForm";
import DocumentationList from "./pages/DocumentationList";
import DocumentationShow from "./pages/DocumentationShow";
import Home from "./pages/Home";
import Login from "./pages/Login";
import Register from "./pages/Register";
import VerifyEmail from "./pages/VerifyEmail";
import RequireAuth from "./components/RequireAuth";
import { auth } from "./api/auth";

function App() {
    const navigate = useNavigate();

    const handleLogout = () => {
        auth.clearToken();
        navigate("/login");
    };

    return (
        <div id="app" className="min-h-screen bg-slate-50">
            {auth.isAuthenticated() && (
                <nav className="flex items-center gap-6 border-b border-slate-200 bg-white px-6 py-3">
                    <Link to="/" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Accueil
                    </Link>
                    <Link to="/produits" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Produits
                    </Link>
                    <Link to="/unites" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Unités
                    </Link>
                    <Link to="/categories" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Catégories
                    </Link>
                    <Link to="/fournisseurs" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Fournisseurs
                    </Link>
                    <Link to="/users" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Utilisateurs
                    </Link>
                    <Link to="/clients" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Clients
                    </Link>
                    <Link to="/type-clients" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Types de client
                    </Link>
                    <Link to="/stock" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Stock
                    </Link>
                    <Link to="/ventes" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Ventes
                    </Link>
                    <Link to="/promotions" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Promotions
                    </Link>
                    <Link to="/conversion-standards" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Conversions
                    </Link>
                    <Link to="/documentation" className="text-sm font-semibold text-slate-700 hover:text-indigo-600">
                        Documentation
                    </Link>
                    <button onClick={handleLogout} className="ml-auto text-sm font-medium text-slate-500 hover:text-red-600">
                        Se déconnecter
                    </button>
                </nav>
            )}

            <main className="mx-auto max-w-5xl px-6 py-8">
                <Routes>
                    <Route
                        path="/"
                        element={
                            <RequireAuth>
                                <Home />
                            </RequireAuth>
                        }
                    />
                    <Route path="/login" element={<Login />} />
                    <Route path="/register" element={<Register />} />
                    <Route path="/verify-email" element={<VerifyEmail />} />
                    <Route
                        path="/produits"
                        element={
                            <RequireAuth>
                                <ProduitList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/produits/new"
                        element={
                            <RequireAuth>
                                <ProduitForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/produits/:id/edit"
                        element={
                            <RequireAuth>
                                <ProduitForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/unites"
                        element={
                            <RequireAuth>
                                <UniteList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/unites/new"
                        element={
                            <RequireAuth>
                                <UniteForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/unites/:id"
                        element={
                            <RequireAuth>
                                <UniteShow />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/unites/:id/edit"
                        element={
                            <RequireAuth>
                                <UniteForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/categories"
                        element={
                            <RequireAuth>
                                <CategorieList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/categories/new"
                        element={
                            <RequireAuth>
                                <CategorieForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/categories/:id"
                        element={
                            <RequireAuth>
                                <CategorieShow />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/categories/:id/edit"
                        element={
                            <RequireAuth>
                                <CategorieForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/fournisseurs"
                        element={
                            <RequireAuth>
                                <FournisseurList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/fournisseurs/new"
                        element={
                            <RequireAuth>
                                <FournisseurForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/fournisseurs/:id"
                        element={
                            <RequireAuth>
                                <FournisseurShow />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/fournisseurs/:id/edit"
                        element={
                            <RequireAuth>
                                <FournisseurForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/users"
                        element={
                            <RequireAuth>
                                <UserList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/users/new"
                        element={
                            <RequireAuth>
                                <UserForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/users/:id/edit"
                        element={
                            <RequireAuth>
                                <UserForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/clients"
                        element={
                            <RequireAuth>
                                <ClientList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/clients/new"
                        element={
                            <RequireAuth>
                                <ClientForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/clients/:id"
                        element={
                            <RequireAuth>
                                <ClientShow />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/clients/:id/edit"
                        element={
                            <RequireAuth>
                                <ClientForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/type-clients"
                        element={
                            <RequireAuth>
                                <TypeClientList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/type-clients/new"
                        element={
                            <RequireAuth>
                                <TypeClientForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/type-clients/:id"
                        element={
                            <RequireAuth>
                                <TypeClientShow />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/type-clients/:id/edit"
                        element={
                            <RequireAuth>
                                <TypeClientForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/stock"
                        element={
                            <RequireAuth>
                                <StockIndex />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/stock/dashboard"
                        element={
                            <RequireAuth>
                                <StockDashboard />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/stock/mouvements"
                        element={
                            <RequireAuth>
                                <StockMouvements />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/stock/entree"
                        element={
                            <RequireAuth>
                                <StockEntree />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/stock/sortie"
                        element={
                            <RequireAuth>
                                <StockSortie />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/stock/ajustement"
                        element={
                            <RequireAuth>
                                <StockAjustement />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/stock/produits/:id"
                        element={
                            <RequireAuth>
                                <StockHistoriqueProduit />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/promotions"
                        element={
                            <RequireAuth>
                                <PromotionList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/promotions/new"
                        element={
                            <RequireAuth>
                                <PromotionForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/promotions/:id"
                        element={
                            <RequireAuth>
                                <PromotionShow />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/promotions/:id/edit"
                        element={
                            <RequireAuth>
                                <PromotionForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/ventes"
                        element={
                            <RequireAuth>
                                <VenteList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/ventes/new"
                        element={
                            <RequireAuth>
                                <VenteForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/ventes/:id"
                        element={
                            <RequireAuth>
                                <VenteShow />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/ventes/:id/edit"
                        element={
                            <RequireAuth>
                                <VenteForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/conversion-standards"
                        element={
                            <RequireAuth>
                                <ConversionStandardList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/conversion-standards/new"
                        element={
                            <RequireAuth>
                                <ConversionStandardForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/conversion-standards/:id/edit"
                        element={
                            <RequireAuth>
                                <ConversionStandardForm />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/documentation"
                        element={
                            <RequireAuth>
                                <DocumentationList />
                            </RequireAuth>
                        }
                    />
                    <Route
                        path="/documentation/:slug"
                        element={
                            <RequireAuth>
                                <DocumentationShow />
                            </RequireAuth>
                        }
                    />
                </Routes>
            </main>
        </div>
    );
}

export default App;
