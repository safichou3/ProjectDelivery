import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

export default function AllChefsPage() {
    const [chefs, setChefs] = useState([]);
    const navigate = useNavigate();

    useEffect(() => {
        const now = new Date();
        const pad = (n) => String(n).padStart(2, "0");
        const dateTime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(
            now.getDate()
        )} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
        fetch(`/api/chefs/available?datetime=${encodeURIComponent(dateTime)}`, {
            headers: {
                "Accept": "application/json",
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error("Network error: " + res.status);
                return res.json();
            })
            .then((data) => setChefs(data.slice(0, 4)))
            .catch((err) => console.error("Error fetching chefs:", err));
    }, []);

    return (
        <div className="container my-5">
            <h2 className="mb-4">All Chefs</h2>
            <div className="row">
                {chefs.map(chef => (
                    <div key={chef.id} className="col-md-6 col-lg-3 col-12 my-3 recipe-card" style={{ cursor: "pointer" }} onClick={() => navigate(`/chef/${chef.id}`, { state: { chef } })}>
                        <div className="card border-0 rounded-3 overflow-hidden aj-drop-shadow h-100">
                            <img className="card-img-top rounded-0" src={chef.image || "/meal.png"} alt={chef.name}/>
                            <div className="card-body py-2">
                                <h5 className="card-title body-text-small fw-bold mb-0 lh-2">
                                    {chef.name}
                                </h5>
                                <p className="body-text-extra-small mb-1">
                                    {chef.certification || "Certification not available"}
                                </p>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
