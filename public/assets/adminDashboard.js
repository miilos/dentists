'use strict'

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

const getUser = async () => {
    return (await fetchAPI('/dentists/api/me')).data.user
}

/**** USERS ****/

const getAllUsers = async () => {
    return await fetchAPI('/dentists/api/users')
}

const banUser = async (e) => {
    const btn = e.target
    const userId = btn.dataset.userId
    
    const res = await fetch(`/dentists/api/ban/${userId}`)
    const data = await res.json()

    if (data.status === 'fail') {
        showModalFail(data.message)
        return
    }

    btn.classList.remove('btn-ban')
    btn.classList.add('btn-unban')
    btn.innerText = 'Unban'
}

const unbanUser = async (e) => {
    const btn = e.target
    const userId = btn.dataset.userId
    
    const res = await fetch(`/dentists/api/unban/${userId}`)
    const data = await res.json()

    if (data.status === 'fail') {
        showModalFail(data.message)
        return
    }

    btn.classList.remove('btn-unban')
    btn.classList.add('btn-ban')
    btn.innerText = 'Ban'
}

const displayUsers = async () => {
    const users = (await getAllUsers()).data.users
    const tbody = document.getElementById('usersTable');
    tbody.innerHTML = '';

    if (users.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center">No users found.</td></tr>`;
        return;
    }

    users.forEach(user => {
        let btnHtml

        if (user.is_banned) {
            btnHtml = `<button class="btn btn-unban" data-user-id="${user.id}">Unban</button>`
        }
        else {
            btnHtml = `<button class="btn btn-ban" data-user-id="${user.id}">Ban</button>`
        }

        tbody.innerHTML += `
            <tr>
                <td>${user.id}</td>
                <td>${user.first_name}</td>
                <td>${user.last_name}</td>
                <td>${user.email}</td>
                <td>${user.num_missed_appointments}</td>
                <td>${btnHtml}</td>
            </tr>
        `
    });

    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const button = e.target

            if (button.innerText === 'Ban') {
                banUser(e)
                return
            }

            if (button.innerText === 'Unban') {
                unbanUser(e)
                return
            }
        })
    })
}

/**** DENTISTS ****/

const getAllDentists = async () => {
    return await fetchAPI('/dentists/api/dentists')
}

const openEditForm = (e) => {
    const btn = e.target

    const id = btn.dataset.dentistId
    const firstName = btn.dataset.dentistFname
    const lastName = btn.dataset.dentistLname
    const photo = btn.dataset.dentistPhoto

    document.getElementById('dentistId').value = id
    document.getElementById('dentistFirstName').value = firstName
    document.getElementById('dentistLastName').value = lastName
    document.getElementById('dentistPhoto').value = photo

    document.getElementById('editFormContainer').style.display = 'flex';
}

const deleteDentist = async (e) => {
    const btn = e.target
    const id = btn.dataset.dentistId

    await fetch(`/dentists/api/dentists/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })

    document.getElementById('editFormContainer').style.display = 'none'
    await displayDentists()
}

const displayDentists = async () => {
    const dentists = (await getAllDentists()).data.dentists
    const tbody = document.getElementById('dentistsTable');
    tbody.innerHTML = '';

    if (dentists.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center">No dentists found.</td></tr>`;
        return;
    }

    dentists.forEach(dentist => {
        tbody.innerHTML += `
            <tr>
                <td>${dentist.id}</td>
                <td>${dentist.first_name}</td>
                <td>${dentist.last_name}</td>
                <td>${dentist.email}</td>
                <td>
                    <button class="btn btn-edit btn-edit-dentist"
                        data-dentist-id="${dentist.id}"
                        data-dentist-fname="${dentist.first_name}"
                        data-dentist-lname="${dentist.last_name}"
                        data-dentist-photo="${dentist.photo}"
                    ">Edit</button>
                    <button class="btn btn-ban btn-delete-dentist" data-dentist-id="${dentist.id}">Delete</button>
                </td>
            </tr>
        `
    });

    document.querySelectorAll('.btn-edit-dentist').forEach(btn => {
        btn.addEventListener('click', openEditForm)
    })

    document.querySelectorAll('.btn-delete-dentist').forEach(btn => {
        btn.addEventListener('click', deleteDentist)
    })
}

