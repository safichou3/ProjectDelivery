import React, { useState, useRef, useEffect } from "react";
import MessageAlert from "./MessageAlert";
import { setToken } from "../utils/authUtils";
import { useNavigate } from "react-router-dom";
import { apiUrl } from "../utils/env";
import { Modal } from "bootstrap";

export default function VerificationPopup() {
    const navigate = useNavigate();
    const modalRef = useRef(null);
    const modalInstance = useRef(null);
    const [otp, setOtp] = useState(['', '', '', '', '', '']);
    const [errorMessage, setErrorMessage] = useState("");
    const [isLoading, setIsLoading] = useState(false);
    const [userData, setUserData] = useState(null);

    useEffect(() => {
        if (modalRef.current) {
            modalInstance.current = new Modal(modalRef.current);
        }
    }, []);

    const cleanupModal = (modalEl) => {
        if (!modalEl) return;
        modalEl.addEventListener(
            "hidden.bs.modal",
            () => {
                document.body.classList.remove("modal-open");
                document.body.style.overflow = "";
                document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
            },
            { once: true }
        );
    };
    const handleOtpChange = (index, value) => {
        if (value.length > 1) return;

        const newOtp = [...otp];
        newOtp[index] = value;
        setOtp(newOtp);

        if (value && index < 5) {
            const nextInput = document.getElementById(`otp-${index + 1}`);
            if (nextInput) nextInput.focus();
        }
    };

    const handleKeyDown = (index, e) => {
        if (e.key === 'Backspace' && !otp[index] && index > 0) {
            const prevInput = document.getElementById(`otp-${index - 1}`);
            if (prevInput) prevInput.focus();
        }
    };

    const handlePaste = (e) => {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text').slice(0, 6);
        if (/^\d{6}$/.test(pastedData)) {
            const newOtp = pastedData.split('');
            setOtp([...newOtp, ...Array(6 - newOtp.length).fill('')]);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const otpString = otp.join('');

        if (otpString.length !== 6) {
            setErrorMessage("Please enter the complete 6-digit OTP.");
            return;
        }

        setIsLoading(true);
        setErrorMessage("");

        try {
            const verifyRes = await fetch(apiUrl("/verify-otp"), {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ otp: otpString })
            });

            const verifyData = await verifyRes.json();

            if (!verifyRes.ok || !verifyData.token) {
                throw new Error(verifyData.error || "OTP verification failed");
            }

            setToken(verifyData.token);
            await fetch(apiUrl("/session-login"), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${verifyData.token}`,
                },
                credentials: "include",
            });
            const payload = JSON.parse(atob(verifyData.token.split(".")[1]));
            localStorage.setItem("username", payload.name || payload.email);
            localStorage.setItem("role", payload.roles?.[0]?.replace("ROLE_", "").toLowerCase() || "user");

            window.dispatchEvent(new Event("storage"));
            window.dispatchEvent(new StorageEvent("storage", { key: "token", newValue: verifyData.token }));
            window.dispatchEvent(new StorageEvent("storage", { key: "username", newValue: payload.name || payload.email }));
            window.dispatchEvent(new StorageEvent("storage", { key: "role", newValue: payload.roles?.[0] }));

            modalInstance.current?.hide();

            cleanupModal(modalRef.current);
            const roles = payload.roles || [];
            if (roles.includes("ROLE_ADMIN")) {
                window.location.href = "/admin/dashboard";
            } else if (roles.includes("ROLE_CHEF")) {
                window.location.href = "/chef/dashboard";
            } else {
                navigate("/", { replace: true });
            }

        } catch (err) {
            setErrorMessage(err.message || "Verification failed. Please try again.");
        } finally {
            setIsLoading(false);
        }
    };

    const handleResendOtp = async () => {
        if (!userData || !userData.email) {
            setErrorMessage("User data missing. Cannot resend OTP.");
            return;
        }

        setIsLoading(true);
        setErrorMessage("");

        try {
            const resendRes = await fetch(apiUrl("/resend-otp"), {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email: userData.email })
            });

            const data = await resendRes.json();

            if (!resendRes.ok || !data.success) {
                throw new Error(data.error || "Failed to resend OTP");
            }

            setErrorMessage("");
            console.log("Resent OTP:", data.otp_for_testing);
        } catch (err) {
            setErrorMessage(err.message || "Failed to resend OTP. Please try again.");
        } finally {
            setIsLoading(false);
        }
    };

    const showVerificationModal = (user) => {
        setUserData(user);
        setOtp(['', '', '', '', '', '']);
        setErrorMessage("");
        modalInstance.current?.show();
    };

    useEffect(() => {
        window.showVerificationModal = showVerificationModal;
        return () => {
            delete window.showVerificationModal;
        };
    }, []);

    return (
        <div
            className="modal fade"
            id="verificationPopup"
            tabIndex="-1"
            aria-labelledby="verificationPopupLabel"
            aria-hidden="true"
            ref={modalRef}
        >
            <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content">
                    <div className="modal-body">
                        {errorMessage && (
                            <MessageAlert message={errorMessage} type="danger" />
                        )}
                        <div className="text-end">
                            <button
                                type="button"
                                className="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"
                            />
                        </div>
                        <form className="text-center" onSubmit={handleSubmit}>
                            <h4 className="text-primary mb-2">Verify Your Phone Number</h4>
                            <p className="body-text">
                                We've sent a 6-digit verification code to your phone number.
                                Please enter it below to complete your registration.
                            </p>
                            <div className="px-sm-5 my-4">
                                <div className="d-flex justify-content-center gap-2 my-4">
                                    {otp.map((digit, index) => (
                                        <input
                                            key={index}
                                            id={`otp-${index}`}
                                            type="text"
                                            maxLength="1"
                                            className="form-control text-center px-2"
                                            style={{ width: '50px', height: '50px', fontSize: '20px' }}
                                            value={digit}
                                            onChange={(e) => handleOtpChange(index, e.target.value)}
                                            onKeyDown={(e) => handleKeyDown(index, e)}
                                            onPaste={handlePaste}
                                            disabled={isLoading}
                                        />
                                    ))}
                                </div>
                            </div>
                            <div className="text-center mb-3 mt-3 pt-3">
                                <button
                                    className="btn btn-primary aj-button body-text-small fw-700 px-4 py-2"
                                    type="submit"
                                    disabled={isLoading}
                                >
                                    {isLoading ? "Verifying..." : "Verify & Complete Registration"}
                                </button>
                                <div className="mt-3">
                                    <button
                                        type="button"
                                        className="btn btn-link aj-link body-text-small"
                                        onClick={handleResendOtp}
                                        disabled={isLoading}
                                    >
                                        Didn't receive the code? Resend
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
