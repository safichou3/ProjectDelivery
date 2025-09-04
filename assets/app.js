/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import React from "react";
import { createRoot } from "react-dom/client";
import App from "./js/App";
import "./styles/app.css";

const container = document.getElementById("root");
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}

