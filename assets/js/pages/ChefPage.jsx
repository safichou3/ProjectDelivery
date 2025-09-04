import React, { useEffect, useState } from "react";
import { useParams, useLocation, useNavigate } from "react-router-dom";

export default function ChefPage() {
    const { id } = useParams();
    const location = useLocation();
    const navigate = useNavigate();
    const handleMenuClick = (id) => {
        navigate(`/menu/${id}`);
    };

    const [chef, setChef] = useState(location.state?.chef || null);
    const [menus, setMenus] = useState([]);
    const [reviews, setReviews] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState("");
    const [filteredMenus, setFilteredMenus] = useState([]);
    const [showReviewForm, setShowReviewForm] = useState(false);
    const [reviewRating, setReviewRating] = useState(5);
    const [reviewComment, setReviewComment] = useState("");
    const [submittingReview, setSubmittingReview] = useState(false);
    const [currentUser, setCurrentUser] = useState(null);
    const displayMenus = searchTerm ? filteredMenus : menus;
    const [reviewMessage, setReviewMessage] = useState("");

    useEffect(() => {
        const token = localStorage.getItem('token');
        if (token) {
            try {
                const payload = JSON.parse(atob(token.split(".")[1]));
                setCurrentUser(payload);
            } catch (error) {
                console.error('Error:', error);
            }
        }
    }, []);

    const handleSearch = (e) => {
        e.preventDefault();

        const term = searchTerm.toLowerCase();
        const results = menus.filter(menu =>
            menu.title.toLowerCase().includes(term) ||
            menu.cuisineType?.toLowerCase().includes(term)
        );
        setFilteredMenus(results);
    };

    const loadReviews = async () => {
        try {
            const response = await fetch(`/api/chefs/${id}/reviews`);
            if (response.ok) {
                const data = await response.json();
                setReviews(data);
            }
        } catch (error) {
            console.error('Error loading reviews:', error);
        }
    };

    const handleSubmitReview = async (e) => {
        e.preventDefault();
        setReviewMessage("");
        if (!currentUser) {
            setReviewMessage("Please login to submit a review.");
            return;
        }

        setSubmittingReview(true);
        try {

            const token = localStorage.getItem("token");
            const response = await fetch(`/api/chefs/${id}/reviews`, {
                method: 'POST',
                headers: {
                    "Authorization": `Bearer ${token}`,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    rating: reviewRating,
                    comment: reviewComment,
                    clientId: currentUser.id
                })
            });

            const data = await response.json();
            if (response.ok) {
                setReviewMessage("Review submitted successfully!");
                setReviewComment("");
                setReviewRating(5);
                setShowReviewForm(false);
                loadReviews();
            } else {
                setReviewMessage(data.error || "Failed to submit review.");
            }
        } catch (error) {
            setReviewMessage("An error occurred while submitting review.");
        } finally {
            setSubmittingReview(false);
        }
    };

    const renderStars = (rating) => {
        return Array.from({ length: 5 }, (_, index) => (
            <i 
                key={index} 
                className={`fi ${index < rating ? 'fi-sr-star text-warning' : 'fi-sr-star text-muted'}`}
            ></i>
        ));
    };

    useEffect(() => {
        async function loadChefIfNeeded() {
            if (chef) return;
            try {
                const res = await fetch("/api/chefs");
                const all = await res.json();
                const found = all.find(c => String(c.id) === String(id));

                setChef(found || null);
            } catch (err) {
                console.error("Error fetching chef list:", err);
            }
        }
        loadChefIfNeeded();
    }, [id, chef]);


    useEffect(() => {
        async function loadMenus() {
            setLoading(true);
            try {
                const res = await fetch(`/api/chefs/${id}/menus`);
                if (!res.ok) {
                    setMenus([]);
                    setLoading(false);
                    return;
                }
                const data = await res.json();
                setMenus(data);
            } catch (err) {
                console.error("Error loading menus:", err);
                setMenus([]);
            } finally {
                setLoading(false);
            }
        }
        if (id) {
            loadMenus();
            loadReviews();
        }
    }, [id]);
    const licenceUrl = chef.licence ? `${window.location.origin}${chef.licence}` : null;

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
    if (!chef) return <p>Chef not found.</p>;


    return (
        <>
            <section className="container my-5">
                <div className="row pt-3">
                    <div className="col-12">
                        <div className="row m-0 aj-drop-shadow background-white rounded-4 py-4 px-md-3 px-2">
                            <div className="col-md-4 col-12">
                                <img
                                    className="rounded-4 w-100"
                                    src={chef.image || "/meal-1.png"}
                                    alt={chef.name}
                                />
                            </div>
                            <div className="col-md-8 col-12 mt-md-0 mt-3">
                                <h4 className="mb-3 text-primary">{chef.name}</h4>
                                <h5 className="mt-3 text-secondary">Bio</h5>
                                <p className="body-text">
                                    {chef.bio || "No biography available for this chef."}
                                </p>
                                <h5 className="mt-3 text-secondary">Certification</h5>
                                <p className="body-text">
                                    {chef.certification || "No certification available."}
                                </p>
                                <h5 className="mt-3 text-secondary">Licence</h5>
                                {licenceUrl ? (
                                    <a href={licenceUrl} target="_blank" rel="noopener noreferrer">
                                        View Licence
                                    </a>
                                ) : (
                                    <p>No licence uploaded.</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section className="recipe-section my-5">
                <div className="container">
                    <div className="row align-items-center">
                        <div className="col-md-8 col-12 my-auto">
                            <form className="d-flex align-items-center gap-3" onSubmit={handleSearch}>
                                <input type="text" id="searchProduct" name="Search" placeholder="Search by title, cuisine, description..." className="form-control mb-0" value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)}/>
                                <button type="submit" className="btn btn-primary aj-button body-text-small fw-700 px-4">
                                    <i className="fi fi-sr-filter me-2 fs-5 lh-1 align-middle"></i>
                                    Filters
                                </button>
                            </form>

                        </div>
                        <div className="col-md-4 col-12 text-md-end mt-md-0 mt-3">
                            <p className="body-text-small mb-0">
                                Showing {menus.length} Menu{menus.length !== 1 && "s"}
                            </p>
                        </div>
                    </div>

                    <div className="row mt-3">
                        {displayMenus.length > 0 ? (
                            displayMenus.map(menu => (
                                <div className="col-md-6 col-lg-3 col-12 my-3 recipe-card" key={menu.id} onClick={() => handleMenuClick(menu.id)} style={{ cursor: 'pointer' }}>
                                    <div className="card border-0 rounded-3 overflow-hidden aj-drop-shadow h-100">
                                        <img className="card-img-top rounded-0" src={menu.image || "/meal.png"} alt={menu.title}/>
                                        <div className="card-body py-2">
                                            <div className="row align-items-center">
                                                <div className="col-8">
                                                    <h5 className="card-title body-text-small fw-bold mb-0 lh-2">
                                                        {menu.title}
                                                    </h5>
                                                    <p className="card-text body-text-extra-small mb-1">
                                                        {menu.description}
                                                    </p>
                                                </div>
                                                {/*<div className="col-4 text-end">*/}
                                                {/*    <button className="btn btn-primary aj-button body-text-small fw-700 px-2 py-2">*/}
                                                {/*        <i className="fi fi-sr-shopping-cart fs-5 lh-1 align-middle"></i>*/}
                                                {/*    </button>*/}
                                                {/*</div>*/}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <p>No menus found for this chef.</p>
                        )}
                    </div>

                    <div className="col-12 mt-4">
                        <div className="d-flex align-items-center gap-2 justify-content-center">
                            {Array.from({ length: Math.ceil(menus.length / 10) }, (_, index) => (
                                <button
                                    key={index}
                                    className={`btn btn-primary aj-button body-text-small fw-700 px-3 py-2 ${
                                        index === 0 ? '' : 'btn-transparent'
                                    }`}
                                >
                                    {index + 1}
                                </button>
                            ))}
                        </div>
                    </div>

                </div>
            </section>
            <section className="container my-5">
                <div className="row">
                    <div className="col-12">
                        <div className="card border-0 rounded-4 aj-drop-shadow">
                            <div className="card-header bg-transparent border-0 py-3">
                                {reviewMessage && (
                                    <div className={`alert ${reviewMessage.includes("successfully") ? "alert-success" : "alert-danger"}`} role="alert">
                                        {reviewMessage}
                                    </div>
                                )}
                                <div className="d-flex justify-content-between align-items-center">
                                    <h4 className="mb-0 text-primary">Reviews</h4>
                                    {currentUser?.roles?.includes("ROLE_USER") && (
                                        <button className="btn btn-primary aj-button body-text-small fw-700 px-3" onClick={() => setShowReviewForm(!showReviewForm)}>
                                            {showReviewForm ? 'Cancel' : 'Write a Review'}
                                        </button>
                                    )}
                                </div>
                            </div>
                            
                            <div className="card-body">
                                {showReviewForm && currentUser?.roles?.includes("ROLE_USER") && (
                                    <div className="mb-4 p-3 border rounded-3 bg-light">
                                        <h5 className="mb-3">Write Your Review</h5>
                                        <form onSubmit={handleSubmitReview}>
                                            <div className="mb-3">
                                                <label className="form-label fw-bold">Rating:</label>
                                                <div className="d-flex gap-1">
                                                    {[1, 2, 3, 4, 5].map((star) => (
                                                        <button key={star} type="button" className="btn btn-link p-0" onClick={() => setReviewRating(star)}>
                                                            <i 
                                                                className={`fi ${star <= reviewRating ? 'fi-sr-star text-warning' : 'fi-sr-star text-muted'}`}
                                                                style={{ fontSize: '1.5rem' }}
                                                            ></i>
                                                        </button>
                                                    ))}
                                                </div>
                                            </div>
                                            <div className="mb-3">
                                                <label className="form-label fw-bold">Comment:</label>
                                                <textarea className="form-control" rows="3" value={reviewComment} onChange={(e) => setReviewComment(e.target.value)} placeholder="Share your experience with this chef..." required></textarea>
                                            </div>
                                            <button type="submit" className="btn btn-primary aj-button body-text-small fw-700 px-4" disabled={submittingReview}>
                                                {submittingReview ? 'Submitting...' : 'Submit Review'}
                                            </button>
                                        </form>
                                    </div>
                                )}

                                <div className="reviews-list">
                                    {reviews.length > 0 ? (
                                        reviews.map((review) => (
                                            <div key={review.id} className="border-bottom pb-3 mb-3">
                                                <div className="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 className="mb-1 fw-bold">{review.reviewer}</h6>
                                                        <div className="mb-2">
                                                            {renderStars(review.rating)}
                                                        </div>
                                                    </div>
                                                    <small className="text-muted">
                                                        {new Date(review.createdAt).toLocaleDateString()}
                                                    </small>
                                                </div>
                                                <p className="mb-0 body-text">{review.comment}</p>
                                            </div>
                                        ))
                                    ) : (
                                        <p className="text-muted text-center py-4">No reviews yet. Be the first to review this chef!</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="my-5 container">
                <div className="cta-section row py-md-5 py-3 mx-0 px-0 rounded-4">
                    <div className="col-12 text-center my-auto py-3 py-md-5">
                        <h2>Explorez, dégustez, soutenez :</h2>
                        <p>Votre ville regorge de saveurs, venez les découvrir !</p>
                        {/*<button className="btn btn-primary aj-button body-text-small fw-700 px-4">*/}
                        {/*    <i className="fi fi-rr-box-open-full me-2 fs-5 lh-1 align-middle"></i>*/}
                        {/*    CTA Button*/}
                        {/*</button>*/}
                    </div>
                </div>
            </section>
        </>
    );
}
