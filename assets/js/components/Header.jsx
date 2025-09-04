import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import UserLoginPopup from "./UserLoginPopup";

import ForgotPasswordPopup from "./ForgotPasswordPopup";
import MessageAlert from "./MessageAlert";

import {getToken, removeToken, checkTokenAndLogout, setupTokenExpirationCheck, isTokenExpired} from "../utils/authUtils";

export default function Header() {
    const navigate = useNavigate();
    const [cartCount, setCartCount] = useState(0);
    const [isLoggedIn, setIsLoggedIn] = useState(false);
    const [username, setUsername] = useState("");
    const [message, setMessage] = useState("");
    const [showMessage, setShowMessage] = useState(false);
    const [role, setRole] = useState("");

    const loadCartCount = () => {
        try {
            const cart = JSON.parse(sessionStorage.getItem("cart")) || [];
            const totalCount = cart.reduce(
                (sum, item) => sum + (item.quantity || 1),
                0
            );
            setCartCount(totalCount);
        } catch {
            setCartCount(0);
        }
    };
    const showMessageAlert = (msg, type = 'info') => {
        setMessage(msg);
        setShowMessage(true);
        setTimeout(() => setShowMessage(false), 5000);
    };
    const clearMessage = () => {
        setShowMessage(false);
        setMessage("");
    };
    const checkLogin = () => {
        const token = getToken();
        setIsLoggedIn(!!token);

        if (token) {
            const savedUsername = localStorage.getItem("username");
            if (savedUsername) setUsername(savedUsername);

            const savedRole = localStorage.getItem("role");
            if (savedRole) setRole(savedRole);
        }
    };

    const handleLogout = () => {
        removeToken();
        setIsLoggedIn(false);
        setUsername("");
        navigate("/");
    };

    const handleLoginSuccess = (payload) => {
        setIsLoggedIn(true);
        setUsername(payload.name || payload.email);

        if (payload.role) {
            localStorage.setItem("role", payload.role);
            setRole(payload.role);
        }
    };

    const handleCartUpdate = () => {
        loadCartCount();
    };

    const updateCartCount = () => {
        loadCartCount();
    };

    useEffect(() => {
        checkLogin();
        loadCartCount();
        const checkInterval = setupTokenExpirationCheck();
        
        const handleTokenExpired = () => {
            setIsLoggedIn(false);
            setUsername("");
        };

        const onStorageChange = (e) => {
            if (e.key === "cart") {
                loadCartCount();
            }
            if (e.key === "token") {
                setIsLoggedIn(!!e.newValue);
                if (!e.newValue) {
                    setUsername("");
                    setRole("");
                }
            }
            if (e.key === "username") {
                setUsername(e.newValue || "");
            }
            if (e.key === "role") {
                setRole(e.newValue || "");
            }
        };

        window.addEventListener("cartUpdated", handleCartUpdate);
        window.addEventListener("storage", onStorageChange);
        window.addEventListener("tokenExpired", handleTokenExpired);
        window.updateHeaderCartCount = updateCartCount;

        return () => {
            clearInterval(checkInterval);
            window.removeEventListener("cartUpdated", handleCartUpdate);
            window.removeEventListener("storage", onStorageChange);
            window.removeEventListener("tokenExpired", handleTokenExpired);
            delete window.updateHeaderCartCount;
        };
    }, []);

    return (
        <div className="aj-drop-shadow background-white">
            {showMessage && (
                <MessageAlert message={message} type={message.includes("warning") ? "warning" : "info"} onClose={clearMessage} autoClose={true}/>
            )}
            
            <div className="container">
                <nav className="navbar navbar-expand-lg bg-body-tertiary background-white position-relative py-3">
                    <div className="container-fluid px-0 position-relative">
                        <Link className="navbar-brand aj-site-logo position-absolute start-50 translate-middle-x" to="/">
                            ChefChezToi
                        </Link>

                        <button className="navbar-toggler position-absolute end-0" type="button" data-bs-toggle="collapse" data-bs-target="#ajNavbar" aria-controls="ajNavbar" aria-expanded="false" aria-label="Toggle navigation">
                            <span className="navbar-toggler-icon"></span>
                        </button>

                        <div className="collapse navbar-collapse justify-content-between text-center" id="ajNavbar">
                            <ul className="navbar-nav aj-navbar-left mb-2 mb-lg-0">
                                {/*<li className="nav-item">*/}
                                {/*    <a className="nav-link" href="#">*/}
                                {/*        Menu Item 1*/}
                                {/*    </a>*/}
                                {/*</li>*/}
                                {/*<li className="nav-item">*/}
                                {/*    <a className="nav-link" href="#">*/}
                                {/*        Menu Item 2*/}
                                {/*    </a>*/}
                                {/*</li>*/}
                            </ul>

                            <ul className="navbar-nav aj-navbar-right ms-auto mb-2 mb-lg-0">
                                {isLoggedIn && (
                                <li className="nav-item me-3 d-flex align-items-center">
                                    <button onClick={() => navigate("/cart")} className="btn nav-link position-relative p-0" style={{ background: "none", border: "none" }} title="Cart">
                                        <i className="fi fi-sr-shopping-cart fs-5"></i>
                                        {cartCount > 0 && (
                                            <span
                                                style={{
                                                    position: "absolute",
                                                    top: "10%",
                                                    right: "-12px",
                                                    transform: "translateY(-50%)",
                                                    fontSize: "0.7rem",
                                                    backgroundColor: "red",
                                                    color: "white",
                                                    borderRadius: "50%",
                                                    padding: "2px 5px",
                                                    minWidth: "18px",
                                                    textAlign: "center",
                                                    lineHeight: "1",
                                                    fontWeight: "700",
                                                }}
                                            >
                                                {cartCount}
                                            </span>
                                        )}
                                    </button>
                                </li>
                                )}

                                {isLoggedIn && (
                                    <li className="nav-item dropdown">
                                        <a className="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            {username}
                                        </a>
                                        <ul className="dropdown-menu dropdown-menu-end">
                                            {role === "admin" && (
                                                <li>
                                                    <a className="dropdown-item" href="/admin/dashboard">
                                                        Dashboard
                                                    </a>
                                                </li>
                                            )}
                                            {role === "chef" && (
                                                <li>
                                                    <a className="dropdown-item" href="/chef/dashboard">
                                                        Dashboard
                                                    </a>
                                                </li>
                                            )}

                                            <li>
                                                <button className="dropdown-item" onClick={() => navigate("/profile/setting")}>
                                                    Profile
                                                </button>
                                            </li>
                                            <li>
                                                <hr className="dropdown-divider" />
                                            </li>
                                            <li>
                                                <button className="dropdown-item text-danger" onClick={handleLogout}>
                                                    Logout
                                                </button>
                                            </li>
                                        </ul>
                                    </li>
                                )}
                                {!isLoggedIn && <li className="nav-item ms-3" style={{ visibility: "hidden" }}>Placeholder</li>}
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
            <UserLoginPopup onLoginSuccess={handleLoginSuccess} />
            <ForgotPasswordPopup />
        </div>
    );
}
