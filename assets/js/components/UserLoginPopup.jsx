import React, { useState } from "react";
import MessageAlert from "./MessageAlert";
import { apiUrl } from "../utils/env";
import { setToken } from "../utils/authUtils";
import { useNavigate } from "react-router-dom";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
export default function UserLoginPopup({ onLoginSuccess }) {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const navigate = useNavigate();
    const [errorMessage, setErrorMessage] = useState("");
    const isValidEmail = (value) => /[^\s@]+@[^\s@]+\.[^\s@]+/.test(value);

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            if (!email || !isValidEmail(email)) {
                setErrorMessage("Please enter a valid email address.");
                return;
            }
            if (!password || password.length < 6) {
                setErrorMessage("Password must be at least 6 characters.");
                return;
            }
            setErrorMessage("");

            const response = await fetch(apiUrl("/login"), {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email, password }),
            });
            if (!response.ok) throw new Error("Login failed");

            const data = await response.json();
            const payload = JSON.parse(atob(data.token.split(".")[1]));
            const roles = payload.roles || [];

            localStorage.setItem("username", payload.name || payload.email);
            setToken(data.token);

            const modalEl = document.getElementById("userLoginPopup");
            const modal = window.bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();

                modalEl.addEventListener("hidden.bs.modal", () => {

                    document.body.classList.remove("modal-open");
                    document.body.style.overflow = "";
                    const backdrops = document.querySelectorAll(".modal-backdrop");
                    backdrops.forEach((backdrop) => backdrop.remove());
                }, { once: true });
            }

            if (onLoginSuccess) onLoginSuccess(payload);

            const sessionResponse = await fetch(apiUrl("/session-login"), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${data.token}`,
                },
                body: JSON.stringify({}),
                credentials: "include"
            });

            const sessionData = await sessionResponse.json();
            if (!sessionData.success) throw new Error("Session login failed");

            if (sessionData.redirect) {
                const redirect = sessionData.redirect;
                const isPrivRoute = /^\/(admin|chef)\b/.test(redirect);
                if (roles.includes("ROLE_ADMIN") || roles.includes("ROLE_CHEF") || isPrivRoute) {
                    window.location.href = redirect;
                } else {
                    navigate(redirect, { replace: true });
                }
                return;
            }

            if (roles.includes("ROLE_ADMIN")) {
                window.location.href = "/admin/dashboard";
            } else if (roles.includes("ROLE_CHEF")) {
                window.location.href = "/chef/dashboard";
            } else {
                navigate("/", { replace: true });
            }

        } catch (error) {
            setErrorMessage(error.message || "Login failed");
        }
    };

    return (
        <div className="modal fade" id="userLoginPopup" tabIndex="-1" aria-labelledby="userLoginPopupLabel" aria-hidden="true">
            <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content">
                    <div className="modal-body">
                        {errorMessage && (
                            <MessageAlert message={errorMessage} type="danger" />
                        )}
                        <div className="text-end">
                            <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <i className="fi fi-sr-rectangle-xmark"></i>
                            </button>
                        </div>
                        <form className="" onSubmit={handleSubmit}>
                            <h4 className="text-primary text-center">Login</h4>
                            <div className="px-sm-5">
                                <input type="email" id="email" name="email" placeholder="Email Address" className="form-control my-3" value={email} onChange={(e) => setEmail(e.target.value)}
                                />
                                <input type="password" id="password" name="password" placeholder="Password" className="form-control my-3" value={password} onChange={(e) => setPassword(e.target.value)}
                                />
                                <div className="d-flex align-items-center justify-content-between">
                                    <a className="aj-link body-text-small text-dark ms-auto" href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordPopup">
                                        Forgot Password?
                                    </a>
                                </div>
                            </div>
                            <div className="text-center mb-3 mt-md-5 mt-3 pt-3">
                                <button className="btn btn-primary aj-button body-text-small fw-700 px-4 py-2" type="submit">
                                    Login
                                </button>
                                <p className="body-text mt-3 mb-0">
                                    Don't have an account?{" "}
                                    <a className="aj-link" href="#" onClick={(e) => e.preventDefault()} data-bs-toggle="modal" data-bs-target="#userRegisterPopup">
                                        Sign up
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
