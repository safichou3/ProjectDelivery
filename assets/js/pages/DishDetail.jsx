import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import MessageAlert from "../components/MessageAlert";
import { addToCart, updateCartItemQuantity } from "../utils/cartUtils";

export default function DishDetail() {
    const { id } = useParams();
    const navigate = useNavigate();

    const [dish, setDish] = useState(null);
    const [relatedDishes, setRelatedDishes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [relatedLoading, setRelatedLoading] = useState(true);
    const [quantity, setQuantity] = useState(1);
    const [reservationDate, setReservationDate] = useState("");
    const [message, setMessage] = useState("");
    const [showMessage, setShowMessage] = useState(false);

    useEffect(() => {
        fetch(`/api/dishes/${id}`)
            .then((res) => res.json())
            .then((data) => {
                setDish(data);
                setLoading(false);
                if (data) {
                    fetchRelatedDishes(data);
                }
            })
            .catch(() => setLoading(false));
    }, [id]);

    const fetchRelatedDishes = async (currentDish) => {
        try {
            setRelatedLoading(true);
            const response = await fetch(`/api/dishes/related/${currentDish.id}?cuisine=${currentDish.cuisineType || ''}&chef=${currentDish.chef?.id || ''}&limit=4`);
            
            if (response.ok) {
                const data = await response.json();
                setRelatedDishes(data);
            } else {
                const fallbackResponse = await fetch('/api/dishes?limit=4');
                if (fallbackResponse.ok) {
                    const fallbackData = await fallbackResponse.json();
                    const filteredDishes = fallbackData.filter(d => d.id !== currentDish.id);
                    setRelatedDishes(filteredDishes.slice(0, 4));
                }
            }
        } catch (error) {
            setRelatedDishes([
                {
                    id: 1,
                    name: "Stir-Fried Noodles",
                    price: 49.99,
                    image: "/meal.png",
                    cuisineType: "Asian"
                },
                {
                    id: 2,
                    name: "Grilled Chicken",
                    price: 39.99,
                    image: "/meal-1.png",
                    cuisineType: "Mediterranean"
                },
                {
                    id: 3,
                    name: "Pasta Carbonara",
                    price: 44.99,
                    image: "/meal.png",
                    cuisineType: "Italian"
                },
                {
                    id: 4,
                    name: "Beef Steak",
                    price: 59.99,
                    image: "/meal-1.png",
                    cuisineType: "American"
                }
            ]);
        } finally {
            setRelatedLoading(false);
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

    const increaseQuantity = () => {
        setQuantity((prev) => prev + 1);
    };
    
    const decreaseQuantity = () => {
        if (quantity > 1) {
            setQuantity((prev) => prev - 1);
        }
    };

    const handleReserve = () => {
        if (!dish) return;

        if (!reservationDate) {
            showMessageAlert("Please select a reservation date before proceeding.", "danger");
            return;
        }

        addToCart({
            ...dish,
            date: reservationDate
        });
        
        showMessageAlert("Dish added to cart successfully!", "success");
    };

    const addRelatedDishToCart = (relatedDish) => {
        if (!reservationDate) {
            showMessageAlert("Please select a reservation date first.", "warning");
            return;
        }

        addToCart({
            ...relatedDish,
            date: reservationDate
        });
        
        showMessageAlert(`${relatedDish.name} added to cart!`, "success");
    };

    if (loading) {
        return (
            <div
                role="status"
                aria-live="polite"
                aria-label="Loading"
                className="loading-overlay"
            >
                <span className="spinner" aria-hidden="true"></span>
                <span className="sr-only">Loading…</span>
            </div>
        );
    }
    if (!dish) return <p>Dish not found</p>;

    return (
        <>
            <section className="product-page">
                <div className="py-5 container">
                    <div className="row py-md-5 py-3">
                        <div className="col-12 mb-4"><button className="btn btn-secondary mb-3" onClick={() => navigate(-1)}>← Back</button></div>
                        {showMessage && (
                            <MessageAlert message={message} type={message.includes("success") ? "success" : message.includes("warning") ? "warning" : "danger"} onClose={clearMessage} autoClose={true}/>
                        )}
                        <div className="col-12 mb-md-3">
                            <p className="breadcrumbs">Home / Product</p>
                        </div>
                        <div className="col-12">
                            <div className="row">
                                <div className="col-md-5 col-12">
                                    <img className="rounded-4 w-100 h-100" src={dish.image || "/meal-1.png"} alt={dish.name}/>
                                </div>
                                <div className="col-md-7 col-12">
                                    <h2>{dish.name}</h2>
                                    <div>
                                        <ul className="nav nav-pills mb-3 gap-5" id="pills-tab" role="tablist">
                                            <li className="nav-item" role="presentation">
                                                <button className="active" id="product-description-tab" data-bs-toggle="pill" data-bs-target="#product-description" type="button" role="tab" aria-controls="product-description" aria-selected="true">
                                                    Description
                                                </button>
                                            </li>
                                            <li className="nav-item" role="presentation">
                                                <button className="" id="product-ingredients-tab" data-bs-toggle="pill" data-bs-target="#product-ingredients" type="button" role="tab" aria-controls="product-ingredients" aria-selected="false">
                                                    Ingredients
                                                </button>
                                            </li>
                                            {/*<li className="nav-item" role="presentation">*/}
                                            {/*    <button className="" id="long-text-tab" data-bs-toggle="pill" data-bs-target="#long-text" type="button" role="tab" aria-controls="long-text" aria-selected="false">*/}
                                            {/*        Long Text*/}
                                            {/*    </button>*/}
                                            {/*</li>*/}
                                        </ul>
                                        <div className="tab-content" id="pills-tabContent">
                                            <div className="tab-pane fade show active" id="product-description" role="tabpanel" aria-labelledby="product-description-tab">
                                                <p className="body-text">{dish.description}</p>
                                            </div>
                                            <div className="tab-pane fade" id="product-ingredients" role="tabpanel" aria-labelledby="product-ingredients-tab">
                                                <p className="body-text">{dish.ingredients || "N/A"}</p>
                                            </div>
                                            {/*<div className="tab-pane fade" id="long-text" role="tabpanel" aria-labelledby="long-text-tab">*/}
                                            {/*    <p className="body-text">{dish.longText || dish.description}</p>*/}
                                            {/*</div>*/}
                                        </div>
                                    </div>
                                    <div className="my-3">
                                        <label className="fw-bold">Select Reservation Date & Time:</label>
                                        <input
                                            type="datetime-local"
                                            className="form-control w-auto"
                                            value={reservationDate}
                                            onChange={(e) => setReservationDate(e.target.value)}
                                            min={new Date().toISOString().slice(0,16)}
                                        />
                                    </div>


                                    {/*<div className="d-flex gap-3 align-items-center my-3">*/}
                                    {/*    <p className="body-text mb-0">Quantity</p>*/}
                                    {/*    <div className="d-flex align-items-center quantity-wrapper w-auto">*/}
                                    {/*        <button className="btn btn-primary aj-button body-text-small fw-700 px-3 py-2" onClick={decreaseQuantity}>*/}
                                    {/*            -*/}
                                    {/*        </button>*/}
                                    {/*        <input type="text" className="form-control text-center border-0 px-2 py-0" value={quantity} readOnly/>*/}
                                    {/*        <button className="btn btn-primary aj-button body-text-small fw-700 px-3 py-2" onClick={increaseQuantity}>*/}
                                    {/*            +*/}
                                    {/*        </button>*/}
                                    {/*    </div>*/}
                                    {/*</div>*/}

                                    <div className="d-flex flex-column align-items-start mt-5 my-3">
                                        <p className="fw-bold mb-2">€{dish.price || "0.00"}</p>
                                        <button className="btn btn-primary aj-button body-text-small fw-700 px-4" onClick={handleReserve}>
                                            <i className="fi fi-sr-calendar me-2 fs-5 lh-1 align-middle"></i>
                                            Reserve
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="recipe-section my-5">
                <div className="container">
                    <div className="row mt-3">
                        <div className="col-12">
                            <h3 className="text-center">Related Products</h3>
                            <p className="text-center text-muted mb-4">
                                {dish.cuisineType ? `More ${dish.cuisineType} dishes` : "Discover more delicious meals"}
                            </p>
                        </div>
                        
                        {relatedLoading ? (
                            <div className="col-12 text-center">
                                <div className="spinner-border text-primary" role="status">
                                    <span className="visually-hidden">Loading...</span>
                                </div>
                                <p className="mt-2">Loading related products...</p>
                            </div>
                        ) : relatedDishes.length > 0 ? (
                            relatedDishes.map((relatedDish) => (
                                <div key={relatedDish.id} className="col-md-6 col-lg-3 col-12 my-3 recipe-card">
                                    <div className="card border-0 rounded-3 overflow-hidden aj-drop-shadow h-100 cursor-pointer"
                                        style={{ 
                                            cursor: 'pointer',
                                            transition: 'all 0.3s ease',
                                            transform: 'translateY(0)'
                                        }}
                                        onClick={() => navigate(`/dish/${relatedDish.id}`)}
                                        title={`Click to view ${relatedDish.name}`}
                                        onMouseEnter={(e) => {
                                            e.currentTarget.style.transform = 'translateY(-5px)';
                                            e.currentTarget.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
                                        }}
                                        onMouseLeave={(e) => {
                                            e.currentTarget.style.transform = 'translateY(0)';
                                            e.currentTarget.style.boxShadow = '';
                                        }}
                                    >
                                        <img className="card-img-top rounded-0" src={relatedDish.image || "/meal-1.png"} alt={relatedDish.name} style={{ height: "200px", objectFit: "cover" }}/>
                                        <div className="card-body py-2 d-flex flex-column">
                                            <div className="row align-items-center flex-grow-1">
                                                <div className="col-8">
                                                    <h5 className="card-title body-text-small fw-bold mb-0 lh-2">
                                                        {relatedDish.name}
                                                    </h5>
                                                    <p className="card-text body-text-extra-small mb-1">
                                                        {relatedDish.cuisineType || "Delicious"}
                                                    </p>
                                                    <p className="card-text body-text-extra-small fw-bold">
                                                        ${relatedDish.price || "0.00"}
                                                    </p>
                                                </div>
                                                <div className="col-4 text-end">
                                                    <button className="btn btn-primary aj-button body-text-small fw-700 px-2 py-2" onClick={(e) => {e.stopPropagation();addRelatedDishToCart(relatedDish);}} title="Add to cart">
                                                        <i className="fi fi-sr-shopping-cart fs-5 lh-1 align-middle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-12 text-center">
                                <p className="text-muted">No related products found.</p>
                            </div>
                        )}
                    </div>
                </div>
            </section>
        </>
    );
}
