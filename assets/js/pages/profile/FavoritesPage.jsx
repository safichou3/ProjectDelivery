import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { apiUrl } from "../../utils/env";
import ProfileSidebar from "../../components/ProfileSidebar";

export default function FavoritesPage() {
    const navigate = useNavigate();
    const [favorites, setFavorites] = useState([]);

    useEffect(() => {
        const token = localStorage.getItem("token");
        if (!token) return;

        const fetchFavorites = async () => {
            try {
                const res = await fetch(apiUrl("/user/favorites"), {
                    headers: { Authorization: "Bearer " + token }
                });
                if (res.ok) {
                    const data = await res.json();
                    setFavorites(data);
                }
            } catch (err) {
                console.error(err);
            }
        };

        fetchFavorites();
    }, []);



    return (
        <div className="container my-md-5 py-md-5 my-3 py-3">
            <div className="col-12 mb-4">
                <h2 className="text-center">Favorites</h2>
            </div>
            <div className="row">
                <div className="col-md-4 col-12 mb-md-0 mb-4">
                    <ProfileSidebar />
                </div>

                <div className="col-md-8 col-12">
                    <div className="aj-drop-shadow background-white rounded-4 p-md-4 p-3">
                        <h3 className="mb-4">Favorite Dishes</h3>

                        {favorites.length === 0 ? (
                            <p className="text-center">You have no favorite dishes yet.</p>
                        ) : (
                            <div className="row">
                                {favorites.map((dish) => (
                                    <div className="col-md-6 col-12 mb-3" key={dish.id}>
                                        <div
                                            className="card aj-drop-shadow rounded-3 overflow-hidden"
                                            onClick={() => navigate(`/dish/${dish.id}`)}
                                            style={{ cursor: "pointer" }}
                                        >
                                            <img
                                                src={dish.image || "/meal.png"}
                                                alt={dish.name}
                                                className="w-100"
                                                style={{ height: "180px", objectFit: "cover" }}
                                            />
                                            <div className="p-3 d-flex justify-content-between align-items-center">
                                                <h5 className="fw-bold mb-0">{dish.name}</h5>
                                                <p className="fw-bold mb-0">${dish.price}</p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
