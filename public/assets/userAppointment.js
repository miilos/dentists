'use strict';

let allServices = [];

// --- Fetch all available services from the API ---
async function fetchServices(dentistId) {
    try {
        const response = await fetch(`/dentists/api/dentists/${dentistId}/services`);
        const result = await response.json();
        allServices = result.data.services;
    } catch (error) {
        console.error('Failed to fetch services', error);
        alert('Failed to load services.');
    }
}

// --- Fetch all active appointments for the user ---
async function fetchActiveAppointments() {
    try {
        const response = await fetch('/dentists/api/appointments/active');
        const result = await response.json();
        console.log('Appointments fetched:', result.data.appointments);
        renderAppointments(result.data.appointments);
    } catch (error) {
        console.error('Failed to fetch appointments', error);
        alert('Failed to load appointments.');
    }
}

// --- Render the appointments in the table ---
function renderAppointments(appointments) {
    const tbody = document.querySelector('#appointmentsTable tbody');
    tbody.innerHTML = '';

    // If no appointments, show a message
    if (appointments.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center">No active appointments found.</td></tr>`;
        return;
    }

    appointments.forEach(appointment => {
        const scheduledTime = new Date(appointment.scheduled_at);

        // Can cancel if more than 4 hours ahead
        const canCancel = (scheduledTime - new Date()) > 4 * 60 * 60 * 1000;

        // Get service names joined by comma
        const servicesList = appointment.services && appointment.services.length > 0
            ? appointment.services.map(s => s.name).join(', ')
            : 'N/A';

        // Get service IDs for edit button data attribute
        const serviceIds = appointment.services ? appointment.services.map(s => s.id) : [];

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${scheduledTime.toLocaleString()}</td>
            <td>${appointment.duration}</td>
            <td>${servicesList}</td>
            <td>${appointment.notes || 'N/A'}</td>
            <td>
                <button class="btn edit-btn"
                    data-dentist-id="${appointment.dentist.id}"
                    data-id="${appointment.id}"
                    data-service-ids='${JSON.stringify(serviceIds)}'
                    data-notes="${appointment.notes || ''}"
                    data-datetime="${appointment.scheduled_at}">
                    Edit
                </button>
                <button class="btn cancel-btn"
                    data-code="${appointment.code}"
                    ${canCancel ? '' : 'disabled'}>
                    ${canCancel ? 'Cancel' : 'Cannot Cancel (<4h)'}
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Attach click handlers to Edit and Cancel buttons
    document.querySelectorAll('.edit-btn').forEach(btn =>
        btn.addEventListener('click', openEditForm)
    );
    document.querySelectorAll('.cancel-btn').forEach(btn =>
        btn.addEventListener('click', cancelAppointment)
    );
}

// --- Open the edit form and populate it with appointment data ---
async function openEditForm(e) {
    const button = e.target;

    const appointmentId = button.getAttribute('data-id');
    const serviceIds = JSON.parse(button.getAttribute('data-service-ids'));
    const notes = button.getAttribute('data-notes');
    const datetime = button.getAttribute('data-datetime');
    const dentistId = button.getAttribute('data-dentist-id')

    // Set appointment data in the form fields
    document.getElementById('editAppointmentId').value = appointmentId;
    document.getElementById('editDateTime').value = new Date(datetime).toLocaleString();
    document.getElementById('editNotes').value = notes;

    // Populate services checkboxes with selected services checked
    await populateServiceOptions(serviceIds, dentistId);

    // Show the edit form container
    document.getElementById('editFormContainer').style.display = 'flex';
}

// --- Populate the services as checkboxes in the edit form ---
async function populateServiceOptions(selectedIds, dentistId) {
    const container = document.getElementById('serviceSelection');
    container.innerHTML = '';

    await fetchServices(dentistId)

    if (allServices.length === 0) {
        container.innerHTML = '<p>No services available.</p>';
        return;
    }

    allServices.forEach(service => {
        const wrapper = document.createElement('div');
        wrapper.classList.add('form-check');

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = 'selectedServices';
        input.classList.add('form-check-input');
        input.value = service.id;
        input.id = `service-${service.id}`;

        // Check the box if the service is currently selected
        if (selectedIds.includes(service.id)) {
            input.checked = true;
        }

        const label = document.createElement('label');
        label.classList.add('form-check-label');
        label.htmlFor = input.id;
        label.textContent = `${service.name} (Duration: ${service.duration} min)`;

        wrapper.appendChild(input);
        wrapper.appendChild(label);
        container.appendChild(wrapper);
    });
}

// --- Handle the form submission to update appointment ---
document.getElementById('editForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const appointmentId = document.getElementById('editAppointmentId').value;

    // Collect all checked service IDs
    const checkedServices = Array.from(document.querySelectorAll('input[name="selectedServices"]:checked')).map(input => input.value);

    if (checkedServices.length === 0) {
        alert('Please select at least one service.');
        return;
    }

    const payload = {
        service_ids: checkedServices
    };

    try {
        // Send update request to API
        const response = await fetch(`/dentists/api/appointments/${appointmentId}/services`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message || 'Appointment updated successfully.');
            document.getElementById('editFormContainer').style.display = 'none';
            fetchActiveAppointments(); // Refresh appointment list
        } else {
            alert(result.message || 'Failed to update appointment.');
        }
    } catch (error) {
        console.error('Update error', error);
        alert('Could not update appointment.');
    }
});

// --- Cancel an appointment ---
async function cancelAppointment(e) {
    const code = e.target.getAttribute('data-code');

    if (!confirm('Are you sure you want to cancel this appointment?')) return;

    try {
        const response = await fetch(`/dentists/api/appointments/${code}/cancel`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message || 'Appointment cancelled.');
            fetchActiveAppointments();
        } else {
            alert(result.message || 'Cancellation failed.');
        }
    } catch (error) {
        console.error('Cancel error', error);
        alert('Could not cancel appointment.');
    }
}

// --- Initialize the page ---
document.addEventListener('DOMContentLoaded', async () => {
    await fetchActiveAppointments();

    // Hide the edit form initially
    document.getElementById('editFormContainer').style.display = 'none';

    // Make notes field read-only and grey background
    const notesField = document.getElementById('editNotes');
    notesField.readOnly = true;
    notesField.style.backgroundColor = '#f8f9fa';
});
