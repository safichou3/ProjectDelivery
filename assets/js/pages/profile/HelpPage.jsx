import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { apiUrl } from "../../utils/env";
import ProfileSidebar from "../../components/ProfileSidebar";

export default function HelpPage() {
    const navigate = useNavigate();
    const [subject, setSubject] = useState("");
    const [message, setMessage] = useState("");
    const [success, setSuccess] = useState(null);
    const [messages, setMessages] = useState([]);
    const [chefs, setChefs] = useState([]);
    const [receiverType, setReceiverType] = useState("admin");
    const [receiverId, setReceiverId] = useState(null);
    const [userRole, setUserRole] = useState("");

    const fetchMessages = async () => {
        try {
            const response = await fetch(apiUrl("/support-messages"), {
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem("token")
                }
            });
            if (response.ok) {
                const data = await response.json();
                setMessages(data);
            } else {
                console.error("Error:", response.status);
            }
        } catch (err) {
            console.error("Error:", err);
        }
    };
    const fetchChefs = async () => {
        try {
            const response = await fetch(apiUrl("/chefs"), {
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem("token")
                }
            });
            if (response.ok) {
                const data = await response.json();
                setChefs(data);
            }
        } catch (err) {
            console.error(err);
        }
    };

    const fetchUserRole = async () => {
        try {
            const response = await fetch(apiUrl("/user"), {
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                },
            });
            if (response.ok) {
                const data = await response.json();
                const roleFromAPI = data.role || "";
                const roleFromStorage = localStorage.getItem("role") || "";
                const finalRole = roleFromAPI || roleFromStorage;
                setUserRole(finalRole);
            }
        } catch (err) {
            console.error(err);
        }
    };

    useEffect(() => {
        fetchMessages();
        fetchChefs();
        fetchUserRole();
    }, []);
    const handleSave = async (e) => {
        e.preventDefault();

        try {
            const response = await fetch(apiUrl("/support-messages"), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": "Bearer " + localStorage.getItem("token")
                },
                body: JSON.stringify({
                    subject: subject,
                    message: message,
                    receiverType: receiverType,
                    receiverId: receiverType === "chef" ? receiverId : null
                }),
            });

            if (response.ok) {
                setSuccess("Your message has been sent!");
                setSubject("");
                setMessage("");
                setReceiverType("admin");
                setReceiverId(null);
                fetchMessages();
            } else {
                setSuccess("Failed to send message!");
            }
        } catch (err) {
            setSuccess("Error sending message!");
        }
    };



    return (
        <div className="container my-md-5 py-md-5 my-3 py-3">
            <div className="row">
                <div className="col-12 mb-4">
                    <h2 className="text-center">Help & Support</h2>
                </div>
                <div className="col-md-4 col-12 mb-md-0 mb-4">
                    <ProfileSidebar />
                </div>

                <div className="col-md-8 col-12">
                    <div className="aj-drop-shadow background-white rounded-4 p-md-4 p-3">
                        {success && <div className="alert alert-info">{success}</div>}
                        <form onSubmit={handleSave}>
                            <div className="mb-3">
                                <label className="form-label">Subject</label>
                                <input type="text" className="form-control" value={subject} onChange={(e) => setSubject(e.target.value)} required/>
                            </div>

                            <div className="mb-3">
                                <label className="form-label">Message</label>
                                <textarea className="form-control" rows="4" value={message} onChange={(e) => setMessage(e.target.value)} required/>
                            </div>

                            <div className="mb-3">
                                <label className="form-label">Send To</label>
                                <select className="form-select" value={receiverType}
                                        onChange={(e) => {
                                        setReceiverType(e.target.value);
                                        setReceiverId(null);
                                    }}
                                >
                                    <option value="admin">Admin</option>
                                    {userRole !== "chef" && <option value="chef">Chef</option>}
                                </select>
                            </div>

                            {receiverType === "chef" && (
                                <div className="mb-3">
                                    <label className="form-label">Select Chef</label>
                                    <select className="form-select" value={receiverId || ""}
                                        onChange={(e) => setReceiverId(e.target.value)}
                                        required
                                    >
                                        <option value="">-- Select Chef --</option>
                                        {chefs.map((chef) => (
                                            <option key={chef.user_id} value={chef.user_id}>
                                                {chef.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            )}

                            <button className="btn btn-primary aj-button body-text-small fw-700 px-3 mt-md-3 mt-2" type="submit">
                                Send Message
                            </button>
                        </form>

                        <div className="mt-5">
                            <h5>Your Previous Messages</h5>
                            {messages.length === 0 && <p>No messages yet.</p>}
                            {messages.map((msg) => {
                                let receiverName = "Admin";
                                let receiverLabel = "Admin";
                                
                                if (msg.receiverType === "chef") {
                                    const chef = chefs.find(c => c.id === msg.receiverId);
                                    receiverName = chef ? chef.name : `Chef ID ${msg.receiverId}`;
                                    receiverLabel = "Chef";
                                }

                                return (
                                    <div key={msg.id} className="border rounded-3 p-3 mb-3">
                                        <p><strong>Subject:</strong> {msg.subject}</p>
                                        <p><strong>Message:</strong> {msg.message}</p>
                                        <p><strong>Reply:</strong> {msg.reply ? msg.reply : "No reply yet"}</p>
                                        <p><strong>Receiver:</strong> {receiverLabel}</p>
                                        <p><strong>Status:</strong> {msg.status}</p>
                                        <small className="text-muted">Sent on {new Date(msg.createdAt).toLocaleString()}</small>
                                    </div>
                                );
                            })}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    );
}
