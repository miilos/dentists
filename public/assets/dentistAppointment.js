'use strict';

document.addEventListener('DOMContentLoaded', async () => {
    const calendarEl = document.getElementById('calendar');

    const appointmentForm = document.getElementById('appointmentForm');
    const appointmentIdInput = document.getElementById('appointmentId');
    const appointmentDateInput = document.getElementById('appointmentDate');
    const appointmentNotesInput = document.getElementById('appointmentNotes');
    const missedCheckbox = document.getElementById('missedCheckbox');
    const deleteBtn = document.getElementById('deleteBtn');

    const dentistId = localStorage.getItem('dentistId');

    if (!dentistId) {
        alert('Dentist not logged in!');
        return;
    }

    const toDatetimeLocal = (dateStr) => {
        const dt = new Date(dateStr);
        const off = dt.getTimezoneOffset();
        const local = new Date(dt.getTime() - off * 60 * 1000);
        return local.toISOString().slice(0, 16);
    };

    const toBackendDateTime = (datetimeLocal) => {
        return datetimeLocal.replace('T', ' ') + ':00';
    };

    async function fetchAppointments() {
        const res = await fetch(`/dentists/api/appointments/dentist/${dentistId}`);
        if (!res.ok) throw new Error('Failed to load appointments');
        const data = await res.json();
        return data.data.appointments;
    }

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        nowIndicator: true,
        selectable: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: async (info, successCallback, failureCallback) => {
            try {
                const appointments = await fetchAppointments();
                const events = appointments.map(app => ({
                    id: app.id.toString(),
                    title: app.note || 'Appointment',
                    start: app.scheduled_at.replace(' ', 'T'),
                    end: new Date(new Date(app.scheduled_at).getTime() + app.duration * 60000).toISOString(),
                    extendedProps: {
                        note: app.note || '',
                        missed: false,
                        user_id: app.user?.id || null
                    },
                    color: '#3c8dbc'
                }));
                successCallback(events);
            } catch (e) {
                failureCallback(e);
            }
        },
        eventClick: (info) => {
            const event = info.event;
            const now = new Date();

            appointmentIdInput.value = event.id;
            appointmentDateInput.value = toDatetimeLocal(event.startStr);
            appointmentDateInput.disabled = event.start < now; // ne menjaj prošlost
            appointmentNotesInput.value = event.extendedProps.note;
            missedCheckbox.checked = false; // manualno unosi
            missedCheckbox.disabled = false;

            appointmentForm.scrollIntoView({ behavior: 'smooth' });
        }
    });

    calendar.render();

    appointmentForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = appointmentIdInput.value;
        const newDate = appointmentDateInput.value;
        const note = appointmentNotesInput.value.trim();
        const missed = missedCheckbox.checked;

        if (!id) {
            alert('Select an appointment first.');
            return;
        }

        try {
            const now = new Date();
            const selectedDate = new Date(newDate);

            // Ako je budući datum, dozvoljena izmena datuma
            if (selectedDate > now) {
                const resTime = await fetch(`/dentists/api/appointments/${id}/editTime`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ newDate: toBackendDateTime(newDate) })
                });
                if (!resTime.ok) throw new Error('Failed to update appointment time');
            }

            // Uvek dozvoljena izmena beleške
            const resNote = await fetch(`/dentists/api/appointments/${id}/note`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ note })
            });
            if (!resNote.ok) throw new Error('Failed to update appointment note');

            // Ako je checkbox čekiran, evidentiraj propušten termin
            if (missed) {
                const userId = calendar.getEventById(id).extendedProps.user_id;
                if (!userId) throw new Error('Missing user ID for appointment');
                const resMissed = await fetch(`/dentists/api/missedAppointment/${userId}`, {
                    method: 'GET'
                });
                if (!resMissed.ok) throw new Error('Failed to record missed appointment');
            }

            alert('Appointment updated successfully!');
            calendar.refetchEvents();
        } catch (err) {
            alert(err.message);
        }
    });

    deleteBtn.addEventListener('click', async () => {
        const id = appointmentIdInput.value;
        if (!id) {
            alert('Select an appointment first.');
            return;
        }

        if (!confirm('Are you sure you want to delete this appointment?')) return;

        try {
            const res = await fetch(`/dentists/api/appointments/${id}/delete`, {
                method: 'DELETE'
            });
            if (!res.ok) throw new Error('Failed to delete appointment');

            alert('Appointment deleted successfully!');
            appointmentForm.reset();
            calendar.refetchEvents();
        } catch (err) {
            alert(err.message);
        }
    });
});
