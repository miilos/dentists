'use strict';
document.addEventListener('DOMContentLoaded', () => {
    const userId = localStorage.getItem('userId');
    const role = localStorage.getItem('role');

    // Redirect if not logged in or not a regular user
    if (!userId || role !== 'user') {
        window.location.href = '/dentists/signin.html';
        return;
    }

    loadUserProfile();
    loadAppointments();

    const form = document.getElementById('profile-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();
        await submitProfileForm();
    });
});

async function loadUserProfile() {
    try {
        const res = await fetch('/dentists/api/me', {
            credentials: 'include'
        });
        if (!res.ok) throw new Error('Failed to load user profile');

        const json = await res.json();
        const user = json.data.user;

        document.getElementById('first_name').value = user.first_name || '';
        document.getElementById('last_name').value = user.last_name || '';
        document.getElementById('email').value = user.email || '';
        document.getElementById('phone').value = user.phone || '';

    } catch (err) {
        console.error(err);
        alert('Error loading user profile.');
    }
}

async function loadAppointments() {
    try {
        const res = await fetch('/dentists/api/appointments', { credentials: 'include' });

        if (!res.ok) {
            const text = await res.text();
            console.error('Fetch failed:', res.status, text);
            throw new Error('Failed to fetch appointments');
        }

        const json = await res.json();

        const appointments = json.data.appointments || [];
        const tableBody = document.querySelector('#ehr-table tbody');
        tableBody.innerHTML = '';

        if (appointments.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `<td colspan="4" class="text-center">No appointments found.</td>`;
            tableBody.appendChild(row);
            return;
        }

        appointments.forEach(app => {

            const date = app.scheduled_at ? new Date(app.scheduled_at).toLocaleDateString() : 'Unknown date';
            const dentistName = app.dentist && app.dentist.first_name && app.dentist.last_name
                ? `${app.dentist.first_name} ${app.dentist.last_name}`
                : 'Unknown dentist';
            const services = Array.isArray(app.services) && app.services.length > 0
                ? app.services.map(s => s.name || 'Unnamed service').join(', ')
                : 'Unknown services';
            const note = app.note || '-';

            const row = document.createElement('tr');
            row.innerHTML = `
        <td>${date}</td>
        <td>${dentistName}</td>
        <td>${services}</td>
        <td>${note}</td>
      `;
            tableBody.appendChild(row);
        });
    } catch (err) {
        console.error('Error loading appointments:', err);
    }
}



function clearErrors() {
    document.getElementById('error-first-name').textContent = '';
    document.getElementById('error-last-name').textContent = '';
    document.getElementById('error-phone').textContent = '';
}

async function submitProfileForm() {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const phone = document.getElementById('phone').value.trim();

    // Simple client validation
    let hasError = false;
    if (firstName.length < 2) {
        document.getElementById('error-first-name').textContent = 'First name must be at least 2 characters.';
        hasError = true;
    }
    if (lastName.length < 2) {
        document.getElementById('error-last-name').textContent = 'Last name must be at least 2 characters.';
        hasError = true;
    }
    if (phone.length < 6) {
        document.getElementById('error-phone').textContent = 'Phone number seems too short.';
        hasError = true;
    }
    if (hasError) return;

    try {
        const res = await fetch('/dentists/api/editProfile', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                phone: phone
            })
        });

        if (!res.ok) {
            const json = await res.json();
            if (json?.errors) {
                // If backend validation errors exist, show them
                if (json.errors.first_name) {
                    document.getElementById('error-first-name').textContent = json.errors.first_name;
                }
                if (json.errors.last_name) {
                    document.getElementById('error-last-name').textContent = json.errors.last_name;
                }
                if (json.errors.phone) {
                    document.getElementById('error-phone').textContent = json.errors.phone;
                }
            } else {
                throw new Error(json.message || 'Failed to update profile');
            }
            return;
        }

        const json = await res.json();
        alert(json.message || 'Profile updated successfully.');

    } catch (err) {
        console.error(err);
        alert('Error updating profile.');
    }
}
