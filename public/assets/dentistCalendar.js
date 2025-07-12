'use strict'
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        selectable: true,
        editable: true,
        slotMinTime: '08:00:00',
        slotMaxTime: '16:00:00',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay'
        },
        events: function (fetchInfo, successCallback, failureCallback) {
            fetch(`/dentists/api/appointments/dentist/${dentistId}`, {
                headers: { Authorization: `Bearer ${authToken}` }
            })
                .then(res => res.json())
                .then(data => {
                    const events = data.data.appointments.map(app => ({
                        id: app.id,
                        title: app.note || 'Appointment',
                        start: app.scheduled_at,
                        allDay: false
                    }));
                    successCallback(events);
                })
                .catch(failureCallback);
        },

        select: function (info) {
            const note = prompt("Enter availability note:");
            if (!note) return;

            const payload = {
                dentist_id: parseInt(dentistId),
                scheduled_at: info.startStr,
                duration: 30,
                price: 0,
                note: note,
                user_id: 1,
                services: [1]
            };

            fetch('/dentists/api/appointments', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            }).then(() => calendar.refetchEvents());
        },

        eventClick: function (info) {
            const appointmentId = info.event.id;
            if (confirm("Do you want to delete this appointment?")) {
                fetch(`/dentists/api/appointments/${appointmentId}/delete`, {
                    method: 'DELETE',
                    headers: { Authorization: `Bearer ${authToken}` }
                }).then(() => calendar.refetchEvents());
            }
        }
    });

    calendar.render();
});
