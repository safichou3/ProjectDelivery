export function getEnv(key, fallback = undefined) {
    if (typeof process !== 'undefined' && process.env && Object.prototype.hasOwnProperty.call(process.env, key)) {
        const value = process.env[key];
        if (typeof value !== 'undefined') return value;
    }
    return fallback;
}

export function getAppBaseUrl() {
    const fromEnv = getEnv('APP_BASE_URL');
    if (fromEnv) return fromEnv.replace(/\/$/, '');
    if (typeof window !== 'undefined' && window.location?.origin) return window.location.origin.replace(/\/$/, '');
    return '';
}

export function apiUrl(path = '/') {
    const base = getAppBaseUrl();
    const normalized = String(path || '/');
    const withApi = normalized.startsWith('/api') ? normalized : `/api${normalized.startsWith('/') ? '' : '/'}${normalized}`;
    if (!base) return withApi;
    return base + withApi;
}


