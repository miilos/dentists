'use strict';

document.addEventListener('DOMContentLoaded', async () => {
    const dentistId = localStorage.getItem('dentistId') || '1';
    const container = document.getElementById('records-container');

    try {
        const res = await fetch(`/dentists/api/appointments/dentist/${dentistId}`);
        const json = await res.json();
        const appointments = json.data.appointments;

        if (!appointments.length) {
            container.innerHTML = '<p>No treatment records found.</p>';
            return;
        }

        const table = document.createElement('table');
        table.className = 'table table-striped';
        table.innerHTML = `
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Note</th>
                    <th>Missed</th>
                </tr>
            </thead>
            <tbody>
                ${appointments.map(app => `
                    <tr>
                        <td>${app.user?.first_name} ${app.user?.last_name}</td>
                        <td>${app.scheduled_at}</td>
                        <td>${app.note || 'No note'}</td>
                        <td>${app.missed ? 'Yes' : 'No'}</td>
                    </tr>
                `).join('')}
            </tbody>
        `;

        container.appendChild(table);
    } catch (err) {
        console.error('Error fetching records:', err);
        container.innerHTML = '<p>Failed to load records.</p>';
    }
});
