/**
 * API Configuration Module
 * Centralized configuration for API endpoints
 * 
 * Defaults to local Laravel development server.
 * Can be overridden via localStorage or browser console.
 */

(function() {
    'use strict';

    // Default configuration for local development
    const DEFAULT_BASE_URL = 'http://localhost:8000/api';
    const DEFAULT_SERVER_URL = 'http://localhost:8000';

    // Try to load from localStorage (allows per-developer override)
    let storedBaseUrl = null;
    try {
        storedBaseUrl = localStorage.getItem('API_BASE_URL');
    } catch (e) {
        // localStorage not available (private browsing, etc.)
    }

    // Parse server URL from base URL
    function deriveServerUrl(baseUrl) {
        return baseUrl.replace(/\/api\/?$/, '');
    }

    // Initial values
    let baseUrl = storedBaseUrl || DEFAULT_BASE_URL;
    let serverUrl = storedBaseUrl ? deriveServerUrl(storedBaseUrl) : DEFAULT_SERVER_URL;

    // Auto-detect when accessed via ngrok/proxy: if page origin differs from API origin
    // and current origin is HTTPS (ngrok), use current origin for API calls
    (function autoDetectOrigin() {
        try {
            const currentOrigin = window.location.origin;
            // Only auto-detect if no stored override
            if (!storedBaseUrl && currentOrigin) {
                const parsedDefault = new URL(DEFAULT_BASE_URL);
                if (currentOrigin !== parsedDefault.origin && currentOrigin.startsWith('https://')) {
                    baseUrl = currentOrigin + '/api';
                    serverUrl = currentOrigin;
                    console.log('[API_CONFIG] Auto-detected ngrok/proxy origin:', baseUrl);
                }
            }
        } catch (e) {
            // Ignore errors in origin detection
        }
    })();

    // Configuration object exposed globally
    window.API_CONFIG = {
        /**
         * Get the API base URL (with /api suffix)
         * @returns {string}
         */
        getBaseUrl() {
            return baseUrl;
        },

        /**
         * Get the server base URL (without /api)
         * Used for image URLs, file uploads, etc.
         * @returns {string}
         */
        getServerUrl() {
            return serverUrl;
        },

        /**
         * Set a new API base URL
         * Updates both baseUrl and serverUrl, persists to localStorage
         * @param {string} url - Full API base URL (e.g., 'http://localhost:8000/api')
         */
        setBaseUrl(url) {
            if (!url || typeof url !== 'string') {
                console.error('API_CONFIG.setBaseUrl: Invalid URL provided');
                return;
            }
            
            // Normalize URL
            const normalized = url.trim().replace(/\/$/, '');
            
            // Ensure it ends with /api
            const finalUrl = normalized.endsWith('/api') ? normalized : normalized + '/api';
            
            baseUrl = finalUrl;
            serverUrl = deriveServerUrl(finalUrl);
            
            try {
                localStorage.setItem('API_BASE_URL', finalUrl);
            } catch (e) {
                console.warn('Could not persist API_BASE_URL to localStorage:', e);
            }
            
            console.log('[API_CONFIG] Updated:', { baseUrl, serverUrl });
        },

        /**
         * Reset to default configuration
         */
        resetToDefault() {
            baseUrl = DEFAULT_BASE_URL;
            serverUrl = DEFAULT_SERVER_URL;
            
            try {
                localStorage.removeItem('API_BASE_URL');
            } catch (e) {
                // ignore
            }
            
            console.log('[API_CONFIG] Reset to defaults');
        },

        /**
         * Get full URL for an API endpoint
         * @param {string} endpoint - API endpoint (e.g., '/products', '/cart')
         * @returns {string} Full URL
         */
        url(endpoint) {
            return `${baseUrl}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;
        },

        /**
         * Get full URL for a server resource (images, files)
         * @param {string} path - Resource path (e.g., '/storage/products/image.jpg')
         * @returns {string} Full URL
         */
        assetUrl(path) {
            return `${serverUrl}${path.startsWith('/') ? '' : '/'}${path}`;
        }
    };

    // Backward compatibility: expose as global functions
    window.baseUrl = function() { return window.API_CONFIG.getBaseUrl(); };
    window.serverUrl = function() { return window.API_CONFIG.getServerUrl(); };

    // Debug: Log config on load
    console.log('[API_CONFIG] Initialized:', {
        baseUrl: window.API_CONFIG.getBaseUrl(),
        serverUrl: window.API_CONFIG.getServerUrl()
    });

    // Expose helper for quick console debugging
    window.setApiUrl = (url) => window.API_CONFIG.setBaseUrl(url);
    window.resetApiUrl = () => window.API_CONFIG.resetToDefault();
})();