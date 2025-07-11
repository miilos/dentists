document.addEventListener('DOMContentLoaded', () => {
    // Fetch logged-in user data
    fetch('dentist/api/me')
        .then(res => {
            if (!res.ok) throw new Error('Failed to fetch user data');
            return res.json();
        })
        .then(data => {
            const user = data.data.user;

            // Populate input fields with user data
            document.getElementById('first_name').value = user.first_name || '';
            document.getElementById('last_name').value = user.last_name || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('phone').value = user.phone || '';

            // Fetch user's appointment records
            return fetch('/api/appointments');
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to fetch appointments');
            return res.json();
        })
        .then(appointmentsData => {
            const appointments = appointmentsData.data.appointments || [];
            const tableBody = document.querySelector('#ehr-table tbody');
            tableBody.innerHTML = '';

            // If no appointments found, show message
            if (appointments.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="3" class="text-center">No appointments found.</td>`;
                tableBody.appendChild(row);
                return;
            }

            // Display each appointment in table
            appointments.forEach(app => {
                const date = new Date(app.scheduled_at || app.date).toLocaleDateString();
                const dentistName = app.dentist_name || 'Unknown dentist';
                const service = app.service || 'Unknown service';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${date}</td>
                    <td>${dentistName}</td>
                    <td>${service}</td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(err => console.error('Error loading:', err.message));

    // Handle profile update form submission
    document.getElementById('profile-form').addEventListener('submit', function (e) {
        e.preventDefault();

        // Get form values
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const phone = document.getElementById('phone').value.trim();

        // Error message spans
        const errors = {
            firstName: document.getElementById('error-first-name'),
            lastName: document.getElementById('error-last-name'),
            phone: document.getElementById('error-phone'),
        };

        // Clear previous errors
        Object.values(errors).forEach(span => span.textContent = '');

        // Validation patterns
        const nameRegex = /^[A-Za-zČĆŽŠĐčćžšđ\s\-']+$/;
        const phoneRegex = /^[0-9]{10,12}$/;

        let hasError = false;

        // Validate first name
        if (!nameRegex.test(firstName)) {
            errors.firstName.textContent = "Only letters allowed in First Name.";
            hasError = true;
        }

        // Validate last name
        if (!nameRegex.test(lastName)) {
            errors.lastName.textContent = "Only letters allowed in Last Name.";
            hasError = true;
        }

        // Validate phone number
        if (!phoneRegex.test(phone)) {
            errors.phone.textContent = "Phone must be 10-12 digits.";
            hasError = true;
        }

        if (hasError) return;

        // Prepare user data for update
        const updatedUser = {
            first_name: firstName,
            last_name: lastName,
            phone: phone
        };

        // Send update request
        fetch('/api/editProfile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updatedUser)
        })
            .then(res => {
                if (res.ok) {
                    alert('Data changed successfully!');
                } else {
                    return res.json().then(data => {
                        throw new Error(data.message || 'Error saving.');
                    });
                }
            })
            .catch(err => alert(err.message));
    });
});
