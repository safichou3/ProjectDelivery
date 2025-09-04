
export const addToCart = (dish) => {
    let cart = JSON.parse(sessionStorage.getItem("cart")) || [];
    const existingIndex = cart.findIndex(item => item.id === dish.id);

    if (existingIndex !== -1) {
        cart[existingIndex].quantity += 1;
    } else {
        cart.push({ ...dish, quantity: 1 });
    }

    sessionStorage.setItem("cart", JSON.stringify(cart));
    window.dispatchEvent(new CustomEvent("cartUpdated"));

    return cart;
};

export const removeFromCart = (dishId) => {
    let cart = JSON.parse(sessionStorage.getItem("cart")) || [];
    cart = cart.filter(item => item.id !== dishId);
    sessionStorage.setItem("cart", JSON.stringify(cart));
    window.dispatchEvent(new CustomEvent("cartUpdated"));

    return cart;
};

export const updateCartItemQuantity = (dishId, quantity) => {
    let cart = JSON.parse(sessionStorage.getItem("cart")) || [];
    const existingIndex = cart.findIndex(item => item.id === dishId);

    if (existingIndex !== -1) {
        if (quantity <= 0) {
            cart.splice(existingIndex, 1);
        } else {
            cart[existingIndex].quantity = quantity;
        }
        sessionStorage.setItem("cart", JSON.stringify(cart));
        window.dispatchEvent(new CustomEvent("cartUpdated"));
    }

    return cart;
};

export const clearCart = () => {
    sessionStorage.removeItem("cart");
    window.dispatchEvent(new CustomEvent("cartUpdated"));
    
    return [];
};

export const getCart = () => {
    return JSON.parse(sessionStorage.getItem("cart")) || [];
};

export const getCartCount = () => {
    const cart = getCart();
    return cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
};
