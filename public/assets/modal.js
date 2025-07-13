'use strict'

let modal = document.querySelector('.modal-container')
const modalCloseBtn = document.querySelector('.modal-close-btn')

const showModalSuccess = (message) => {
    modal.style.display = 'flex'
    modal.classList.remove('modal--success', 'modal--fail')
    modal.classList.add('modal--success')
    modal.querySelector('.modal-title').innerText = message
}

const showModalFail = (message) => {
    modal.style.display = 'flex'
    modal.classList.remove('modal--success', 'modal--fail')
    modal.classList.add('modal--fail')
    modal.querySelector('.modal-title').innerText = message
}

modalCloseBtn.addEventListener('click', (e) => {
    modal.style.display = 'none'
})