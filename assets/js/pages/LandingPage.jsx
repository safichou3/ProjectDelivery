import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { addToCart, getCart } from "../utils/cartUtils";


export default function LandingPage() {
    const [chefs, setChefs] = useState([]);
    const [dishes, setDishes] = useState([]);
    const navigate = useNavigate();

    const [loggedIn, setLoggedIn] = useState(!!localStorage.getItem("token"));

    useEffect(() => {
        const onAuthChanged = (e) => {
            setLoggedIn(!!localStorage.getItem("token"));
        };
        window.addEventListener('authChanged', onAuthChanged);
        return () => window.removeEventListener('authChanged', onAuthChanged);
    }, []);

    const handleProtectedClick = (callback) => {
        if (loggedIn) {
            callback();
        } else {
            const loginPopup = document.getElementById("userLoginPopup");
            if (loginPopup) {
                const modal = new window.bootstrap.Modal(loginPopup);
                modal.show();
            }
        }
    };

    useEffect(() => {
        const now = new Date();
        const pad = (n) => String(n).padStart(2, "0");
        const dateTime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(
            now.getDate()
        )} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
        fetch(`/api/chefs/available?datetime=${encodeURIComponent(dateTime)}`, {
            headers: {
                "Accept": "application/json",
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error("Network error: " + res.status);
                return res.json();
            })
            .then((data) => setChefs(data.slice(0, 4)))
            .catch((err) => console.error("Error fetching chefs:", err));
    }, []);




    useEffect(() => {
        fetch('/api/dishes')
            .then(res => res.json())
            .then(data => setDishes(data.slice(0, 4)))
            .catch(err => console.error("Error:", err));
    }, []);

    const handleLoginSuccess = (token) => {
        localStorage.setItem("token", token);
        setLoggedIn(true);
    };


    return (
        <>
            <section className="banner-section">
                <div className="container">
                    <div className="row pt-md-5 pt-3">
                        <div className="col-12 text-center my-4">
                            <h1 className="my-3 mb-4 text-white">
                                Explorez les saveurs de votre ville.
                            </h1>
                            <h4 className="mb-4 text-white">Street food, cuisine du monde ou plats traditionnels : livrés où vous êtes.</h4>
                            <button
                                className="btn btn-primary aj-button body-text-small fw-700 px-4"
                                onClick={() =>
                                    handleProtectedClick(() => navigate("/dishes"))
                                }
                            >
                                <i className="fi fi-rr-box-open-full me-2 fs-5 lh-1 align-middle"></i>
                                {loggedIn ? "Explorer les saveurs" : "Connexion"}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section className="content-section container my-4">
                <div className="row align-items-center">
                    <div className="col-md-7 col-12 my-3">
                        <h3 className="mb-3">Un voyage culinaire sans quitter votre maison  </h3>
                        <p>Envie d’un ramen japonais, d’un couscous marocain ou d’une pizza artisanale ? Avec nous, passez d’un continent à l’autre en quelques clics.</p>
                    </div>
                    <div className="col-md-5 col-12 my-3">
                        <img className="rounded-4 w-100 meal-image-div" />
                    </div>
                </div>
            </section>

            <section className="recipe-section my-5">
                <div className="container">
                    <div className="row">
                        <div className="col-md-8 col-12 my-auto">
                            <h3>Nos chefs</h3>
                        </div>
                        <div className="col-md-4 col-12 text-md-end mt-md-0 mt-3">
                            <button
                                className="btn btn-primary aj-button body-text-small fw-700 px-4"
                                onClick={() =>
                                    handleProtectedClick(() => navigate("/chefs"))
                                }
                            >
                                <i className="fi fi-rr-box-open-full me-2 fs-5 lh-1 align-middle"></i>
                                Tous voir
                            </button>
                        </div>
                    </div>
                    <div className="row mt-3">
                        {chefs.map((chef) => (
                            <div
                                className="col-md-6 col-lg-3 col-12 my-3 recipe-card"
                                key={chef.id}
                                role="button"
                                tabIndex={0}
                                style={{ cursor: "pointer" }}
                                onClick={() => handleProtectedClick(() => navigate(`/chef/${chef.id}`, { state: { chef } }))}
                            >
                                <div className="card border-0 rounded-3 overflow-hidden aj-drop-shadow">
                                    <img
                                        className="card-img-top rounded-0"
                                        src={chef.image || "/meal.png"}
                                        alt={chef.name}
                                    />
                                    <div className="card-body py-2">
                                        <div className="row align-items-center">
                                            <div className="col-8">
                                                <h5 className="card-title body-text-small fw-bold mb-0 lh-2">
                                                    {chef.name}
                                                </h5>
                                                <p className="card-text body-text-extra-small mb-1">
                                                    {chef.certification || "Certification not available"}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            <section className="my-5 container">
                <div className="cta-section row py-md-5 py-3 mx-0 px-0 rounded-4">
                    <div className="col-12 text-center my-auto py-3 py-md-5">
                        <h2>Voyagez depuis votre assiette</h2>
                        <p>Un seul clic et vous passez du Mexique au Japon, de l’Italie au Maroc.</p>
                        <button
                            className="btn btn-primary aj-button body-text-small fw-700 px-4"
                            onClick={() =>
                                handleProtectedClick(() => navigate("/dishes"))
                            }
                        >
                            <i className="fi fi-rr-box-open-full me-2 fs-5 lh-1 align-middle"></i>
                            {loggedIn ? "Voyager" : "Connexion"}
                        </button>
                    </div>
                </div>
            </section>

            <section className="content-section container my-4">
                <div className="row align-items-center">
                    <div className="col-md-5 col-12 my-3 ">
                        <img className="rounded-4 w-100 meal-image-div" />
                    </div>
                    <div className="col-md-7 col-12 my-3">
                        <h3 className="mb-3">Un savoir-faire à découvrir </h3>
                        <p>Derrière chaque plat se cache un restaurant passionné. Notre mission : vous aider à découvrir des adresses proches de chez vous, parfois discrètes, toujours délicieuses. </p>
                    </div>
                </div>
            </section>

            <section className="recipe-section my-5">
                <div className="container">
                    <div className="row">
                        <div className="col-md-8 col-12 my-auto">
                            <h3>Nouveautés : viens vite les découvrir !</h3>
                        </div>
                        <div className="col-md-4 col-12 text-md-end mt-md-0 mt-3">
                            <button
                                className="btn btn-primary aj-button body-text-small fw-700 px-4"
                                onClick={() =>
                                    handleProtectedClick(() => navigate("/dishes"))
                                }
                            >
                                <i className="fi fi-rr-box-open-full me-2 fs-5 lh-1 align-middle"></i>
                                Tous nos plats
                            </button>
                        </div>
                    </div>
                    <div className="row mt-3">
                        {dishes.map((dish) => (
                                <div key={dish.id} className="col-md-6 col-lg-3 col-12 my-3 recipe-card" style={{ cursor: "pointer" }} onClick={() => handleProtectedClick(() => navigate(`/dish/${dish.id}`))}
                                >
                                <div className="card border-0 rounded-3 overflow-hidden aj-drop-shadow">
                                    <img
                                        className="card-img-top rounded-0"
                                        src={dish.image || "/meal.png"}
                                        alt={dish.name}
                                    />
                                    <div className="card-body py-2">
                                        <div className="row align-items-center">
                                            <div className="col-8">
                                                <h5 className="card-title body-text-small fw-bold mb-0 lh-2">
                                                    {dish.name}
                                                </h5>
                                                <p className="card-text body-text-extra-small mb-1">
                                                    {dish.ingredients || ""}
                                                </p>
                                                <p className="card-text body-text-extra-small fw-bold">
                                                    €{dish.price}
                                                </p>
                                            </div>
                                            <div className="col-4 text-end">
                                                {/*<button className="btn btn-primary aj-button body-text-small fw-700 px-2 py-2"*/}
                                                {/*    onClick={(e) => {*/}
                                                {/*        e.stopPropagation();*/}
                                                {/*        handleProtectedClick(() => addToCart(dish));*/}
                                                {/*    }}*/}
                                                {/*>*/}
                                                {/*    <i className="fi fi-sr-shopping-cart fs-5 lh-1 align-middle"></i>*/}
                                                {/*</button>*/}
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            <section className="my-5 container">
                <div className="cta-section row py-md-5 py-3 mx-0 px-0 rounded-4">
                    <div className="col-12 text-center my-auto py-3 py-md-5">
                        <h2>Explorez, dégustez, soutenez : </h2>
                        <p>Votre ville regorge de saveurs, venez les découvrir ! </p>
                        <button
                            className="btn btn-primary aj-button body-text-small fw-700 px-4"
                            onClick={() =>
                                handleProtectedClick(() => navigate("/dishes"))
                            }
                        >
                            <i className="fi fi-rr-box-open-full me-2 fs-5 lh-1 align-middle"></i>
                            {loggedIn ? "Explorer" : "Connexion"}
                        </button>
                    </div>
                </div>
            </section>
        </>
    );
}
