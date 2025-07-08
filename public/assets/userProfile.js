document.addEventListener('DOMContentLoaded', () => {
    fetch('/api/user/me')
        .then(res => res.json())
        .then(user => {
            document.getElementById('first_name').value = user.first_name || '';
            document.getElementById('last_name').value = user.last_name || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('phone').value = user.phone || '';
        })
        .catch(err => console.error('Error loading:', err));

    // VALIDATION AND SENDING
    document.getElementById('profile-form').addEventListener('submit', function (e) {
        e.preventDefault();

        // Data from form
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const phone = document.getElementById('phone').value.trim();

        // Error span
        const errors = {
            firstName: document.getElementById('error-first-name'),
            lastName: document.getElementById('error-last-name'),
            phone: document.getElementById('error-phone'),
        };

        // Delete old errors
        Object.values(errors).forEach(span => span.textContent = '');

        // Validation
        const nameRegex = /^[A-Za-zČĆŽŠĐčćžšđ\s\-']+$/;
        const phoneRegex = /^[0-9]{10,12}$/;

        let hasError = false;

        if (!nameRegex.test(firstName)) {
            errors.firstName.textContent = "Only letters allowed in First Name.";
            hasError = true;
        }

        if (!nameRegex.test(lastName)) {
            errors.lastName.textContent = "Only letters allowed in Last Name.";
            hasError = true;
        }

        if (!phoneRegex.test(phone)) {
            errors.phone.textContent = "Phone must be 10-12 digits.";
            hasError = true;
        }

        if (hasError) return;

        const updatedUser = {
            first_name: firstName,
            last_name: lastName,
            phone: phone
        };


        fetch('NI OVDE NE ZNAM', {
            method: 'GET',
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
