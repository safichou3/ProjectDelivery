import React from "react";

export default function CookiePolicy() {
    return (
        <div className="container py-5">
            <div className="text-center mb-5">
                <h1 className="fw-bold display-5 text-dark">
                    Politique des cookies – ChefChezToi
                </h1>
                <p className="lead text-muted">
                    Lors de votre navigation sur notre site, des cookies peuvent être déposés sur votre appareil.
                </p>
            </div>
            <div className="bg-white shadow-sm rounded-4 p-4 p-md-5">
                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">1. Qu’est-ce qu’un cookie ?</h3>
                    <p className="text-muted">
                        Un cookie est un petit fichier texte stocké sur votre appareil, permettant de mémoriser des informations liées à votre navigation.
                    </p>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">2. Types de cookies utilisés</h3>
                    <ul className="text-muted">
                        <li>
                            <strong>Cookies essentiels :</strong> nécessaires au fonctionnement du site (connexion, panier, commande).
                        </li>
                        <li>
                            <strong>Cookies analytiques :</strong> pour analyser la fréquentation et améliorer nos services (par ex. Google Analytics).
                        </li>
                        <li>
                            <strong>Cookies publicitaires :</strong> pour personnaliser nos offres, uniquement si vous les acceptez.
                        </li>
                    </ul>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">3. Consentement</h3>
                    <p className="text-muted">
                        Lors de votre première visite, un bandeau vous informe de l’utilisation des cookies.
                        Vous pouvez accepter, refuser ou personnaliser vos choix.
                    </p>
                </section>

                <section>
                    <h3 className="fw-semibold text-primary">4. Gestion des cookies</h3>
                    <p className="text-muted mb-0">
                        Vous pouvez modifier vos préférences à tout moment via notre outil de gestion des cookies
                        ou directement dans les paramètres de votre navigateur.
                    </p>
                </section>
            </div>
        </div>
    );
}
