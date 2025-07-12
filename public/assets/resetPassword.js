'use strict'

const passwordInput = document.getElementById('password')
const tokenInput = document.getElementById('reset_token')
const submitBtn = document.getElementById('submit-btn')

const sendTokenEmail = async () => {
    const res = await fetch('/dentists/api/forgotPassword')
    const data = await res.json()

    if (data.status === 'success') [
        showModalSuccess(data.message)
    ]
    else {
        showModalFail(data.message)
    }
}

const resetPassword = async () => {
    const password = passwordInput.value
    const token = tokenInput.value

    if (!password || !token) {
        showModalFail('You have to enter the password and the token!')
        return
    }

    if (password.length < 8) {
        showModalFail('Your password has to be at least 8 characters long!')
        return
    }

    const res = await fetch('/dentists/api/resetPassword', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            password,
            reset_token: token
        })
    })
    const data = await res.json()

    if (data.status === 'success') {
        showModalSuccess(data.message)
        window.location.href = '/dentists/public/profile.html'
    }
    else {
        showModalFail(data.message)
    }
}

document.addEventListener('DOMContentLoaded', async (e) => {
    await sendTokenEmail()
})

submitBtn.addEventListener('click', async (e) => {
    e.preventDefault()
    await resetPassword()
})