/**
 * Admin Auth Module - Admin panel authentication utilities
 * Uses admin_token for admin panel authentication
 */
/**
 * Admin Auth — now reads from shared `token` (set by frontend login)
 * Falls back to legacy `admin_token` for backward compatibility.
 */
const AdminAuth = {
    getBaseUrl() {
        return window.API_CONFIG?.getBaseUrl() || window.location.origin + '/api';
    },

    getServerUrl() {
        return window.API_CONFIG?.getServerUrl() || window.location.origin;
    },

    getToken() {
        return localStorage.getItem("token") || localStorage.getItem("admin_token");
    },

    setToken(token) {
        localStorage.setItem("token", token);
    },

    clearAuth() {
        localStorage.removeItem("token");
        localStorage.removeItem("admin_token");
        localStorage.removeItem("user");
        sessionStorage.clear();
    },

    isAdmin() {
        const token = this.getToken();
        return !!token;
    },

    getHeaders() {
        const token = this.getToken();
        return {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "Authorization": `Bearer ${token}`
        };
    },

    async getUser() {
        try {
            const resp = await fetch(`${this.getBaseUrl()}/me`, { headers: this.getHeaders() });
            if (!resp.ok) return null;
            const data = await resp.json();
            if (data.data) localStorage.setItem("user", JSON.stringify(data.data));
            return data.data;
        } catch { return null; }
    },

    async logout() {
        const token = this.getToken();
        if (token) {
            try {
                await fetch(`${this.getBaseUrl()}/logout`, {
                    method: "POST",
                    headers: this.getHeaders()
                });
            } catch (e) { console.error("Logout error:", e); }
        }
        this.clearAuth();
        window.location.href = "../frontend/login.html";
    },

    requireAuth() {
        if (!this.getToken()) {
            window.location.href = "../frontend/login.html";
            return false;
        }
        return true;
    }
};

window.AdminAuth = AdminAuth;