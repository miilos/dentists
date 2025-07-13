'use strict'
fetch('/api/dentists')
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('dentist-card');
        const dentists = data.data.dentists;
        dentists.forEach(d => {
            const name = d.first_name + ' ' + d.last_name;
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-4';
            col.innerHTML = `
                <div class="card h-100">
                <img src="${d.photo}" class="card-img-top" alt="${name}">
                    <div class="card-body">
                        <h5 class="card-title">${name}</h5>
                        <p class="card-text"><strong>Specialization:</strong> ${d.specialization}</p>
                        <div class="mb-2">
                            ${(d.services || []).map(service => `<span class="badge rounded-pill bg-secondary">${service.name}</span>`).join('')}
                        </div>
                        <button class="btn"><a href="/public/makeAppointment.html?dentist=${d.id}" class="btn-link">Book an appointment</a></button>
                    </div>
                </div>
            `;
            container.appendChild(col);
        });
    })
    .catch(err => {
        console.error('Error fetch:', err);
    });
