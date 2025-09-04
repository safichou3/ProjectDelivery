import React from "react";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import LandingPage from "./pages/LandingPage";
import Header from "./components/Header";
import Footer from "./components/Footer";
import ChefPage from "./pages/ChefPage";
import MenuDishes from './pages/MenuDishes';
import DishDetail from "./pages/DishDetail";
import CartPage from "./pages/CartPage";
import PaymentPage from "./pages/PaymentPage";
import ThankYouPage from "./pages/ThankYouPage";
import AllChefsPage from "./pages/AllChefsPage";
import AllDishesPage from "./pages/AllDishesPage";
import UserLoginPopup from "./components/UserLoginPopup";
import UserRegisterPopup from "./components/UserRegisterPopup";
import ForgotPasswordPopup from "./components/ForgotPasswordPopup";
import VerificationPopup from "./components/verificationPopup";
import SettingPage from "./pages/profile/SettingPage";
import FavoritesPage from "./pages/profile/FavoritesPage";
import LocationsPage from "./pages/profile/LocationsPage";
import HelpPage from "./pages/profile/HelpPage";
import PrivacyPolicy from "./pages/PrivacyPolicy";
import CookiePolicy from "./pages/CookiePolicy";
import TermsConditions from "./pages/TermsConditions";
import NotFoundPage from "./pages/NotFoundPage";
import OrderHistoryPage from "./pages/profile/OrderHistoryPage";

function App() {
    return (
        <div className="app-wrapper">
        <Router>
            <Header />
            <main className="app-content">
            <Routes>
                <Route path="/" element={<LandingPage />} />
                <Route path="/chef/:id" element={<ChefPage />} />
                <Route path="/menu/:id" element={<MenuDishes />} />
                <Route path="/dish/:id" element={<DishDetail />} />
                <Route path="/cart" element={<CartPage />} />
                <Route path="/payment" element={<PaymentPage />} />
                <Route path="/thank-you" element={<ThankYouPage />} />
                <Route path="/chefs" element={<AllChefsPage />} />
                <Route path="/dishes" element={<AllDishesPage />} />
                <Route path="/profile/setting" element={<SettingPage />} />
                <Route path="/profile/favorites" element={<FavoritesPage />} />
                <Route path="/profile/locations" element={<LocationsPage />} />
                <Route path="/profile/help" element={<HelpPage />} />
                <Route path="/profile/order-history" element={<OrderHistoryPage />} />
                <Route path="/privacy-policy" element={<PrivacyPolicy />} />
                <Route path="/cookie-policy" element={<CookiePolicy />} />
                <Route path="/terms-conditions" element={<TermsConditions />} />
                <Route path="*" element={<NotFoundPage />} />
            </Routes>
            </main>
            <Footer />
            <UserLoginPopup />
            <UserRegisterPopup />
            <ForgotPasswordPopup />
            <VerificationPopup />
        </Router>
        </div>
    );
}

export default App;
