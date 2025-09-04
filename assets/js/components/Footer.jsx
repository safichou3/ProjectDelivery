import React from 'react';
import { useNavigate } from 'react-router-dom';
export default function Footer() {
    const navigate = useNavigate();
    return (
        <>
            <div className="aj-drop-shadow background-white mt-0">
                <div className="container background-white py-4">
                    <div className="row">
                        <div className="col-lg-4 col-12 text-center text-lg-start">
                            <a className="aj-site-logo" href="/">ChefChezToi</a>
                            <p className="body-text-small">Explorez de nouvelles saveurs, découvrez des adresses locales et laissez-vous surprendre chaque jour.</p>
                        </div>
                        <div className="col-lg-3 col-12 text-center text-lg-start">
                            <p className="mb-2">
                                {/*<b>Nav Menu Items</b>*/}
                            </p>
                            <ul className="footer-menu">
                                <li>
                                    {/*<a className="body-text-small" href="#">Menu Item</a>*/}
                                </li>
                                <li>
                                    {/*<a className="body-text-small" href="#">Menu Item</a>*/}
                                </li>
                                <li>
                                    {/*<a className="body-text-small" href="#">Menu Item</a>*/}
                                </li>
                                <li>
                                    {/*<a className="body-text-small" href="#">Menu Item</a>*/}
                                </li>
                            </ul>
                        </div>
                        <div className="col-lg-3 col-12 text-center text-lg-start">
                            <p className="mb-2">
                                {/*<b>Nav Menu Items</b>*/}
                            </p>
                            <ul className="footer-menu">
                                <li>
                                    {/*<a className="body-text-small" href="#">Menu Item</a>*/}
                                </li>
                                <li>
                                    {/*<a className="body-text-small" href="#">Menu Item</a>*/}
                                </li>
                                <li>
                                    {/*<a className="body-text-small" href="#">Menu Item</a>*/}
                                </li>
                            </ul>
                        </div>
                        <div className="col-lg-2 col-12 text-center text-lg-start">
                            <p className="mb-2">
                                <b>Join Our Community</b>
                            </p>
                            <ul className="footer-menu social-icon-menu">
                                <li>
                                    <a className="body-text-small" href="#">
                                        <i className="fi fi-brands-facebook"></i>
                                    </a>
                                </li>
                                <li>
                                    <a className="body-text-small" href="#">
                                        <i className="fi fi-brands-instagram"></i>
                                    </a>
                                </li>
                                <li>
                                    <a className="body-text-small" href="#">
                                        <i className="fi fi-brands-twitter"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div className="copyright-bar background-primary py-2">
                <div className="container">
                    <div className="row">
                        <div className="col-lg-6 col-12 text-center text-lg-start">
                            <p className="text-white body-text-extra-small">
                                <b>© ChefChezToi</b> - All Rights Reserved.
                            </p>
                        </div>
                        <div className="col-lg-6 col-12 text-center text-lg-end">
                            <ul className="footer-menu copyright-menu">
                                <li>
                                    <a className="body-text-extra-small fw-semibold" onClick={() => navigate('/privacy-policy')} style={{ cursor: "pointer", textDecoration: "none", color: "white" }}>
                                        Privacy Policy
                                    </a>
                                </li>
                                <li>
                                    <a className="body-text-extra-small fw-semibold" onClick={() => navigate('/cookie-policy')} style={{ cursor: "pointer", textDecoration: "none", color: "white" }}>
                                        Cookie Policy
                                    </a>
                                </li>
                                <li>
                                    <a className="body-text-extra-small fw-semibold" onClick={() => navigate('/terms-conditions')} style={{ cursor: "pointer", textDecoration: "none", color: "white" }}>
                                        Terms & Conditions
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
