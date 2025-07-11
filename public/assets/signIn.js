'use strict'

const signInBtn = document.getElementById('sign-in-btn')
const emailInput = document.getElementById('email')
const passwordInput = document.getElementById('password')

document.querySelector('.err-email').style.visibility = 'hidden'
document.querySelector('.err-pass').style.visibility = 'hidden'

const validate = (email, password) => {
    if (!email) {
        document.querySelector('.err-email').style.visibility = 'visible'
        return false
    }

    if (!password) {
        document.querySelector('.err-pass').style.visibility = 'visible'
        return false
    }

    return true
}

signInBtn.addEventListener('click', async (e) => {
    e.preventDefault()

    const email = emailInput.value
    const password = passwordInput.value

    if (!validate(email, password)) {
        return
    }

    const res = await fetch('/dentists/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email,
            password
        })
    })

    const json = await res.json()

    if (json.status === 'success') {
        showModalSuccess('Login successful!')
        setTimeout(() => {
            window.location = '/dentists/'
        }, 1500)
    }
    else {
        showModalFail(json.message)
    }
})