/**** SERVICES ****/

const getAllServices = async () => {
    return await fetchAPI('/dentists/api/services')
}

const openServiceEditForm = (e) => {
    const btn = e.target

    const id = btn.dataset.serviceId
    const name = btn.dataset.serviceName
    const price = btn.dataset.servicePrice
    const duration = btn.dataset.serviceDuration

    document.getElementById('serviceId').value = id
    document.getElementById('serviceName').value = name
    document.getElementById('servicePrice').value = price
    document.getElementById('serviceDuration').value = duration

    document.getElementById('editServicesFormContainer').style.display = 'flex';
}

const deleteService = async (e) => {
    const btn = e.target
    const id = btn.dataset.serviceId

    await fetch(`/dentists/api/services/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })

    document.getElementById('editServicesFormContainer').style.display = 'none'
    await displayServices()
}

const displayServices = async () => {
    const services = (await getAllServices()).data.services
    const tbody = document.getElementById('servicesTable');
    tbody.innerHTML = '';

    if (services.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center">No services found.</td></tr>`;
        return;
    }

    services.forEach(service => {
        tbody.innerHTML += `
            <tr>
                <td>${service.id}</td>
                <td>${service.name}</td>
                <td>${service.price}</td>
                <td>${service.duration}</td>
                <td>
                    <button class="btn btn-edit btn-edit-service"
                        data-service-id="${service.id}"
                        data-service-name="${service.name}"
                        data-service-price="${service.price}"
                        data-service-duration="${service.duration}"
                    ">Edit</button>
                    <button class="btn btn-ban btn-delete-service" data-service-id="${service.id}">Delete</button>
                </td>
            </tr>
        `
    });

    document.querySelectorAll('.btn-edit-service').forEach(btn => {
        btn.addEventListener('click', openServiceEditForm)
    })

    document.querySelectorAll('.btn-delete-service').forEach(btn => {
        btn.addEventListener('click', deleteService)
    })
}


document.addEventListener('DOMContentLoaded', async (e) => {
    const user = await getUser()

    if (user.role !== 'admin') {
        window.location.href = '/dentists/public/signin.html'
    }

    await displayUsers()
    await displayDentists()
    await displayServices()
})

document.getElementById('editFormContainer').addEventListener('submit', async (e) => {
    e.preventDefault()

    const id = document.getElementById('dentistId').value
    const dentistFname = document.getElementById('dentistFirstName').value
    const dentistLname = document.getElementById('dentistLastName').value
    const dentistPhoto = document.getElementById('dentistPhoto').value

    const res = await fetchAPI(`/dentists/api/dentists/${id}`, 'POST', {
        'first_name': dentistFname,
        'last_name': dentistLname,
        'photo': dentistPhoto
    })

    if (res.status === 'success') {
        showModalSuccess('Update successful!')
        document.getElementById('editFormContainer').style.display = 'none'
        await displayDentists()
    }
    else if (res.status === 'fail') {
        showModalFail(res.message)
    }
})

document.getElementById('editServicesFormContainer').addEventListener('submit', async (e) => {
    e.preventDefault()

    const id = document.getElementById('serviceId').value
    const serviceName = document.getElementById('serviceName').value
    const servicePrice = document.getElementById('servicePrice').value
    const serviceDuration = document.getElementById('serviceDuration').value

    const res = await fetchAPI(`/dentists/api/services/${id}`, 'POST', {
        'name': serviceName,
        'price': servicePrice,
        'duration': serviceDuration
    })

    if (res.status === 'success') {
        showModalSuccess('Update successful!')
        document.getElementById('editServicesFormContainer').style.display = 'none'
        await displayServices()
    }
    else if (res.status === 'fail') {
        showModalFail(res.message)
    }
})