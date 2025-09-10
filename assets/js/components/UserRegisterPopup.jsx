import React, { useState, useRef, useEffect } from "react";
import MessageAlert from "./MessageAlert";
import { setToken } from "../utils/authUtils";
import { useNavigate } from "react-router-dom";
import { apiUrl } from "../utils/env";
import { Modal } from "bootstrap";

export default function UserRegisterPopup() {
    const navigate = useNavigate();
    const signupModalRef = useRef(null);
    const signupModalInstance = useRef(null);

    const [name, setName] = useState("");
    const [email, setEmail] = useState("");
    const [phone, setPhone] = useState("");
    const [password, setPassword] = useState("");
    const [confirmPassword, setConfirmPassword] = useState("");
    const [role, setRole] = useState("user");
    const [errorMessage, setErrorMessage] = useState("");
    const [acceptTerms, setAcceptTerms] = useState(false);

    const isValidEmail = (value) => /[^\s@]+@[^\s@]+\.[^\s@]+/.test(value);
    const isValidPhone = (value) => {
        if (!value) return false;
        const digitsOnly = value.replace(/\D/g, '');
        return digitsOnly.length >= 10 && digitsOnly.length <= 15;
    };

    const isValidPassword = (value) => {
        const regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return regex.test(value);
    };

    useEffect(() => {
        if (signupModalRef.current) {
            signupModalInstance.current = new Modal(signupModalRef.current);
        }
    }, []);

    const handleLoginClick = (e) => {
        e.preventDefault();

        signupModalInstance.current?.hide();

        const loginModalEl = document.getElementById("userLoginPopup");
        const loginModal = Modal.getInstance(loginModalEl) || new Modal(loginModalEl);
        loginModal.show();
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (password !== confirmPassword) {
            setErrorMessage("Passwords do not match.");
            return;
        }

        try {
            if (!name || name.trim().length < 2) {
                setErrorMessage("Please enter your name.");
                return;
            }
            if (!email || !isValidEmail(email)) {
                setErrorMessage("Please enter a valid email address.");
                return;
            }
            if (!isValidPhone(phone)) {
                setErrorMessage("Please enter a valid phone number.");
                return;
            }
            if (!isValidPassword(password)) {
                setErrorMessage(
                    "Password must be at least 8 characters, include one uppercase letter, one number, and one special character."
                );
                return;
            }
            if (!acceptTerms) {
                setErrorMessage("Please accept the terms and conditions.");
                return;
            }

            setErrorMessage("");
            let formattedPhone = phone.startsWith("+") ? phone : `+${phone}`;

            const registerRes = await fetch(apiUrl("/register"), {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    name,
                    email,
                    phone: formattedPhone,
                    password,
                    role
                })
            });

            let registerData;
            try {
                registerData = await registerRes.json();
            } catch {
                throw new Error("Unexpected server response. Please try again.");
            }

            if (!registerRes.ok || !registerData.success) {
                throw new Error(registerData.error || "Registration failed");
            }

            signupModalInstance.current?.hide();
            if (window.showVerificationModal) {
                window.showVerificationModal(registerData.user);
            } else {
                navigate("/", { replace: true });
            }

        } catch (err) {
            setErrorMessage(err.message || "Registration failed. Please try again.");
        }
    };


    return (
        <div className="modal fade" id="userRegisterPopup" tabIndex="-1" aria-labelledby="userRegisterPopupLabel" aria-hidden="true" ref={signupModalRef}>
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
                        <form className="text-center" onSubmit={handleSubmit}>
                            <h4 className="text-primary">Register</h4>
                            <div className="px-sm-2">
                                <div className="d-flex align-items-center gap-3">
                                    <input type="text" id="name" name="name" placeholder="Name" className="form-control my-2" value={name} onChange={(e) => setName(e.target.value)} />
                                    <select className="form-select my-2" value={role} onChange={(e) => setRole(e.target.value)}>
                                        <option value="user">User</option>
                                        <option value="chef">Chef</option>
                                    </select>
                                </div>
                                <div className="d-flex align-items-center gap-3">
                                    <input type="email" id="email" name="email" placeholder="Email Address" className="form-control my-2" value={email} onChange={(e) => setEmail(e.target.value)} />
                                    <input type="phone" id="phone" name="phone" placeholder="Phone Number" className="form-control my-2" value={phone} onChange={(e) => setPhone(e.target.value)} />
                                </div>
                                <div className="d-flex align-items-center gap-3">
                                    <input type="password" id="password" name="password" placeholder="Password" className="form-control my-2" value={password} onChange={(e) => setPassword(e.target.value)} />
                                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" className="form-control my-2" value={confirmPassword} onChange={(e) => setConfirmPassword(e.target.value)} />
                                </div>
                                <div className="d-flex align-items-center mt-3">
                                    <input 
                                        className="form-check-input my-0" 
                                        type="checkbox" 
                                        id="acceptance" 
                                        name="acceptance"
                                        checked={acceptTerms}
                                        onChange={(e) => setAcceptTerms(e.target.checked)}
                                    />
                                    <label className="body-text-small ms-2" htmlFor="acceptance">Accept our terms & conditions</label>
                                </div>
                            </div>
                            <div className="text-center mb-3 mt-md-5 mt-3 pt-3">
                                <button className="btn btn-primary aj-button body-text-small fw-700 px-4 py-2" type="submit">Sign up</button>
                                <p className="body-text mt-3 mb-0">Already have an account? <a
                                    className="aj-link"
                                    href="#"
                                    onClick={handleLoginClick}
                                >
                                    Login
                                </a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}