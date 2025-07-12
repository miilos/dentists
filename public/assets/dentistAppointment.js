'use strict'

const authToken = localStorage.getItem('authToken');
let dentistId
const appointmentsTable = document.getElementById("appointmentsTable");
const officeHoursForm = document.getElementById("officeHoursForm");
const officeHoursList = document.getElementById("officeHoursList");

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

let noteModal;
document.addEventListener("DOMContentLoaded", async () => {
    dentistId = (await getUser()).id

    loadAppointments()
    loadOfficeHours()

    noteModal = new bootstrap.Modal(document.getElementById('noteModal'));
});

function loadAppointments() {
    fetch(`/dentists/api/appointments/dentist/${dentistId}`)
        .then(res => res.json())
        .then(data => {
            appointmentsTable.innerHTML = "";
            data.data.appointments.forEach(app => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
        <td>${app.user.first_name} ${app.user.last_name}</td>
        <td>${app.scheduled_at}</td>
        <td>${app.duration} min</td>
        <td>${app.note || '-'}</td>
        <td>
          <a class="btn btn-sm btn-primary" href="healthRecord.html?user_id=${app.user_id}">View Record</a>
          <button class="btn btn-sm btn-secondary" onclick="editNote(${app.id}, '${app.note || ''}')">Edit Note</button>
        </td>
      `;
                appointmentsTable.appendChild(tr);
            });
        });
}

function editNote(appointmentId, currentNote) {
    const newNote = prompt("Enter treatment note:", currentNote);
    if (newNote === null) return;

    fetch(`/dentists/api/appointments/${appointmentId}/note`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ note: newNote })
    }).then(() => loadAppointments());
}

function loadOfficeHours() {
    fetch(`/dentists/api/appointments/dentist/${dentistId}`)
        .then(res => res.json())
        .then(data => {
            officeHoursList.innerHTML = "";
            data.data.appointments.forEach(a => {
                const li = document.createElement("li");
                li.className = "list-group-item";
                li.innerHTML = `
        ${a.scheduled_at} (${a.duration} min)
        <button class="btn btn-sm btn-danger" onclick="deleteOfficeHour(${a.id})">Delete</button>
      `;
                officeHoursList.appendChild(li);
            });
        });
}

officeHoursForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const start = document.getElementById("officeStart").value;
    const duration = document.getElementById("officeDuration").value;

    const payload = {
        dentist_id: parseInt(dentistId),
        scheduled_at: start,
        duration: parseInt(duration),
        price: 0,
        note: "Office Hour",
        services: []
    };

    fetch('/dentists/api/appointments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
        .then(() => {
            loadOfficeHours();
            officeHoursForm.reset();
        });
});

function deleteOfficeHour(id) {
    if (!confirm("Delete this time slot?")) return;

    fetch(`/dentists/api/appointments/${id}/delete`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${authToken}` }
    }).then(loadOfficeHours);
}

function editNote(appointmentId, currentNote) {
    document.getElementById("noteText").value = currentNote;
    document.getElementById("noteAppointmentId").value = appointmentId;
    noteModal.show();
}

document.getElementById("noteForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const appointmentId = document.getElementById("noteAppointmentId").value;
    const note = document.getElementById("noteText").value;

    console.log(appointmentId)
    
    fetch(`/dentists/api/appointments/${appointmentId}/note`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ note })
    })
        .then(() => {
            noteModal.hide();
            loadAppointments();
        });
});
