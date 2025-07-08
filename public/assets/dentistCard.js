fetch('/api/dentists')
    .then(res => res.json())
    .then(dentists => {
        const container = document.getElementById('dentist-card');
        dentists.forEach(d => {
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-4';
            col.innerHTML = `
                <div class="card h-100">
                    <img src="public/img/${d.photo}" class="card-img-top" alt="${d.name}">
                    <div class="card-body">
                        <h5 class="card-title">${d.name}</h5>
                        <p class="card-text"><strong>Specialization:</strong> ${d.specialization}</p>
                        <div class="mb-2">
                            ${(d.service || '').split(',').map(service => `<span class="badge rounded-pill bg-secondary">${service.trim()}</span>`).join('')}
                        </div>
                        <button class="btn"><a href="#">Book an appointment</a></button>
                    </div>
                </div>
            `;
            container.appendChild(col);
        });
    });
