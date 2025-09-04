import React, { useEffect, useState } from "react";
import { useLocation, useSearchParams, useNavigate } from "react-router-dom";
import { clearCart } from "../utils/cartUtils";

export default function ThankYouPage() {
    const navigate = useNavigate();
    const location = useLocation();
    const [searchParams] = useSearchParams();
    const { orderId } = location.state || {};
    const sessionId = searchParams.get('session_id');
    const [message, setMessage] = useState("");
    const [messageType, setMessageType] = useState("");

    useEffect(() => {
        const token = localStorage.getItem("token");
        if (!token) {
            navigate("/");
            return;
        }
        if (sessionId) {
            const pendingOrderIds = sessionStorage.getItem("pendingOrderIds");
            if (pendingOrderIds) {
                clearCart();
                const updatePaymentStatus = async () => {
                    try {
                        const token = localStorage.getItem("token");
                        const response = await fetch('/api/update-payment-status', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`,
                            },
                            body: JSON.stringify({
                                orderIds: JSON.parse(pendingOrderIds),
                                sessionId: sessionId
                            })
                        });
                    } catch (error) {
                        console.error('Error:', error);
                    }
                };
                
                updatePaymentStatus();
            }
        }
    }, [sessionId]);

    const handleInvoiceClick = async (e) => {
        e.preventDefault();
        
        let orderIds = [];
        const pendingOrderIds = sessionStorage.getItem("pendingOrderIds");
        if (pendingOrderIds) {
            orderIds = JSON.parse(pendingOrderIds);
        } else if (orderId) {
            orderIds = [orderId];
        }
        
        if (orderIds.length === 0) {
            setMessage("No orders found!");
            setMessageType("error");
            return;
        }

        const token = localStorage.getItem("token");

        let response;
        if (orderIds.length === 1) {
            response = await fetch(`/api/invoice/${orderIds[0]}`, {
                method: 'GET',
                headers: {
                    "Authorization": `Bearer ${token}`,
                }
            });
        } else {
            response = await fetch('/api/invoice-multiple', {
                method: 'POST',
                headers: {
                    "Authorization": `Bearer ${token}`,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ orderIds: orderIds })
            });
        }

        if (!response.ok) {
            setMessage("Failed to download invoice.");
            setMessageType("error");
            return;
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = orderIds.length === 1 ? `invoice-${orderIds[0]}.pdf` : `invoice-${orderIds.join('-')}.pdf`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        sessionStorage.removeItem("pendingOrderIds");
        setMessage("Invoice downloaded successfully!");
        setMessageType("success");
    };

    return (
        <div className="container my-md-5 py-md-5 my-3 py-3">
            <div className="row">
                <div className="col-md-8 col-12 mx-auto">
                    <div className="row px-3">
                        {message && (
                            <div className={`alert ${messageType === "success" ? "alert-success" : "alert-danger"} mt-3`} role="alert">
                                {message}
                            </div>
                        )}
                        <div className="col-12 text-center aj-drop-shadow background-white rounded-4 p-md-5 p-3">
                            <h2 className="text-center mb-4">Thank you</h2>
                            <p>Your order has been placed successfully.</p>
                            {sessionId && (
                                <p className="text-success mb-3">
                                    ✅ Payment completed successfully!
                                </p>
                            )}
                            <button
                                className="btn btn-primary aj-button body-text-small fw-700 px-3 mt-3"
                                onClick={handleInvoiceClick}
                            >
                                Download Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
