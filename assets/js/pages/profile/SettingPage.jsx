import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { apiUrl } from "../../utils/env";
import ProfileSidebar from "../../components/ProfileSidebar";

export default function SettingPage() {
    const navigate = useNavigate();
    const [userData, setUserData] = useState({
        name: "",
        email: "",
        phone: "",
        password: "",
        confirmPassword: "",
    });
    const [success, setSuccess] = useState(null);
    const [error, setError] = useState(null);
    const fetchUserData = async () => {
        try {
            const response = await fetch(apiUrl("/user"), {
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                },
            });
            if (response.ok) {
                const data = await response.json();
                setUserData((prev) => ({
                    ...prev,
                    name: data.name || "",
                    email: data.email || "",
                    phone: data.phone || "",
                }));
            }
        } catch (err) {
            console.error(err);
        }
    };

    useEffect(() => {
        fetchUserData();
    }, []);



    const handleChange = (e) => {
        const { name, value } = e.target;
        setUserData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };
    const handleSave = async (e) => {
        e.preventDefault();

        if (userData.password && !userData.confirmPassword) {
            setError("Please confirm your password!");
            return;
        }

        if (userData.password !== userData.confirmPassword) {
            setError("Passwords do not match!");
            return;
        }

        try {
            const response = await fetch(apiUrl("/user/update"), {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                },
                body: JSON.stringify({
                    name: userData.name,
                    email: userData.email,
                    phone_number: userData.phone,
                    password: userData.password ? userData.password : undefined,
                }),
            });

            if (response.ok) {
                const data = await response.json();
                setSuccess("Profile updated successfully!");
                setError(null);
                fetchUserData();
                setUserData((prev) => ({
                    ...prev,
                    password: "",
                    confirmPassword: "",
                }));
            } else {
                const errorData = await response.json();
                if (Array.isArray(errorData.error)) {
                    setError(errorData.error.join(", "));
                } else {
                    setError(errorData.error || "Failed to update profile!");
                }
            }
        } catch (err) {
            setError("Error updating profile!");
        }
    };



    return (
        <div className="container my-md-5 py-md-5 my-3 py-3">
            <form className="row" onSubmit={handleSave}>
                <div className="col-12 mb-4">
                    <h2 className="text-center">Welcome {userData.name}</h2>
                </div>
                <div className="col-12">
                    <div className="row">
                        <div className="col-md-4 col-12 mb-md-0 mb-4">
                            <ProfileSidebar />
                        </div>
                        <div className="col-md-8 col-12">
                            <div className="aj-drop-shadow background-white rounded-4 p-md-4 p-3">
                                <h3 className="mb-4">Settings</h3>
                                {success && <div className="alert alert-info">{success}</div>}
                                {error && <div className="alert alert-danger">{error}</div>}
                                <p className="body-text lh-sm fw-bold text-md-start text-center mb-0 d-md-block d-none">Basic Information</p>

                                <input type="text" name="name" placeholder="Full Name" className="form-control my-3" value={userData.name} onChange={handleChange} />
                                <input type="email" name="email" placeholder="Email Address" className="form-control my-3" value={userData.email} readOnly/>
                                <input type="text" name="phone" placeholder="Phone Number" className="form-control my-3" value={userData.phone} readOnly/>
                                <input type="password" name="password" placeholder="Password" className="form-control my-3" value={userData.password} onChange={handleChange} />
                                <input type="password" name="confirmPassword" placeholder="Confirm Password" className="form-control my-3" value={userData.confirmPassword} onChange={handleChange} />

                                <button className="btn btn-primary aj-button body-text-small fw-700 px-3 mt-md-5 mt-3" type="submit">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    );
}
