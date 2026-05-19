// Hardcoded API base path for your exact folder
const API_BASE = '/FYP/FYP%20COURSE%20REGISTRATION/api';

async function apiGet(endpoint) {
    const url = `${API_BASE}/${endpoint}`;
    console.log('API GET:', url);
    const response = await fetch(url, { credentials: 'include' });
    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(errorText || `HTTP ${response.status}`);
    }
    return response.json();
}

async function apiPost(endpoint, data) {
    const url = `${API_BASE}/${endpoint}`;
    console.log('API POST:', url);
    const response = await fetch(url, {
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