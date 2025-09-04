import React, { useState } from "react";
import MessageAlert from "./MessageAlert";

export default function ForgotPasswordPopup() {
    const [email, setEmail] = useState("");
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("info");
    const [isLoading, setIsLoading] = useState(false);
    const [errors, setErrors] = useState({});
    const validateForm = () => {
        const newErrors = {};
        if (!email.trim()) {
            newErrors.email = "Email is required";
        } else if (!/\S+@\S+\.\S+/.test(email)) {
            newErrors.email = "Please enter a valid email address";
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };
    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        setIsLoading(true);
        setMessage("");

        try {
            const response = await fetch('/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email }),
            });

            const data = await response.json();

            if (response.ok) {
                setMessage("Password reset link has been sent to your email. Please check your inbox.");
                setMessageType("success");
                setEmail("");
                setErrors({});
            } else {
                setMessage(data.message || "An error occurred. Please try again.");
                setMessageType("danger");
            }
        } catch (error) {
            setMessage("Network error. Please check your connection and try again.");
            setMessageType("danger");
        } finally {
            setIsLoading(false);
        }
    };

    const clearMessage = () => {
        setMessage("");
        setMessageType("info");
    };
    const handleChange = (e) => {
        const { name, value } = e.target;
        setEmail(value);

        if (errors[name]) {
            setErrors(prev => ({ ...prev, [name]: "" }));
        }
    };

    return (
        <div
            className="modal fade"
            id="forgotPasswordPopup"
            tabIndex="-1"
            aria-labelledby="forgotPasswordPopupLabel"
            aria-hidden="true"
        >
            <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content">
                    <div className="modal-body">
                        <div className="text-end">
                            <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <i className="fi fi-sr-rectangle-xmark"></i>
                            </button>
                        </div>
                        
                        {message && (
                            <MessageAlert message={message} type={messageType} onClose={clearMessage} autoClose={messageType === "success"}/>
                        )}

                        <form className="text-center" onSubmit={handleSubmit}>
                            <h4 className="text-primary mb-2">Forgotten password</h4>
                            <p className="text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>
                            
                            <div className="px-sm-5">
                                <div className="mb-3">
                                    <input type="email" id="email" name="email" value={email} onChange={handleChange} placeholder="Email Address" className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                    />
                                    {errors.email && (
                                        <div className="invalid-feedback text-start">
                                            {errors.email}
                                        </div>
                                    )}
                                </div>
                            </div>
                            
                            <div className="text-center mb-3 mt-md-5 mt-3 pt-3">
                                <button className="btn btn-primary aj-button body-text-small fw-700 px-4 py-2" type="submit" disabled={isLoading}>
                                    {isLoading ? (
                                        <>
                                            <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Sending...
                                        </>
                                    ) : (
                                        "Send Reset Link"
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
