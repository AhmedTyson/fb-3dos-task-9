/**
 * Auth Module - Shared authentication utilities
 * Loaded globally on all pages
 */
const Auth = {
    getBaseUrl() {
        return window.API_CONFIG?.getBaseUrl() || 'http://localhost:8000/api';
    },

    getServerUrl() {
        return window.API_CONFIG?.getServerUrl() || 'http://localhost:8000';
    },

    getToken() {
        return localStorage.getItem("token") || localStorage.getItem("userToken") || localStorage.getItem("auth_token");
    },

    setToken(token) {
        localStorage.setItem("token", token);
    },

    getUser() {
        const userStr = localStorage.getItem("user");
        return userStr ? JSON.parse(userStr) : null;
    },

    setUser(user) {
        localStorage.setItem("user", JSON.stringify(user));
    },

    clearAuth() {
        localStorage.removeItem("token");
        localStorage.removeItem("userToken");
        localStorage.removeItem("auth_token");
        localStorage.removeItem("user");
        sessionStorage.clear();
    },

    isAdmin() {
        const user = this.getUser();
        return user && user.role === 'admin';
    },

    getHeaders() {
        const token = this.getToken();
        return {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "Authorization": `Bearer ${token}`,
            "ngrok-skip-browser-warning": "true"
        };
    },

    async login(email, password) {
        const response = await fetch(`${this.getBaseUrl()}/login`, {
            method: "POST",
            headers: { "Accept": "application/json", "Content-Type": "application/json" },
            body: JSON.stringify({ email, password })
        });
        const data = await response.json();
        
        if (response.ok && data.data?.token) {
            this.setToken(data.data.token);
            // Fetch and store user data
            const userResponse = await fetch(`${this.getBaseUrl()}/me`, {
                headers: this.getHeaders()
            });
            const userData = await userResponse.json();
            if (userData.data) {
                this.setUser(userData.data);
            }
            return { success: true, data };
        }
        return { success: false, message: data.message || "Invalid credentials" };
    },

    async register(name, email, password, passwordConfirmation) {
        const response = await fetch(`${this.getBaseUrl()}/register`, {
            method: "POST",
            headers: { "Accept": "application/json", "Content-Type": "application/json" },
            body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation })
        });
        const data = await response.json();
        
        if (response.ok) {
            return await this.login(email, password);
        }
        return { success: false, message: data.message || "Registration failed" };
    },

    async loadUserInfo() {
        try {
            const resp = await fetch(`${this.getBaseUrl()}/me`, { headers: this.getHeaders() });
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.data?.name) {
                const el = document.getElementById('userNameDisplay');
                if (el) el.textContent = data.data.name;
            }
        } catch (e) { /* silent */ }
    },

    async logout() {
        const token = this.getToken();
        if (token) {
            try {
                await fetch(`${this.getBaseUrl()}/logout`, {
                    method: "POST",
                    headers: this.getHeaders()
                });
            } catch (e) {
                console.error("Logout error:", e);
            }
        }
        this.clearAuth();
        const loginPage = window.location.pathname.includes('/admin/') ? '../frontend/login.html' : 'login.html';
        window.location.href = loginPage;
    },

    async forgotPassword(email) {
        const response = await fetch(`${this.getBaseUrl()}/forgot-password`, {
            method: "POST",
            headers: { "Accept": "application/json", "Content-Type": "application/json" },
            body: JSON.stringify({ email })
        });
        return response.json();
    },

    async resetPassword(token, email, password, passwordConfirmation) {
        const response = await fetch(`${this.getBaseUrl()}/reset-password`, {
            method: "POST",
            headers: { "Accept": "application/json", "Content-Type": "application/json" },
            body: JSON.stringify({ token, email, password, password_confirmation: passwordConfirmation })
        });
        return response.json();
    },

    requireAuth() {
        if (!this.getToken()) {
            window.location.href = "login.html";
            return false;
        }
        return true;
    },

    requireAdmin() {
        if (!this.requireAuth()) return false;
        if (!this.isAdmin()) {
            alert("Access denied. Admin only.");
            window.location.href = "home.html";
            return false;
        }
        return true;
    }
};

window.Auth = Auth;
document.addEventListener('DOMContentLoaded', () => Auth.loadUserInfo());