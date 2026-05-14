/**
 * common.js - Shared API utilities for UTM Course Registration System
 * All pages must include this script before any page-specific JS.
 */

const API_BASE = '/api'; // Change if your API endpoints are located elsewhere (e.g., 'http://localhost/project/api')

// -------------------------------------------------------------------
// Generic fetch wrappers with error handling
// -------------------------------------------------------------------

/**
 * Perform a GET request to the API
 * @param {string} endpoint - API endpoint (e.g., 'students/list.php')
 * @returns {Promise<any>} Parsed JSON response
 */
async function apiGet(endpoint) {
    const response = await fetch(`${API_BASE}/${endpoint}`, {
        credentials: 'include' // send cookies/session
    });
    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(errorText || `HTTP ${response.status}`);
    }
    return response.json();
}

/**
 * Perform a POST request to the API
 * @param {string} endpoint - API endpoint
 * @param {object} data - Payload to send as JSON
 * @returns {Promise<any>} Parsed JSON response
 */
async function apiPost(endpoint, data) {
    const response = await fetch(`${API_BASE}/${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(data)
    });
    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(errorText || `HTTP ${response.status}`);
    }
    return response.json();
}

/**
 * Get current logged-in user information from session
 * @returns {Promise<{id: number, name: string, role: string, email: string}>}
 */
async function getCurrentUser() {
    return apiGet('session.php');
}

/**
 * Show a temporary notification (simple alert, can be replaced with toast)
 * @param {string} message 
 * @param {'success'|'error'|'info'} type 
 */
function showNotification(message, type = 'info') {
    // You can replace this with a nicer toast/alert
    alert(message);
}

// Export for use in other scripts (if using modules, but we attach to window)
window.apiGet = apiGet;
window.apiPost = apiPost;
window.getCurrentUser = getCurrentUser;
window.showNotification = showNotification;