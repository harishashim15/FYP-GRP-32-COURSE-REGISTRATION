/**
 * common.js - Shared API utilities for UTM Course Registration System
 */

// Get the correct base path dynamically
function getApiBase() {
    let path = window.location.pathname;
    console.log('Current path:', path);
    let folderPath = path.substring(0, path.lastIndexOf('/') + 1);
    console.log('Folder path:', folderPath);
    let apiPath = folderPath + 'api';
    console.log('API base path:', apiPath);
    return apiPath;
}

const API_BASE = getApiBase();

async function apiGet(endpoint) {
    const url = `${API_BASE}/${endpoint}`;
    console.log('API GET Request URL:', url);
    
    const response = await fetch(url, {
        credentials: 'include'
    });
    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(errorText || `HTTP ${response.status}`);
    }
    return response.json();
}

async function apiPost(endpoint, data) {
    const url = `${API_BASE}/${endpoint}`;
    console.log('API POST Request URL:', url);
    
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