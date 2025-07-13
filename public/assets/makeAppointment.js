'use strict'

const role = localStorage.getItem('role')
if (!role || role !== 'user') {
    window.location.href = '/public/signin.html'
}

const queryString = window.location.search
const urlParams = new URLSearchParams(queryString)

const dentistId = urlParams.get('dentist')
let totalCost = 0
let totalDuration = 0
let appointmentStart, appointmentEnd

let appointment = {
    dentist_id: dentistId,
    services: [],
    scheduled_at: null,
    total: 0,
    duration: 0
}

const fetchAPI = async (route, method = 'GET', data = {}) => {
    let res

    if (method !== 'GET') {
        res = await fetch(route, {
            method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
    }
    else {
        res = await fetch(route)
    }

    const json = await res.json()

    return json
}

// format duration from 180 to 1h 30min for examle
const formatDuration = (durationMins) => {
    let res = ``

    const hours = Math.floor(durationMins / 60)

    if (hours !== 0) {
        res += `${hours}h`
    }

    const minutes = durationMins % 60

    if (minutes !== 0) {
        res += ` ${minutes}min`
    }

    return res
}

const setAppointmentStart = (startTimeObj) => {
    appointmentStart = startTimeObj
}

const setAppointmentEnd = (endTimeObj) => {
    appointmentEnd = endTimeObj
}

const displayAppointmentStartAndEnd = () => {
    document.querySelector('.book-appointment-title').innerText = 
        `${appointmentStart.getDate()}. ${appointmentStart.getMonth()+1}. ${appointmentStart.getFullYear()}. ${appointmentStart.getHours()}:${appointmentStart.getMinutes().toString().padStart(2, '0')}-${appointmentEnd.getHours()}:${appointmentEnd.getMinutes().toString().padStart(2, '0')}`
}

const setDentistData = async () => {
    const dentistRes = await fetchAPI(`/api/dentists/${dentistId}`)
    const dentist = dentistRes.data.dentist

    // set the basic dentist info at the top of the page (picture, name)
    document.querySelector('.dentist-img').src = dentist.photo
    document.querySelector('.dentist-name').innerText = dentist.first_name + ' ' + dentist.last_name
    document.querySelector('.dentist-specialization').innerText = dentist.specialization

    // render all the dentist services
    dentist.services.forEach(service => {
        document.querySelector('.services').insertAdjacentHTML('beforeend', `
            <div class="service">
                <label for="service--${service.id}">
                    <h5 class="service-name">${service.name}</h4>
                </label>
                <p class="service-price">${service.price}e</p>
                <p class="service-duration">${formatDuration(service.duration)}</p>
                <input type="checkbox" class="service-cb" name="services" id="service--${service.id}" value=${service.id}>
            </div>    
        `)
    });

    // add event listeners to calculate total cost and duration each time a checkbox state changes
    document.querySelectorAll('.service-cb').forEach(curr => {
        curr.addEventListener('change', (e) => {
            const cb = e.target
            const service = dentist.services.find(s => s.id === Number(cb.value))
            const price = Number(service.price)
            const duration = Number(service.duration)
            
            // update the UI
            totalCost += cb.checked ? price : -price
            totalDuration += cb.checked ? duration : -duration

            document.querySelector('.total-amount').innerText = totalCost !== 0 ? `${totalCost}e` : ''
            document.querySelector('.total-duration').innerText = formatDuration(totalDuration)

            // update the object to be sent to the API
            appointment.total = totalCost
            appointment.duration = totalDuration

            if (appointmentStart) {
                setAppointmentEnd(new Date(appointmentStart.getTime() + totalDuration*60*1000))
                displayAppointmentStartAndEnd()
            }

            if (cb.checked) {
                appointment.services.push(cb.value)
            }
            else {
                appointment.services = appointment.services.filter(val => val !== cb.value)
            }
        })
    })
}

const validateData = () => {
    if (appointment.services.length === 0) {
        return {
            status: 'fail',
            message: 'You need to select at least one service!'
        }
    }

    if (appointment.services.scheduled_at === null) {
        return {
            status: 'fail',
            message: 'You need to select a date and time for the appointment!'
        }
    }

    if (appointment.services.total === 0 || appointment.services.duration === 0) {
        return {
            status: 'fail',
            message: 'You need to select at least one service!'
        }
    }

    let appointmentDate = new Date(appointment.scheduled_at)
    appointmentDate = new Date(appointmentDate.getTime() + totalDuration*60*1000)

    if (appointmentDate.getHours() >= 16) {
        return {
            status: 'fail',
            message: 'Your appointment must end before 16:00!'
        }
    }

    return {
        status: 'success'
    }
}

const getUser = async () => {
    return (await fetchAPI('/api/me')).data.user
}

document.addEventListener('DOMContentLoaded', async (e) => {
    await setDentistData()

    const user = await getUser()
    document.querySelector('.user-header').innerHTML = `You're making an appointment as: <b> ${user.first_name} ${user.last_name}</b>, with: `
})

document.querySelector('.book-btn').addEventListener('click', async (e) => {
    const validationRes = validateData()

    if (validationRes.status === 'fail') {
        showModalFail(validationRes.message)
        return
    }

    const res = await fetchAPI('/api/appointments', 'POST', appointment)

    if (res.status === 'success') {
        showModalSuccess('Appointment booked successfully!')
    }
    else if (res.status === 'fail') {
        showModalFail(res.message)
    }
})
