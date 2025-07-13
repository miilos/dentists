'use strict';

/**
 * Handles the user logout process.
 * Sends a POST request to the backend to invalidate the session and redirects the user.
 */
async function logoutUser() {
    try {
        // Send a POST request to the backend's logout endpoint
        // This endpoint should handle session invalidation on the server-side
        const res = await fetch('/dentists/api/logout', {
            method: 'POST',
            credentials: 'include' // Include cookies for session invalidation
        });

        if (!res.ok) {
            // If the logout request was not successful, parse the error message
            const errorData = await res.json();
            throw new Error(errorData.message || 'Failed to logout');
        }

        // If logout is successful on the server, redirect the user to the index page
        // Adjust this path if your index.html is located elsewhere relative to the current page
        window.location.href = 'dentists/index.html';

    } catch (err) {
        console.error('Error logging out:', err);
        // Display an error message to the user (consider a custom modal instead of alert)
        alert('An error occurred during logout.');
    }
}

// Attach the logout function to the logout link when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const logoutLink = document.getElementById('logout-link');
    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent the default link navigation
            logoutUser();       // Call the logout function
        });
    }
});
