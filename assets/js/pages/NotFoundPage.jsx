import React from "react";
import { useNavigate } from "react-router-dom";

export default function NotFoundPage() {
    const navigate = useNavigate();

    return (
        <div className="flex flex-col items-center justify-center min-h-screen bg-gray-50 text-center pt-20" style={{ color: "#ff6900" }}>
            <h1 className="text-6xl font-bold mt-5">404</h1>
            <h2 className="text-2xl font-semibold mt-4">Page Not Found</h2>
            <p className="mt-2 mb-6 text-black">
                Sorry, the page you are looking for doesn’t exist or has been moved.
            </p>
            <div className="container mt-4">
                <button className="btn btn-secondary mb-3"  onClick={() => navigate("/")}>
                    ← Back
                </button>
            </div>
        </div>
    );
}
