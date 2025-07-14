'use strict';

document.getElementById("signupForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const firstName = document.getElementById("first_name").value.trim();
    const lastName = document.getElementById("last_name").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const password = document.getElementById("password").value.trim();

    const errors = {
        firstName: document.getElementById("error-first-name"),
        lastName: document.getElementById("error-last-name"),
        email: document.getElementById("error-email"),
        phone: document.getElementById("error-phone"),
        password: document.getElementById("error-password")
    };

    // Clear previous errors and hide them
    for (let key in errors) {
        errors[key].textContent = "";
        errors[key].classList.add('hidden-error');
    }

    const nameRegex = /^[A-Za-zČĆŽŠĐčćžšđ\s\-']+$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^[0-9]{10,12}$/;

    let hasError = false;

    if (!nameRegex.test(firstName)) {
        errors.firstName.textContent = "Only letters allowed in First Name.";
        errors.firstName.classList.remove('hidden-error');
        hasError = true;
    }

    if (!nameRegex.test(lastName)) {
        errors.lastName.textContent = "Only letters allowed in Last Name.";
        errors.lastName.classList.remove('hidden-error');
        hasError = true;
    }

    if (!emailRegex.test(email)) {
        errors.email.textContent = "Please enter a valid email.";
        errors.email.classList.remove('hidden-error');
        hasError = true;
    }

    if (!phoneRegex.test(phone)) {
        errors.phone.textContent = "Phone must be 10-12 digits.";
        errors.phone.classList.remove('hidden-error');
        hasError = true;
    }

    if (password.length < 8) {
        errors.password.textContent = "Password must be at least 8 characters.";
        errors.password.classList.remove('hidden-error');
        hasError = true;
    }

    if (hasError) {
        return;
    }

    try {
        const res = await fetch('/api/signup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                email,
                phone,
                password
            })
        });

        const json = await res.json();

        if (json.status === 'success') {
            showModalSuccess('Registration successful! Please sign in.');
            setTimeout(() => {
                window.location = '/public/signin.html';
            }, 1500);
        } else {
            showModalFail(json.message || 'Registration failed. Please try again.');
        }
    } catch (error) {
        console.error('Error during registration:', error);
        showModalFail('An error occurred. Please try again.');
    }
});
