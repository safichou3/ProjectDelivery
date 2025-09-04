import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";

export default function MenuDishes() {
    const { id } = useParams();
    const [dishes, setDishes] = useState([]);
    const [filteredDishes, setFilteredDishes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchName, setSearchName] = useState("");
    const [minPrice, setMinPrice] = useState("");
    const [maxPrice, setMaxPrice] = useState("");
    const navigate = useNavigate();

    useEffect(() => {
        fetch(`/api/menus/${id}/dishes`)
            .then((res) => res.json())
            .then((data) => {
                setDishes(data);
                setFilteredDishes(data);
                setLoading(false);
            })
            .catch((err) => {
                setLoading(false);
            });
    }, [id]);

    const handleFilter = (e) => {
        e.preventDefault();
        let filtered = [...dishes];

        if (searchName.trim() !== "") {
            filtered = filtered.filter(dish =>
                dish.name.toLowerCase().includes(searchName.toLowerCase())
            );
        }

        if (minPrice !== "") {
            filtered = filtered.filter(dish => parseFloat(dish.price) >= parseFloat(minPrice));
        }

        if (maxPrice !== "") {
            filtered = filtered.filter(dish => parseFloat(dish.price) <= parseFloat(maxPrice));
        }

        setFilteredDishes(filtered);
    };

    if (loading) {
        return (
            <div
                role="status"
                aria-live="polite"
                aria-label="Loading"
                className="loading-overlay"
            >
                <span className="spinner" aria-hidden="true"></span>
                <span className="sr-only">Loading…</span>
            </div>
        );
    }

    return (
        <>
            <section className="banner-section mh-0">
                <div className="container py-4">
                    <div className="row py-5">
                        <div className="col-12 text-center my-4">
                            <h1 className="my-3 mb-4 text-white">
                                Menu #{id} Dishes
                            </h1>
                            <h4 className="mb-4 text-white">
                                Enjoy our carefully crafted menu selection
                            </h4>
                        </div>
                    </div>
                </div>
            </section>

            <section className="recipe-section my-5">
                <div className="container">
                    <div className="row align-items-center">
                        <div className="col-md-8 col-12 my-auto">
                            <form className="d-flex align-items-center gap-2" onSubmit={handleFilter}>
                                <input
                                    type="text"
                                    placeholder="Search by name"
                                    className="form-control"
                                    value={searchName}
                                    onChange={(e) => setSearchName(e.target.value)}
                                />
                                <input
                                    type="number"
                                    placeholder="Min Price"
                                    className="form-control"
                                    value={minPrice}
                                    onChange={(e) => setMinPrice(e.target.value)}
                                />
                                <input
                                    type="number"
                                    placeholder="Max Price"
                                    className="form-control"
                                    value={maxPrice}
                                    onChange={(e) => setMaxPrice(e.target.value)}
                                />
                                <button type="submit" className="btn btn-primary aj-button body-text-small fw-700 px-4">
                                    Filter
                                </button>
                            </form>
                        </div>
                        <div className="col-md-4 col-12 text-md-end mt-md-0 mt-3">
                            <p className="body-text-small mb-0">
                                Showing {filteredDishes.length} Product{filteredDishes.length !== 1 && "s"}
                            </p>
                        </div>
                    </div>

                    <div className="row mt-3">
                        {filteredDishes.length > 0 ? (
                            filteredDishes.map((dish) => (
                                <div
                                    key={dish.id}
                                    className="col-md-6 col-lg-3 col-12 my-3 recipe-card"
                                    style={{ cursor: "pointer" }}
                                    onClick={() => navigate(`/dish/${dish.id}`)}
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
                                                        {dish.description?.length > 60
                                                            ? `${dish.description.slice(0, 60)}[...]`
                                                            : dish.description}
                                                    </p>
                                                    <p className="card-text body-text-extra-small fw-bold">
                                                        €{dish.price || "N/A"}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <p>No dishes match your filters.</p>
                        )}

                        <div className="col-12 mt-4">
                            <div className="d-flex align-items-center gap-2 justify-content-center">
                                <div className="d-flex align-items-center gap-2 justify-content-center">
                                    {Array.from({ length: Math.ceil(dishes.length / 10) }, (_, index) => (
                                        <button
                                            key={index}
                                            className={`btn btn-primary aj-button body-text-small fw-700 px-3 py-2 ${
                                                index === 0 ? '' : 'btn-transparent'
                                            }`}
                                        >
                                            {index + 1}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="my-5 container">
                <div className="cta-section row py-md-5 py-3 mx-0 px-0 rounded-4">
                    <div className="col-12 text-center my-auto py-3 py-md-5">
                        <h2>Explorez, dégustez, soutenez :</h2>
                        <p>
                            Votre ville regorge de saveurs, venez les découvrir !
                        </p>
                    </div>
                </div>
            </section>
        </>
    );
}
