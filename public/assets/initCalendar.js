'use strict'

        const dentistid = 1 // Assuming this is defined elsewhere or will be dynamic

        const getAppointments = async () => {
                const res = await fetch(`/dentists/api/appointments/dentist/${dentistid}`)
                const json = await res.json()
                return json.data.appointments
        }

        // helper function to format date object to ISO string so calendar can use it
        const formatToIsoLocal = (date) => {
            const year = date.getFullYear()
            const month = (date.getMonth() + 1).toString().padStart(2, '0')
            const day = date.getDate().toString().padStart(2, '0')
            const hours = date.getHours().toString().padStart(2, '0')
            const minutes = date.getMinutes().toString().padStart(2, '0')
            const seconds = date.getSeconds().toString().padStart(2, '0')
            return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`
        }


        document.addEventListener('DOMContentLoaded', async function() {
            const calendarEl = document.getElementById('calendar');

            let calendar
            let currentSelectedSlot = null
            let currentHighlightEventId = 'selected-slot-id'

            function updateSelectedSlotHighlight(start, end) {
                const existingHighlightEvent = calendar.getEventById(currentHighlightEventId)
                if (existingHighlightEvent) {
                    existingHighlightEvent.remove()
                }

                // If start and end are provided, add the new highlight event
                if (start && end) {
                    calendar.addEvent({
                        id: currentHighlightEventId,
                        title: 'Selected',
                        start: formatToIsoLocal(start),
                        end: formatToIsoLocal(end),
                        display: 'background',
                        type: 'manual-selection'
                    });
                }
            }

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                nowIndicator: true,
                slotMinTime: '08:00:00',
                slotMaxTime: '16:00:00',
                slotDuration: '00:15:00',
                scrollTime: '08:00:00',
                headerToolbar: {
                    left: 'prev,next today',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                dateClick: function(info) {
                    calendar.changeView('timeGridDay', info.dateStr)
                    currentSelectedSlot = null
                    updateSelectedSlotHighlight(null, null);
                },
                events: async function(fetchInfo, successCallback, failureCallback) {
                    const appointments = await getAppointments()

                    const calendarEventAppointments = appointments.map(curr => {
                        // the calendar prefers getting ISO strings for time
                        const startTimeISOString = curr.scheduled_at.replace(' ', 'T')
                        const startDate = new Date(startTimeISOString)

                        const endDate = new Date(startDate)
                        endDate.setMinutes(endDate.getMinutes() + curr.duration)

                        return {
                            title: 'Booked appointment',
                            start: startTimeISOString,
                            end: formatToIsoLocal(endDate),
                            display: 'background',
                            classNames: ['unavailable'],
                            type: 'unavailable'
                        }
                    })

                    successCallback(calendarEventAppointments)
                },
                eventClick: function(info) {
                    if (info.event.id === currentHighlightEventId) {
                        return
                    }
                },
                select: function(info) {
                    const start = info.start
                    const singleSlotDurationMinutes = parseInt(calendar.getOption('slotDuration').substring(3, 5));

                    const calculatedEndTime = new Date(start);
                    calculatedEndTime.setMinutes(calculatedEndTime.getMinutes() + singleSlotDurationMinutes);

                    updateSelectedSlotHighlight(start, calculatedEndTime);

                    currentSelectedSlot = {
                        start: start,
                        end: calculatedEndTime,
                        duration: singleSlotDurationMinutes
                    };


                    // update the DOM on the page and the appointment object that's going to be sent to the API
                    // all of the other stuff related to that is in makeAppointment.js
                    const appointmentStart = new Date(info.start.getTime())
                    const appointmentEnd = new Date(info.end.getTime())

                    document.querySelector('.book-appointment-title').innerText = 
                        `${appointmentStart.getDate()}. ${appointmentStart.getMonth()+1}. ${appointmentStart.getFullYear()}. ${appointmentStart.getHours()}:${appointmentStart.getMinutes().toString().padStart(2, '0')}-${appointmentEnd.getHours()}:${appointmentEnd.getMinutes().toString().padStart(2, '0')}`

                    appointment.scheduled_at = `${appointmentStart.getFullYear()}.${appointmentStart.getMonth()+1}.${appointmentStart.getMonth()} ${appointmentStart.getHours()}:${appointmentStart.getMinutes().toString().padStart(2, '0')}:${appointmentStart.getSeconds()}`
                },
                selectAllow: function(selectInfo) {
                    const calendarEvents = calendar.getEvents()
                    const singleSlotDurationMs = parseInt(calendar.getOption('slotDuration').substring(3, 5)) * 60 * 1000;

                    // 1. Check for overlapping booked appointments (type 'unavailable')
                    const overlappingBookedAppointments = calendarEvents.filter(event =>
                        event.start < selectInfo.end &&
                        event.end > selectInfo.start &&
                        event.extendedProps.type === 'unavailable'
                    )
                    if (overlappingBookedAppointments.length > 0) {
                        return false
                    }

                    // 2. Prevent selection if it overlaps with our own existing highlight
                    const overlappingHighlight = calendarEvents.filter(event =>
                        event.start < selectInfo.end &&
                        event.end > selectInfo.start &&
                        event.id === currentHighlightEventId
                    );
                    if (overlappingHighlight.length > 0) {
                        return false;
                    }

                    // 3. Enforce single slot selection
                    const durationMs = selectInfo.end.getTime() - selectInfo.start.getTime();

                    if (Math.abs(durationMs - singleSlotDurationMs) > 50) {
                        return false;
                    }

                    return true
                }
            });

            calendar.render();
        });

