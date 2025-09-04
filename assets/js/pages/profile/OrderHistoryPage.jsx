import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { apiUrl } from "../../utils/env";
import ProfileSidebar from "../../components/ProfileSidebar";

export default function OrderHistoryPage() {
    const navigate = useNavigate();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [message, setMessage] = useState(null);

    useEffect(() => {
        const token = localStorage.getItem("token");
        fetch(apiUrl("/my-orders"), {
            headers: {
                "Authorization": `Bearer ${token}`,
                "Content-Type": "application/json",
            },
        })
            .then(res => res.json())
            .then(data => {
                setOrders(data.orders || []);
                setLoading(false);
            })
            .catch(err => {
                setLoading(false);
            });
    }, []);


    const handleCancel = async (orderId) => {
        const token = localStorage.getItem("token");
        try {
            const res = await fetch(apiUrl(`/cancel-order/${orderId}`), {
                method: "POST",
                headers: {
                    "Authorization": `Bearer ${token}`,
                    "Content-Type": "application/json",
                },
            });

            if (res.ok) {
                setMessage({ type: "success", text: "Order cancelled successfully!" });
                setOrders(orders.map(o => o.id === orderId ? { ...o, status: "pending" } : o));
            } else {
                setMessage({ type: "error", text: "Failed to cancel order." });
            }
        } catch (error) {
            setMessage({ type: "error", text: "Something went wrong!" });
        }
    };

    return (
        <div className="container my-md-5 py-md-5 my-3 py-3">
            <form className="row">
                <div className="col-12 mb-4">
                    <h2 className="text-center">History</h2>
                </div>
                <div className="col-12">
                    <div className="row">
                        <div className="col-md-4 col-12 mb-md-0 mb-4">
                            <ProfileSidebar />
                        </div>

                        <div className="col-md-8 col-12">
                            <div className="aj-drop-shadow background-white rounded-4 p-md-4 p-3">
                                <h3 className="mb-4">Order History</h3>
                                {message && (
                                    <div
                                        className={`alert ${message.type === "success" ? "alert-success" : "alert-danger"} mb-3`}
                                        role="alert"
                                    >
                                        {message.text}
                                    </div>
                                )}
                                {loading ? (
                                    <div role="status" aria-live="polite" aria-label="Loading" className="flex justify-center items-center py-6">
                                    <span className="spinner border-4 border-gray-300 border-t-blue-500 rounded-full w-8 h-8 animate-spin" aria-hidden="true"></span>
                                        <span className="sr-only">Loading…</span>
                                    </div>
                                ) : orders.length === 0 ? (
                                    <p>No orders found.</p>
                                ) : (
                                    <div className="table-responsive">
                                        <table className="table table-bordered align-middle">
                                            <thead className="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Dish</th>
                                                <th>Quantity</th>
                                                <th>Total (Rs.)</th>
                                                <th>Pickup Date</th>
                                                <th>Status</th>
                                                <th>Payment</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {orders.map((order, index) => (
                                                <tr key={order.id}>
                                                    <td>{index + 1}</td>
                                                    <td>{order.dish}</td>
                                                    <td>{order.quantity}</td>
                                                    <td>{order.totalAmount}</td>
                                                    <td>{order.pickupDate} {order.pickupTime}</td>
                                                    <td>{order.status}</td>
                                                    <td>{order.paymentStatus} ({order.paymentType})</td>
                                                    <td>
                                                        {order.status.toLowerCase() === "pending" ? (
                                                            <button
                                                                className="btn btn-danger btn-sm"
                                                                onClick={() => handleCancel(order.id)}
                                                            >
                                                                Cancel Order
                                                            </button>
                                                        ) : (
                                                            "-"
                                                        )}
                                                    </td>
                                                </tr>
                                            ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    );
}
