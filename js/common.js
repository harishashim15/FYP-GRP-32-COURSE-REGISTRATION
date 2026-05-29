// Get the base path from the current HTML file's location
const API_BASE = 'api';
console.log('API_BASE:', API_BASE);

console.log('Detected API_BASE:', API_BASE);

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