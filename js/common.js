// Dynamically determine the API base path
const scripts = document.getElementsByTagName('script');
const currentScript = scripts[scripts.length - 1];
const scriptSrc = currentScript.src;
const scriptPath = scriptSrc.substring(0, scriptSrc.lastIndexOf('/'));
const API_BASE = scriptPath + '/api';

console.log('API_BASE:', API_BASE); // For debugging – remove later

async function apiGet(endpoint) {
    const response = await fetch(`${API_BASE}/${endpoint}`, { credentials: 'include' });
    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(errorText || `HTTP ${response.status}`);
    }
    return response.json();
}

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

async function getCurrentUser() {
    return apiGet('session.php');
}

window.apiGet = apiGet;
window.apiPost = apiPost;
window.getCurrentUser = getCurrentUser;