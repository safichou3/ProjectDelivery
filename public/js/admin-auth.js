const getToken = () => {
    return localStorage.getItem('token');
};

const removeToken = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('username');
    localStorage.removeItem('role');
};

const isTokenExpired = (token) => {
    if (!token) return true;
    
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const currentTime = Math.floor(Date.now() / 1000);
        return payload.exp < currentTime;
    } catch (error) {
        console.error('Error:', error);
        return true;
    }
};

const getTokenTimeRemaining = (token) => {
    if (!token) return 0;
    
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const currentTime = Math.floor(Date.now() / 1000);
        return Math.max(0, payload.exp - currentTime);
    } catch (error) {
        console.error('Error:', error);
        return 0;
    }
};

const getTokenExpirationInfo = (token) => {
    if (!token) return null;
    
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const currentTime = Math.floor(Date.now() / 1000);
        const timeRemaining = Math.max(0, payload.exp - currentTime);
        
        return {
            createdAt: payload.iat * 1000,
            expiresAt: payload.exp * 1000,
            timeRemaining: timeRemaining,
            isExpired: timeRemaining <= 0,
            formattedTimeRemaining: formatTimeRemaining(timeRemaining)
        };
    } catch (error) {
        console.error('Error:', error);
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

const checkTokenAndLogout = () => {
    const token = getToken();
    
    if (token && isTokenExpired(token)) {
        autoLogout();
        return false;
    }
    
    return true;
};

const autoLogout = () => {
    removeToken();
    window.location.href = '/';
};

const setupTokenExpirationCheck = () => {
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

    window.addEventListener('beforeunload', () => {
        checkTokenAndLogout();
    });
    
    return checkInterval;
};

document.addEventListener('DOMContentLoaded', function() {
    setupTokenExpirationCheck();
});

window.addEventListener('focus', function() {
    checkTokenAndLogout();
});
