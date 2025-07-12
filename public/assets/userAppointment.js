'use strict';

document.addEventListener('DOMContentLoaded', async () => {
    const appointments = await fetchAppointments();
    renderAppointments(appointments);
});

// Fetch only active appointments for the logged-in user
const fetchAppointments = async () => {
    try {
        const res = await fetch('/dentists/api/appointments/active');
        const json = await res.json();
        return json.data.appointments || [];
    } catch (error) {
        alert("Failed to fetch appointments.");
        return [];
    }
};

// Render appointments in the table
const renderAppointments = (appointments) => {
    const tbody = document.querySelector('#appointmentsTable tbody');
    tbody.innerHTML = '';

    if (appointments.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="5" class="text-center">No active appointments found.</td>`;
        tbody.appendChild(row);
        return;
    }

    appointments.forEach(app => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${formatDateTime(app.scheduled_at)}</td>
            <td>${app.duration} min</td>
            <td>${app.service_name || '-'}</td>
            <td>${app.note || '-'}</td>
            <td>
                <button class="btn btn-sm btn-warning me-1" onclick="openEditModal('${app.id}', '${app.scheduled_at}')">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="cancelAppointment('${app.code}', '${app.scheduled_at}')">Cancel</button>
            </td>
        `;

        tbody.appendChild(row);
    });
};

// Format datetime to readable string
const formatDateTime = (isoStr) => {
    const date = new Date(isoStr);
    return date.toLocaleString('en-GB'); // Format: DD/MM/YYYY, HH:MM
};

// Cancel appointment
const cancelAppointment = async (code, scheduledAt) => {
    const now = new Date();
    const appointmentTime = new Date(scheduledAt);
    const fourHoursFromNow = new Date(now.getTime() + 4 * 60 * 60 * 1000);

    if (appointmentTime < fourHoursFromNow) {
        alert("You can't cancel your appointment less than 4 hours before it starts!");
        return;
    }

    if (!confirm("Are you sure you want to cancel this appointment?")) return;

    try {
        const res = await fetch(`/dentists/api/appointments/${code}/cancel`, {
            method: 'DELETE'
        });

        const json = await res.json();

        if (res.ok) {
            alert(json.message);
            location.reload();
        } else {
            alert(json.message || "Error while cancelling appointment.");
        }
    } catch (err) {
        alert("Error while cancelling appointment.");
    }
};

// Open edit modal
const openEditModal = (appointmentId, currentDate) => {
    const input = prompt("Enter new date and time (YYYY-MM-DD HH:MM):", currentDate.replace("T", " ").slice(0, 16));
    if (!input) return;

    const formattedDate = new Date(input);

    if (isNaN(formattedDate.getTime())) {
        alert("Invalid date format.");
        return;
    }

    editAppointmentTime(appointmentId, formattedDate.toISOString());
};

// Send edit time request
const editAppointmentTime = async (appointmentId, newDateTimeIso) => {
    try {
        const res = await fetch(`/dentists/api/appointments/${appointmentId}/editTime`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ newDate: newDateTimeIso })
        });

        const json = await res.json();

        if (res.ok) {
            alert(json.message);
            location.reload();
        } else {
            alert(json.message || "Error while updating appointment.");
        }
    } catch (err) {
        alert("Failed to update appointment.");
    }
};
