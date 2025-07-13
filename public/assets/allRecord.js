'use strict';

document.addEventListener('DOMContentLoaded', async () => {
    const dentistId = localStorage.getItem('dentistId');
    const container = document.getElementById('records-container');

    try {
        const res = await fetch(`/api/medicalRecords/dentist/${dentistId}/allPatients`);
        if (!res.ok) throw new Error('Failed to fetch records from API');

        const json = await res.json();
        const appointments = json.data.appointments;

        if (!appointments.length) {
            container.innerHTML = '<p>No medical records found.</p>';
            return;
        }

        const table = document.createElement('table');
        table.className = 'table table-striped';

        table.innerHTML = `
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Dentist</th>
                    <th>Services</th>
                    <th>Date & Time</th>
                    <th>Duration (min)</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                ${appointments.map(app => `
                    <tr>
                        <td>${app.user_first_name} ${app.user_last_name}</td>
                        <td>${app.dentist_first_name} ${app.dentist_last_name}</td>
                        <td>${app.services?.length ? app.services.join(', ') : 'No services'}</td>
                        <td>${app.scheduled_at}</td>
                        <td>${app.duration ?? ''}</td>
                        <td>${app.note?.trim() ? app.note : 'No note'}</td>
                    </tr>
                `).join('')}
            </tbody>
        `;

        container.appendChild(table);
    } catch (err) {
        console.error('Error loading records:', err);
        container.innerHTML = '<p>Error loading medical records.</p>';
    }
});
