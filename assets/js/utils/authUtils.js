export const getToken = () => {
    return localStorage.getItem('token');
};

export const setToken = (token) => {
    localStorage.setItem('token', token);
    try {
        window.dispatchEvent(new CustomEvent('authChanged', { detail: { isAuthenticated: true } }));
    } catch (e) {}
};

export const removeToken = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('username');
    try {
        window.dispatchEvent(new CustomEvent('authChanged', { detail: { isAuthenticated: false } }));
    } catch (e) {}
};

export const isTokenExpired = (token) => {
    if (!token) return true;
    
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const currentTime = Math.floor(Date.now() / 1000);
        return payload.exp < (currentTime + 300);
    } catch (error) {
        console.error('Error decoding token:', error);
        return true;
    }
};

export const getTokenExpirationTime = (token) => {
    if (!token) return null;
    
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        return payload.exp * 1000;
    } catch (error) {
        console.error('Error decoding token:', error);
        return null;
    }
};

export const getTokenCreationTime = (token) => {
    if (!token) return null;
    
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        return payload.iat * 1000;
    } catch (error) {
        console.error('Error decoding token:', error);
        return null;
    }
};

export const getTokenTimeRemaining = (token) => {
    if (!token) return 0;
    
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const currentTime = Math.floor(Date.now() / 1000);
        return Math.max(0, payload.exp - currentTime);
    } catch (error) {
        console.error('Error decoding token:', error);
        return 0;
    }
};

export const getTokenExpirationInfo = (token) => {
    if (!token) return null;
    
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const currentTime = Math.floor(Date.now() / 1000);
        const timeRemaining = Math.max(0, payload.exp - currentTime);
        
        return {
            createdAt: payload.iat * 1000, // Convert to milliseconds
            expiresAt: payload.exp * 1000, // Convert to milliseconds
            timeRemaining: timeRemaining,
            isExpired: timeRemaining <= 0,
            formattedTimeRemaining: formatTimeRemaining(timeRemaining)
        };
    } catch (error) {
        console.error('Error decoding token:', error);
        return null;
    }
};

const formatTimeRemaining = (seconds) => {
    if (seconds <= 0) return 'Expired';
    
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;
    
    if (hours > 0) {
        return `${hours}h ${minutes}m ${remainingSeconds}s`;
    } else if (minutes > 0) {
        return `${minutes}m ${remainingSeconds}s`;
    } else {
        return `${remainingSeconds}s`;
    }
};

export const autoLogout = () => {
    removeToken();
    window.dispatchEvent(new CustomEvent('tokenExpired'));
    if (window.location.pathname !== '/') {
        window.location.href = '/';
    }
};

export const checkTokenAndLogout = () => {
    const token = getToken();
    
    if (token && isTokenExpired(token)) {
        autoLogout();
        return false;
    }
    
    return true;
};
export const setupTokenExpirationCheck = () => {
    checkTokenAndLogout();
    const checkInterval = setInterval(() => {
        if (!checkTokenAndLogout()) {
            clearInterval(checkInterval);
        }
    }, 30000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            checkTokenAndLogout();
        }
    });
    window.addEventListener('focus', () => {
        checkTokenAndLogout();
    });
    
    return checkInterval;
};

export const refreshTokenIfNeeded = async () => {
    const token = getToken();
    
    if (!token) return null;
    
    const timeRemaining = getTokenTimeRemaining(token);
    if (timeRemaining < 3600) {
        try {
            const response = await fetch('/api/refresh-token', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                setToken(data.token);
                return data.token;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
    
    return token;
}; 