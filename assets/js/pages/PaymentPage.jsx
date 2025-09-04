import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { loadStripe } from "@stripe/stripe-js";
import { clearCart } from "../utils/cartUtils";
import {apiUrl} from "../utils/env";
const stripePromise = loadStripe("pk_test_51N6XIUA5jyT7aSXEZMbfF0IoesQ9YIrz5XuBK3SoHwu57LOFqsIPFVWX8tro7EqtudkgQvqpd3aVS5jTNbTON4K30010Tf0rbh");

export default function PaymentPage() {
    const [cartItems, setCartItems] = useState([]);
    const [promoCode, setPromoCode] = useState("");
    const [discount, setDiscount] = useState(0);
    const [taxRate, setTaxRate] = useState(1);
    const [paymentMethod, setPaymentMethod] = useState("cod");
    const [formData, setFormData] = useState({
        name: "",
        email: "",
        address: "",
        city: "",
        postalCode: "",
    });
    const [missingInfo, setMissingInfo] = useState(false);
    const [message, setMessage] = useState({ text: "", type: "", show: false });
    const navigate = useNavigate();

    const showMessage = (text, type = "info") => {
        setMessage({ text, type, show: true });
        setTimeout(() => setMessage({ text: "", type: "", show: false }), 5000);
    };

    useEffect(() => {
        const fetchTax = async () => {
            try {
                const token = localStorage.getItem("token");
                if (!token) return;

                const res = await fetch(apiUrl("/settings"), {
                    headers: {
                        "Authorization": "Bearer " + token,
                        "Content-Type": "application/json"
                    }
                });

                if (!res.ok) {
                    console.error("Failed", res.status);
                    return;
                }

                const data = await res.json();
                setTaxRate(data.tax || 1);
            } catch (err) {
                console.error("Error", err);
            }
        };

        fetchTax();
    }, []);

    useEffect(() => {
        const storedCart = sessionStorage.getItem("cart");
        if (storedCart) {
            const parsedCart = JSON.parse(storedCart);
            setCartItems(parsedCart);
        }
        const fetchUser = async () => {
            const token = localStorage.getItem("token");
            try {
                const res = await fetch("/api/user", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                if (res.ok) {
                    const data = await res.json();
                    const requiredFields = ["name", "email", "address", "city", "postalCode"];
                    const hasMissing = requiredFields.some((field) => !data[field] || data[field].trim() === "");
                    setMissingInfo(hasMissing);

                    setFormData({
                        name: data.name || "",
                        email: data.email || "",
                        address: data.address || "",
                        city: data.city || "",
                        postalCode: data.postalCode || "",
                    });
                } else {
                    setMissingInfo(true);
                }
            } catch (err) {
                console.error(err);
                setMissingInfo(true);
            }
        };

        fetchUser();
    }, []);

    const subTotal = cartItems.reduce(
        (sum, item) =>
            sum + (isNaN(Number(item.price)) ? 0 : Number(item.price)) * (item.quantity || 1),
        0
    );
    const totalBeforeDiscount = subTotal;
    const discountAmount = discount;
    const tax = +((totalBeforeDiscount - discountAmount) * (taxRate / 100)).toFixed(2);
    const totalPrice = +(totalBeforeDiscount - discountAmount + tax).toFixed(2);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const applyPromoCode = async () => {
        if (!promoCode.trim()) {
            showMessage("Please enter a discount code", "warning");
            return;
        }

        try {
            const response = await fetch("/api/validate-discount", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    discountCode: promoCode.trim()
                })
            });

            const data = await response.json();

            if (data.valid) {
                const discountAmount = totalBeforeDiscount * data.discountPercentage / 100;
                setDiscount(discountAmount);
                showMessage(`Discount applied! ${data.discountPercentage}% off`, "success");
        } else {
                setDiscount(0);
                showMessage(data.message || "Invalid discount code", "danger");
            }
        } catch (error) {
            console.error("Error:", error);
            setDiscount(0);
            showMessage("Error", "danger");
        }
    };
    const createReservation = async (paymentStatus) => {
        try {
            const token = localStorage.getItem("token");
            if (!token) {
                showMessage("User not authenticated", "danger");
                return;
            }

            const cartItemsWithCalculation = cartItems.map((item, index) => {
                const itemSubtotal = (Number(item.price) || 0) * (item.quantity || 1);
                const itemDiscount = discount > 0 ? (itemSubtotal / subTotal) * discount : 0;
                const itemTax = ((itemSubtotal - itemDiscount) * (taxRate / 100));
                const totalAmount = itemSubtotal - itemDiscount + itemTax;

                return {
                    ...item,
                    date: item.date,
                    subTotal: +itemSubtotal.toFixed(2),
                    discountAmount: +itemDiscount.toFixed(2),
                    taxAmount: +itemTax.toFixed(2),
                    totalAmount: +totalAmount.toFixed(2),
                };
            });

            const res = await fetch("/api/reservations", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`,
                },
                body: JSON.stringify({
                    cartItems: cartItemsWithCalculation,
                    paymentStatus,
                    discountAmount: discount,
                    totalAmount: totalPrice,
                    taxRate: taxRate,
                }),
            });

            const data = await res.json();

            if (res.ok && data.reservations && data.reservations.length > 0) {
                const orderIds = data.reservations.map(r => r.id);
                sessionStorage.setItem("pendingOrderIds", JSON.stringify(orderIds));
                const orderId = data.reservations[0].id;
                clearCart();
                navigate("/thank-you", { state: { orderId } });
            } else {
                showMessage(data.message || "Failed to create reservation", "danger");
            }
        } catch (err) {
            console.error(err);
            showMessage("Error creating reservation: " + err.message, "danger");
        }
    };

    const handleCOD = async (e) => {
        e.preventDefault();
        await createReservation("cod");
    };
    const handleStripe = async (e) => {
        e.preventDefault();
        console.log("handleStripe called with payment method:", paymentMethod);
        console.log("Cart items:", cartItems);

        try {
        const token = localStorage.getItem("token");

            const cartItemsWithCalculation = cartItems.map((item, index) => {
                const itemSubtotal = (Number(item.price) || 0) * (item.quantity || 1);
                const itemDiscount = discount > 0 ? (itemSubtotal / subTotal) * discount : 0;
                const itemTax = ((itemSubtotal - itemDiscount) * (taxRate / 100));
                const totalAmount = itemSubtotal - itemDiscount + itemTax;

                return {
                    ...item,
                    date: item.date,
                    subTotal: +itemSubtotal.toFixed(2),
                    discountAmount: +itemDiscount.toFixed(2),
                    taxAmount: +itemTax.toFixed(2),
                    totalAmount: +totalAmount.toFixed(2),
                };
            });
            
            const reservationRes = await fetch("/api/reservations", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`,
                },
                body: JSON.stringify({
                    cartItems: cartItemsWithCalculation,
                    totalAmount: totalPrice,
                    paymentStatus: "online",
                    discountAmount: discount,
                    taxRate: taxRate,
                }),
            });

            if (!reservationRes.ok) {
                showMessage("Failed to create order", "danger");
                return;
            }

            const reservationData = await reservationRes.json();
            if (!reservationData.reservations || reservationData.reservations.length === 0) {
                showMessage("Failed to create order", "danger");
                return;
            }
            const orderIds = reservationData.reservations.map(r => r.id);
            sessionStorage.setItem("pendingOrderIds", JSON.stringify(orderIds));
            const stripe = await stripePromise;
            console.log("stripe checkout:", { amount: totalPrice, orderIds });

        const res = await fetch("/api/create-checkout-session", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`,
            },
                body: JSON.stringify({ 
                    amount: totalPrice,
                    orderIds: orderIds 
                }),
            });

            if (!res.ok) {
                const errorText = await res.text();
                throw new Error(`HTTP error! status: ${res.status}, response: ${errorText}`);
            }

        const data = await res.json();
            
        if (data.url) {
            window.location.href = data.url;
        } else {
                showMessage("Failed to start payment", "danger");
            }
        } catch (err) {
            console.error("Error processing online payment:", err);
            showMessage("Error processing payment: " + err.message, "danger");
        }
    };

    const handleFormSubmit = (e) => {
        e.preventDefault();
        
        if (missingInfo) {
            showMessage("Please complete your profile information first", "warning");
            return;
        }
        
        if (paymentMethod === "cod") {
            handleCOD(e);
        } else if (paymentMethod === "online") {
            handleStripe(e);
        }
    };

    return (
        <div className="container my-md-5 py-md-5 my-3 py-3">
            {missingInfo && (
                <div className="alert alert-warning text-center">
                    ⚠️ Some of your profile information is missing. Please update your profile before proceeding with payment.
                </div>
            )}
            {message.show && (
                <div className={`alert alert-${message.type} alert-dismissible fade show text-center`} role="alert">
                    {message.text}
                    <button type="button" className="btn-close" onClick={() => setMessage({ text: "", type: "", show: false })} aria-label="Close"></button>
                </div>
            )}
            <form className="row" onSubmit={handleFormSubmit}>
                <div className="col-12 mb-4">
                    <h2 className="text-center">Payment</h2>
                </div>
                <div className="col-12 aj-drop-shadow background-white rounded-4 p-md-5 p-3">
                    <div className="row">
                        <div className="col-md-7 col-12">
                            <p className="body-text lh-sm fw-bold text-md-start text-center mb-0 d-md-block d-none">
                                Basic Information
                            </p>
                            <input type="text" id="name" name="name" placeholder="Full Name" className="form-control my-3" value={formData.name} disabled />
                            <input type="email" id="email" name="email" placeholder="Email Address" className="form-control my-3" value={formData.email} disabled />
                            <input type="text" id="address" name="address" placeholder="Address" className="form-control my-3" value={formData.address} disabled />
                            <div className="row">
                                <div className="col-md-6 col-12">
                                    <input type="text" id="city" name="city" placeholder="City" className="form-control" value={formData.city} disabled />
                                </div>
                                <div className="col-md-6 col-12">
                                    <input type="text" id="postalCode" name="postalCode" placeholder="Postal Code" className="form-control" value={formData.postalCode} disabled />
                                </div>
                            </div>
                            <div className="mt-4">
                                <p className="fw-bold">Select Payment Method</p>
                                <div>
                                    <label>
                                        <input type="radio" value="cod" checked={paymentMethod === "cod"} onChange={(e) => setPaymentMethod(e.target.value)}/>{" "}
                                        Cash on Delivery
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" value="online" checked={paymentMethod === "online"} onChange={(e) => setPaymentMethod(e.target.value)}/>{" "}
                                        Online Payment
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div className="col-md-5 col-12 mb-md-0 mb-4 order-first order-md-last">
                            <div id="productSummary" className="overflow-hidden border border-3 rounded-4">
                                <div className="w-100 py-2 px-3">
                                    <p className="body-text fw-bold mb-1">Order Summary</p>
                                    <p className="body-text lh-sm fw-500">
                                        {cartItems.length} item{cartItems.length !== 1 ? "s" : ""} in the cart
                                    </p>
                                    {cartItems.map((item) => (
                                        <div className="row" key={item.id}>
                                            <div className="col-6">
                                                <p className="body-text fw-normal mb-1">{item.name}</p>
                                            </div>
                                            <div className="col-6">
                                                <p className="body-text mb-1 text-end">
                                                    €{((Number(item.price) || 0) * (item.quantity || 1)).toFixed(2)}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <div className="w-100 border-top border-3 py-2 px-3">
                                    <div className="row">
                                        <div className="col-6">
                                            <p className="body-text fw-bold mb-1">Sub Total</p>
                                        </div>
                                        <div className="col-6">
                                            <p className="body-text fw-bold mb-1 text-end">€{subTotal.toFixed(2)}</p>
                                        </div>
                                    </div>
                                    <div className="row">
                                        <div className="col-6">
                                            <p className="body-text fw-bold mb-1">Tax Amount</p>
                                        </div>
                                        <div className="col-6">
                                            <p className="body-text fw-bold mb-1 text-end">€{tax.toFixed(2)}</p>
                                        </div>
                                    </div>
                                    {discount > 0 && (
                                        <div className="row">
                                            <div className="col-6">
                                                <p className="body-text fw-bold mb-1 text-success">Discount</p>
                                            </div>
                                            <div className="col-6">
                                                <p className="body-text fw-bold mb-1 text-end text-success">-€{discount.toFixed(2)}</p>
                                            </div>
                                        </div>
                                    )}
                                </div>
                                <div className="w-100 py-2 px-3 background-secondary">
                                    <div className="row">
                                        <div className="col-6">
                                            <p className="body-text fw-bolder mb-1">Total Price</p>
                                        </div>
                                        <div className="col-6">
                                            <p className="body-text fw-bolder mb-1 text-end">€{totalPrice.toFixed(2)}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="my-3">
                                <p className="mb-1 body-text-small text-primary fw-bold">Do you have a promo code?</p>
                                <div className="input-group">
                                <input type="text" id="promoCode" name="promoCode" placeholder="Discount Code" className="form-control mb-0" value={promoCode} onChange={(e) => setPromoCode(e.target.value)} onBlur={applyPromoCode}/>
                                <button
                                    type="button"
                                    className="btn btn-secondary"
                                    onClick={() => applyPromoCode(promoCode)}
                                    style={{ backgroundColor: "#ff6900", color: "#fff", border: "none" }}>
                                    Apply
                                </button>
                                </div>
                            </div>
                            <button className="btn btn-primary aj-button body-text-small fw-700 px-3 w-100" type="submit">
                                {paymentMethod === "cod" ? "Place Order (COD)" : "Pay Online"}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    );
}
