import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { addToCart } from "../utils/cartUtils";

export default function AllDishesPage() {
    const [dishes, setDishes] = useState([]);
    const [filteredDishes, setFilteredDishes] = useState([]);
    const [searchName, setSearchName] = useState("");
    const [minPrice, setMinPrice] = useState("");
    const [maxPrice, setMaxPrice] = useState("");
    const [loggedIn, setLoggedIn] = useState(!!localStorage.getItem("token"));
    const navigate = useNavigate();

    useEffect(() => {
        const now = new Date();
        const pad = (n) => String(n).padStart(2, "0");
        const dateTime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(
            now.getDate()
        )} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
        
        fetch(`/api/dishes?datetime=${encodeURIComponent(dateTime)}`, {
            headers: {
                "Accept": "application/json",
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error("Network error: " + res.status);
                return res.json();
            })
            .then(data => {
                setDishes(data);
                setFilteredDishes(data); // initialize filteredDishes
            })
            .catch(err => console.error("Error:", err));
    }, []);



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

    return (
        <div className="container my-5">
            <h2 className="mb-4">All Dishes</h2>

            {/* Filter Form */}
            <form className="d-flex align-items-center gap-2 mb-4" onSubmit={handleFilter}>
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

            <div className="row">
                {filteredDishes.length > 0 ? (
                    filteredDishes.map(dish => (
                        <div key={dish.id} className="col-md-6 col-lg-3 col-12 my-3 recipe-card" style={{ cursor: "pointer" }} onClick={() => navigate(`/dish/${dish.id}`)}>
                            <div className="card border-0 rounded-3 overflow-hidden aj-drop-shadow h-100">
                                <img className="card-img-top rounded-0" src={dish.image || "/meal.png"} alt={dish.name}/>
                                <div className="card-body py-2">
                                    <div className="row align-items-center">
                                        <div className="col-8">
                                            <h5 className="card-title body-text-small fw-bold mb-0 lh-2">
                                                {dish.name}
                                            </h5>
                                            <p className="body-text-extra-small mb-1">
                                                {dish.ingredients || ""}
                                            </p>
                                            <p className="body-text-extra-small fw-bold">€{dish.price}</p>
                                        </div>
                                        <div className="col-4 text-end">
                                            {/* Uncomment if you want to enable cart button */}
                                            {/* <button className="btn btn-primary aj-button body-text-small fw-700 px-2 py-2"
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    handleProtectedClick(() => addToCart(dish));
                                                }}
                                            >
                                                <i className="fi fi-sr-shopping-cart fs-5 lh-1 align-middle"></i>
                                            </button> */}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))
                ) : (
                    <p>No dishes match your filters.</p>
                )}
            </div>
        </div>
    );
}
