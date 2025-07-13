'use strict'

const signInBtn = document.getElementById('sign-in-btn')
const emailInput = document.getElementById('email')
const passwordInput = document.getElementById('password')

const errEmail = document.querySelector('.err-email')
const errPass = document.querySelector('.err-pass')

errEmail.style.visibility = 'hidden'
errPass.style.visibility = 'hidden'

const validate = (email, password) => {
    let isValid = true;

    if (!email) {
        errEmail.style.visibility = 'visible'
        isValid = false
    } else {
        errEmail.style.visibility = 'hidden'
    }

    if (!password) {
        errPass.style.visibility = 'visible'
        isValid = false
    } else {
        errPass.style.visibility = 'hidden'
    }

    return isValid
}

signInBtn.addEventListener('click', async (e) => {
    e.preventDefault()

    const email = emailInput.value.trim()
    const password = passwordInput.value.trim()

    if (!validate(email, password)) {
        return
    }

    try {
        const res = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        })

        const json = await res.json()

        if (json.status === 'success') {
            const user = json.data.user

            // Save user data to localStorage
            localStorage.setItem('userId', user.id)
            localStorage.setItem('role', user.role)

            if (user.role === 'dentist') {
                localStorage.setItem('dentistId', user.id)
                showModalSuccess('Login successful!')
                setTimeout(() => {
                    window.location.href = '/public/dentistDashboard.html'
                }, 1000)
            } 
            else if (user.role === 'admin') {
                localStorage.setItem('adminId', user.id)
                showModalSuccess('Login successful!')
                setTimeout(() => {
                    window.location.href = '/public/adminDashboard.html'
                }, 1000)
            }
            else {
                showModalSuccess('Login successful!')
                setTimeout(() => {
                    window.location.href = '/index.html'
                }, 1000)
            }
        } else {
            showModalFail(json.message || 'Invalid login.')
        }

    } catch (error) {
        console.error('Login error:', error)
        showModalFail('Something went wrong. Please try again.')
    }
})
