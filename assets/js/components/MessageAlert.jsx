import React, { useEffect } from 'react';
const MessageAlert = ({ message, type = 'info', onClose, autoClose = true, duration = 5000 }) => {
    useEffect(() => {
        if (autoClose && message) {
            const timer = setTimeout(() => {
                onClose && onClose();
            }, duration);
            return () => clearTimeout(timer);
        }
    }, [message, autoClose, duration, onClose]);

    if (!message) return null;

    const alertClass = `alert alert-${type} alert-dismissible fade show`;
    const iconClass = type === 'success' ? 'fi fi-sr-check' :
                     type === 'danger' ? 'fi fi-sr-cross' :
                     type === 'warning' ? 'fi fi-sr-exclamation' : 'fi fi-sr-info';

    return (
        <div className={alertClass} role="alert">
            <i className={iconClass} style={{ marginRight: '8px' }}></i>
            {message}
            <button type="button" className="btn-close" onClick={onClose} aria-label="Close"></button>
        </div>
    );
};

export default MessageAlert; 