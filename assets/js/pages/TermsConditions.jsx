import React from "react";

export default function TermsConditions() {
    return (
        <div className="container py-5">
            <div className="text-center mb-5">
                <h1 className="fw-bold display-5 text-dark">
                    Conditions générales de vente et d’utilisation
                </h1>
                <p className="lead text-muted">Bienvenue sur <strong>ChefChezToi</strong> 👋</p>
                <p className="text-muted">
                    En utilisant notre site et en passant commande, vous acceptez les
                    conditions suivantes.
                </p>
            </div>

            <div className="bg-white shadow-sm rounded-4 p-4 p-md-5">
                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">1. Notre rôle</h3>
                    <p className="text-muted mb-0">
                        ChefChezToi est une plateforme en ligne mettant en relation des
                        clients et des restaurants partenaires. Nous facilitons la commande
                        et la livraison de repas, mais les plats sont préparés directement
                        par les restaurants.
                    </p>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">2. Commandes</h3>
                    <p className="text-muted mb-0">
                        Toute commande passée via le site est transmise au restaurant
                        partenaire. Une fois confirmée, la commande est ferme et définitive,
                        sauf accord exceptionnel du restaurant en cas d’annulation.
                    </p>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">3. Prix et paiement</h3>
                    <p className="text-muted mb-0">
                        Les prix affichés sont en euros, toutes taxes comprises (TTC). Le
                        paiement est effectué en ligne via{" "}
                        <strong>Stripe</strong>, un prestataire de paiement sécurisé agréé.
                        ChefChezToi ne conserve et n’a jamais accès à vos données bancaires.
                    </p>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">4. Livraison</h3>
                    <p className="text-muted mb-0">
                        La livraison est assurée par les restaurants partenaires ou leurs
                        livreurs. Les délais indiqués sont estimatifs et peuvent varier selon
                        la disponibilité, la circulation ou les conditions extérieures.
                    </p>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">5. Responsabilité</h3>
                    <p className="text-muted mb-0">
                        La qualité et la conformité des plats relèvent de la responsabilité
                        des restaurants. ChefChezToi agit comme intermédiaire. En cas de
                        problème (plat manquant, erreur, etc.), notre service client vous
                        accompagnera pour trouver une solution (remboursement, avoir, geste
                        commercial).
                    </p>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">6. Droit de rétractation</h3>
                    <p className="text-muted mb-0">
                        Conformément à l’article L221-28 du Code de la consommation, le droit
                        de rétractation ne s’applique pas à la fourniture de biens
                        périssables ou de repas préparés à la demande.
                    </p>
                </section>

                <section className="mb-4">
                    <h3 className="fw-semibold text-primary">7. Propriété intellectuelle</h3>
                    <p className="text-muted mb-0">
                        Tous les contenus présents sur le site (logos, textes, images,
                        interface) sont protégés par le droit d’auteur et le droit de la
                        propriété intellectuelle français. Toute reproduction sans
                        autorisation est interdite.
                    </p>
                </section>

                <section>
                    <h3 className="fw-semibold text-primary">8. Loi applicable et litiges</h3>
                    <p className="text-muted mb-0">
                        Les présentes conditions sont soumises au droit français. En cas de
                        litige, vous pouvez saisir un médiateur de la consommation ou les
                        tribunaux compétents.
                    </p>
                </section>
            </div>
        </div>
    );
}
