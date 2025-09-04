import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { apiUrl } from "../utils/env";

export default function ProfileSidebar() {
    const navigate = useNavigate();
    const [userRole, setUserRole] = useState("");

    useEffect(() => {
        fetchUserRole();
    }, []);

    const fetchUserRole = async () => {
        try {
            const response = await fetch(apiUrl("/user"), {
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                },
            });
            if (response.ok) {
                const data = await response.json();
                const roleFromAPI = data.role || "";
                const roleFromStorage = localStorage.getItem("role") || "";
                const finalRole = roleFromAPI || roleFromStorage;
                setUserRole(finalRole);
            }
        } catch (err) {
            console.error(err);
        }
    };

    const handleLogout = () => {
        localStorage.removeItem("token");
        navigate("/");
    };

    return (
        <div className="aj-drop-shadow background-white rounded-4 p-md-4 p-3">
            <Link to="/profile/setting" className="btn btn-transparent aj-button d-flex justify-content-between body-text-small fw-700 px-3 w-100 my-3">
                Settings <i className="fi fi-rr-angle-small-right ms-2 fs-5 lh-1 align-middle"></i>
            </Link>
            {userRole === "chef" && (
                <>
                    <Link to="/profile/help" className="btn btn-transparent aj-button d-flex justify-content-between body-text-small fw-700 px-3 w-100 my-3">
                        Help <i className="fi fi-rr-angle-small-right ms-2 fs-5 lh-1 align-middle"></i>
                    </Link>
                </>
            )}
            
            {userRole !== "admin" && userRole !== "chef" && (
                <>
                    <Link to="/profile/order-history" className="btn btn-transparent aj-button d-flex justify-content-between body-text-small fw-700 px-3 w-100 my-3">
                        Order History <i className="fi fi-rr-angle-small-right ms-2 fs-5 lh-1 align-middle"></i>
                    </Link>
                    <Link to="/profile/favorites" className="btn btn-transparent aj-button d-flex justify-content-between body-text-small fw-700 px-3 w-100 my-3">
                        Favorites <i className="fi fi-rr-angle-small-right ms-2 fs-5 lh-1 align-middle"></i>
                    </Link>
                    <Link to="/profile/help" className="btn btn-transparent aj-button d-flex justify-content-between body-text-small fw-700 px-3 w-100 my-3">
                        Help <i className="fi fi-rr-angle-small-right ms-2 fs-5 lh-1 align-middle"></i>
                    </Link>
                </>
            )}
            
            <Link to="/profile/locations" className="btn btn-transparent aj-button d-flex justify-content-between body-text-small fw-700 px-3 w-100 my-3">
                Locations <i className="fi fi-rr-angle-small-right ms-2 fs-5 lh-1 align-middle"></i>
            </Link>
            
            <button type="button" className="btn btn-primary aj-button body-text-small fw-700 px-3 w-100 my-2" onClick={handleLogout}>
                Logout
            </button>
        </div>
    );
}
