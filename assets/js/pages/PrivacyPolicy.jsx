import React from "react";

export default function PrivacyPolicy() {
    return (
        <div className="container py-5">
            <div className="text-center mb-5">
                <h1 className="fw-bold display-5 text-dark">
                    Politique de confidentialité – ChefChezToi
                </h1>
                <p className="lead text-muted">
                    Chez <strong>ChefChezToi</strong>, la protection de vos données personnelles est une priorité.
                </p>
            </div>
            <div className="bg-white shadow-sm rounded-4 p-4 p-md-5">
                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">1. Données collectées</h3>
                    <p className="text-muted">Nous collectons les données suivantes :</p>
                    <ul className="text-muted">
                        <li>Informations de compte : nom, prénom, e-mail, téléphone, adresse de livraison.</li>
                        <li>Historique de commandes et préférences.</li>
                        <li>Données de paiement (gérées uniquement par notre prestataire Stripe).</li>
                    </ul>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">2. Finalités du traitement</h3>
                    <p className="text-muted">Nous utilisons vos données pour :</p>
                    <ul className="text-muted">
                        <li>Traiter, gérer et livrer vos commandes.</li>
                        <li>Améliorer nos services et personnaliser votre expérience.</li>
                        <li>Vous informer sur nos offres commerciales si vous y avez consenti.</li>
                    </ul>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">3. Partage des données</h3>
                    <p className="text-muted">Vos données peuvent être transmises à :</p>
                    <ul className="text-muted">
                        <li>Nos restaurants partenaires (pour préparer vos commandes).</li>
                        <li>Nos livreurs partenaires (pour la livraison).</li>
                        <li>
                            Notre prestataire de paiement <strong>Stripe</strong>, qui assure un traitement
                            sécurisé et conforme des données bancaires. ChefChezToi n’a jamais accès aux informations complètes de votre carte bancaire.
                        </li>
                        <li>Nos prestataires techniques (hébergement, maintenance).</li>
                    </ul>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">4. Conservation des données</h3>
                    <p className="text-muted mb-0">
                        Vos données personnelles sont conservées uniquement le temps nécessaire à la gestion de vos commandes
                        et à nos obligations légales (facturation, comptabilité).
                    </p>
                </section>

                <section>
                    <h3 className="fw-semibold text-primary">5. Vos droits (RGPD)</h3>
                    <p className="text-muted">
                        Conformément au RGPD et à la loi Informatique et Libertés, vous disposez des droits suivants :
                    </p>
                    <ul className="text-muted">
                        <li>Droit d’accès, de rectification et de suppression.</li>
                        <li>Droit à la limitation ou à l’opposition du traitement.</li>
                        <li>Droit à la portabilité des données.</li>
                    </ul>
                    <p className="text-muted mb-0">
                        Pour exercer vos droits, contactez-nous à :{" "}
                        <a href="mailto:support@chefcheztoi.fr">support@chefcheztoi.fr</a>
                    </p>
                </section>
            </div>
        </div>
    );
}
