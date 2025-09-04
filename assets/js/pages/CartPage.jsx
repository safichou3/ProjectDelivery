import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { removeFromCart, updateCartItemQuantity, clearCart, getCart } from "../utils/cartUtils";
import { apiUrl } from "../utils/env";

export default function Cart() {
    const [cartItems, setCartItems] = useState([]);
    const [favoriteIds, setFavoriteIds] = useState([]);
    const [taxRate, setTaxRate] = useState(1);
    const navigate = useNavigate();

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
        const storedCart = getCart();
        if (storedCart.length > 0) {
            setCartItems(storedCart);
        }

        const fetchFavorites = async () => {
            try {
                const token = localStorage.getItem("token");
                if (!token) return;

                const res = await fetch(apiUrl("/user/favorites"), {
                    headers: {
                        "Authorization": "Bearer " + token
                    }
                });

                if (res.status === 401) {
                    localStorage.removeItem("token");
                    navigate("/");
                    return;
                }

                const data = await res.json();
                setFavoriteIds(data.map(f => f.id));
            } catch (err) {
                console.error("Error fetching favorites", err);
            }
        };

        fetchFavorites();
    }, []);


    useEffect(() => {
        const storedCart = getCart();
        if (storedCart.length > 0) {
            setCartItems(storedCart);
        }
        
        const fetchFavorites = async () => {
            try {
                const token = localStorage.getItem("token");
                if (!token) return;

                const res = await fetch(apiUrl("/user/favorites"), {
                    headers: {
                        "Authorization": "Bearer " + token
                    }
                });
                if (res.status === 401) {
                    localStorage.removeItem("token");
                    navigate("/");
                    return;
                }
                const data = await res.json();
                setFavoriteIds(data.map(f => f.id));
            } catch (err) {
                console.error("Error fetching favorites", err);
            }
        };
        fetchFavorites();
    }, []);

    const increaseQuantity = (index) => {
        const item = cartItems[index];
        updateCartItemQuantity(item.id, item.quantity + 1);
        setCartItems(getCart());
    };

    const decreaseQuantity = (index) => {
        const item = cartItems[index];
        if (item.quantity > 1) {
            updateCartItemQuantity(item.id, item.quantity - 1);
            setCartItems(getCart());
        }
    };

    const removeItem = (index) => {
        const item = cartItems[index];
        removeFromCart(item.id);
        setCartItems(getCart());
    };

    const clearCartItems = () => {
        clearCart();
        setCartItems([]);
    };

    const subTotal = cartItems.reduce((sum, item) => {
        const price = Number(item.price);
        const quantity = typeof item.quantity === "number" ? item.quantity : 1;
        return sum + (isNaN(price) ? 0 : price) * quantity;
    }, 0);

    const tax = +(subTotal * (taxRate / 100)).toFixed(2);
    const totalPrice = +(subTotal + tax);

    const toggleFavorite = async (dishId) => {
        try {
            const token = localStorage.getItem("token");
            if (!token) return alert("Please login first");

            const isFav = favoriteIds.includes(dishId);
            setFavoriteIds(prev =>
                isFav ? prev.filter(id => id !== dishId) : [...prev, dishId]
            );

            const response = await fetch(apiUrl("/user/favorites/toggle"), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": "Bearer " + token
                },
                body: JSON.stringify({ dishId })
            });

            if (!response.ok) {
                setFavoriteIds(prev =>
                    isFav ? [...prev, dishId] : prev.filter(id => id !== dishId)
                );
                const result = await response.json();
                alert(result.error || "Failed to update favorite");
            }

        } catch (err) {
            console.error(err);
        }
    };

    return (
        <div className="container my-md-5 py-md-5 my-3 py-3">
            <div className="row">
                <div className="col-12 mb-4">
                    <h2 className="text-center">Cart</h2>
                    {cartItems.length > 0 && (
                        <div className="text-center">
                            <button className="btn btn-outline-danger btn-sm" onClick={clearCartItems} title="Clear all items from cart">
                                <i className="fi fi-sr-trash me-2"></i>
                                Clear Cart
                            </button>
                        </div>
                    )}
                </div>
                <div className="col-12 aj-drop-shadow background-white rounded-4 p-md-5 p-3">
                    <div className="row">
                        <div className="col-md-7 col-12">
                            <p className="body-text lh-sm fw-bold text-md-start text-center mb-0 d-md-block d-none">
                                {cartItems.length} item{cartItems.length !== 1 ? "s" : ""} in the cart
                            </p>

                            {cartItems.length === 0 ? (
                                <p className="text-center">Your cart is empty.</p>
                            ) : (
                                cartItems.map((item, index) => (
                                    <div className="row my-2 recipe-card" key={item.id}>
                                        <div className="card border-0 rounded-3 overflow-hidden aj-drop-shadow p-2">
                                            <div className="row align-items-center">
                                                <div className="col-md-3 col-12">
                                                    <img className="w-100 rounded-2" src={item.image || "/meal.png"} alt={item.name} />
                                                </div>
                                                <div className="col-md-5 col-12 my-md-0 my-3">
                                                    <h5 className="card-title fw-bold mb-2 lh-2">{item.name}</h5>
                                                    <p className="card-text body-text-small fw-bold">${item.price}</p>
                                                </div>
                                                <div className="col-md-4 col-12 text-md-end text-center d-flex gap-3">
                                                    <div className="d-flex align-items-center quantity-wrapper">
                                                        <button
                                                            className="btn btn-primary aj-button body-text-small fw-normal px-2 py-2 rounded-0"
                                                            onClick={() => decreaseQuantity(index)}
                                                        >
                                                            <span className="px-1">-</span>
                                                        </button>
                                                        <input
                                                            type="text"
                                                            className="form-control text-center border-0 px-2 py-0 w-100"
                                                            value={item.quantity}
                                                            readOnly
                                                        />
                                                        <button
                                                            className="btn btn-primary aj-button body-text-small fw-normal px-2 py-2 rounded-0"
                                                            onClick={() => increaseQuantity(index)}
                                                        >
                                                            <span className="px-1">+</span>
                                                        </button>
                                                    </div>
                                                    <button
                                                        className="btn btn-primary btn-danger aj-button body-text-small fw-700 px-md-2 px-3 py-2"
                                                        onClick={() => removeItem(index)}
                                                    >
                                                        <i className="fi fi-sr-trash fs-5 lh-1 align-middle"></i>
                                                    </button>
                                                    <button
                                                        className="btn btn-transparent aj-button border-0 body-text-small fw-700 px-md-2 px-3 py-0"
                                                        onClick={() => toggleFavorite(item.id)}
                                                    >
                                                        <i
                                                            className={`fi fs-3 lh-1 align-middle ${
                                                                favoriteIds.includes(item.id)
                                                                    ? "fi-sr-heart text-danger"
                                                                    : "fi-rr-heart"
                                                            }`}
                                                        ></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))
                            )}
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
                                                    €{(item.price * item.quantity)}
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
                                            <p className="body-text fw-bold mb-1 text-end">€{subTotal}</p>
                                        </div>
                                    </div>
                                    <div className="row">
                                        <div className="col-6">
                                            <p className="body-text fw-bold mb-1">Tax Amount</p>
                                        </div>
                                        <div className="col-6">
                                            <p className="body-text fw-bold mb-1 text-end">€{tax}</p>
                                        </div>
                                    </div>
                                </div>

                                <div className="w-100 py-2 px-3 background-secondary">
                                    <div className="row">
                                        <div className="col-6">
                                            <p className="body-text fw-bolder mb-1">Total Price</p>
                                        </div>
                                        <div className="col-6">
                                            <p className="body-text fw-bolder mb-1 text-end">€{totalPrice}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button
                                className="btn btn-primary aj-button body-text-small fw-700 px-3 w-100 mt-3"
                                onClick={() => navigate("/payment")}
                                disabled={cartItems.length === 0}
                                style={{
                                    pointerEvents: cartItems.length === 0 ? "none" : "auto",
                                    opacity: 1,
                                    cursor: cartItems.length === 0 ? "not-allowed" : "pointer",
                                    backgroundColor: "#ff6900",
                                    color: "#fff",
                                }}
                            >
                                Continue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
