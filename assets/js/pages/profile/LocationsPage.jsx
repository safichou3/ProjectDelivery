import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { apiUrl } from "../../utils/env";
import ProfileSidebar from "../../components/ProfileSidebar";

export default function LocationPage() {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        country: "",
        address: "",
        city: "",
        postalCode: ""
    });
    const [success, setSuccess] = useState(null);

    const token = localStorage.getItem("token");
    const fetchUserData = async () => {
        if (!token) return;
        try {
            const response = await fetch(apiUrl("/user"), {
                headers: { "Authorization": "Bearer " + token }
            });
            if (response.ok) {
                const data = await response.json();

                setFormData({
                    country: data.country || "",
                    address: data.address || "",
                    city: data.city || "",
                    postalCode: data.postalCode || ""
                });
            }
        } catch (error) {
            console.error("Error fetching user data:", error);
        }
    };

    useEffect(() => {
        fetchUserData();
    }, []);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const handleSave = async (e) => {
        e.preventDefault();
        try {
            const response = await fetch(apiUrl("/user/location"), {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": "Bearer " + token
                },
                body: JSON.stringify(formData)
            });
            if (response.ok) {
                const data = await response.json();
                setSuccess(data.message);
            } else {
                setSuccess("Failed to update location!");
            }
        } catch (error) {
            setSuccess("Error updating location!");
        }
    };



    return (
        <div className="container my-md-5 py-md-5 my-3 py-3">
            <form className="row" onSubmit={handleSave}>
                <div className="col-12 mb-4">
                    <h2 className="text-center">Manage Location</h2>
                </div>

                <div className="col-12">
                    <div className="row">
                        <div className="col-md-4 col-12 mb-md-0 mb-4">
                            <ProfileSidebar />
                        </div>

                        <div className="col-md-8 col-12">
                            <div className="aj-drop-shadow background-white rounded-4 p-md-4 p-3">
                                <h3 className="mb-4">Location</h3>
                                {success && <div className="alert alert-info">{success}</div>}
                                <input type="text" id="country" name="country" placeholder="Country" className="form-control my-3" value={formData.country} onChange={handleChange}/>
                                <input type="text" id="address" name="address" placeholder="Address" className="form-control my-3" value={formData.address} onChange={handleChange}/>
                                <div className="row">
                                    <div className="col-md-6 col-12">
                                        <input type="text" id="city" name="city" placeholder="City" className="form-control my-3" value={formData.city} onChange={handleChange}/>
                                    </div>
                                    <div className="col-md-6 col-12">
                                        <input type="text" id="postalCode" name="postalCode" placeholder="Postal Code" className="form-control my-3" value={formData.postalCode} onChange={handleChange}/>
                                    </div>
                                </div>

                                <button type="submit" className="btn btn-primary aj-button mt-3">
                                    Save Location
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    );
}
