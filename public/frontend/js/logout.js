/**
 * Shared logout utility
 * Used by home.js and other pages that need logout functionality
 */

window.logout = async function logout(e) {
    if (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
    }

    const token = localStorage.getItem("token");
    console.log("Token sending to logout:", token);

    if (token) {
        try {
            let response = await fetch(`${window.baseUrl()}/logout`, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token.trim()}`
                }
            });

            console.log("Logout Response Status:", response.status);
            
            if (response.ok) {
                console.log("Logged out successfully from server backend!");
            } else {
                let errData = await response.text();
                console.error("Backend refused logout:", errData);
            }
        } catch (error) {
            console.error("Network error - Failed to reach the server:", error);
        }
    } else {
        console.warn("No token found in localStorage. Skipping backend request.");
    }

    // Clear local storage
    localStorage.removeItem("token"); 
    localStorage.removeItem("userData");
    localStorage.removeItem("userToken");
    localStorage.removeItem("auth_token");
    sessionStorage.clear();

    // Redirect to login page
    window.location.href = "login.html";
};