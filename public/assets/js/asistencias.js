document.addEventListener('DOMContentLoaded', () => {
    cargarAsistencias();
});

async function cargarAsistencias() {
    try {
        // 🔹 Obtener el área del usuario logueado
        const areaID = sessionStorage.getItem('areaID');
        if (!areaID) {
            console.warn("⚠️ No se encontró área del usuario.");
            return;
        }
        console.log("este es el valor de area id: ", areaID);

        // 🔹 Llamar al endpoint pasando el areaID
        const response = await api.listarAsistencias({ areaID: parseInt(areaID) });
        console.log(response);

        if (!response || !response.success || !Array.isArray(response.data.data)) {
            console.error("Error: formato de datos inválido", response);
            return;
        }

        const asistencias = response.data.data;
        const tbody = document.getElementById('tableAsistenciasBody');
        tbody.innerHTML = '';

        asistencias.forEach(row => {
            const tr = document.createElement('tr');

            // Calcular duración si hay entrada y salida
            let duracion = '-';
            if (row.HoraEntrada && row.HoraSalida) {
                const entrada = new Date(`1970-01-01T${row.HoraEntrada}`);
                const salida = new Date(`1970-01-01T${row.HoraSalida}`);
                const diffMs = salida - entrada;
                const horas = Math.floor(diffMs / (1000 * 60 * 60));
                const minutos = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                duracion = `${horas}h ${minutos}min`;
            }

            tr.innerHTML = `
                <td>${row.NombreCompleto}</td>
                <td>${row.Turno || '-'}</td>
                <td>${row.HoraEntrada || '-'}</td>
                <td>${row.HoraSalida || '-'}</td>
                <td>${duracion}</td>
                <td>${row.Estado}</td>
                <td>
                    <button class="btn-success" onclick="registrarEntrada(${row.PracticanteID})" id="btnEntrada">
                        Entrada <i class="fas fa-sign-in-alt"></i>
                    </button>
                    <button class="btn-warning" onclick="registrarSalida(${row.PracticanteID})" id="btnSalida">
                        Salida <i class="fas fa-sign-out-alt"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
        });

        // Actualizar estadísticas
        actualizarStats(asistencias);

    } catch (err) {
        console.error("Error al cargar asistencias:", err);
    }
}



async function registrarEntrada(practicanteID) {
    try {
        console.log({ practicanteID });
        const res = await api.registrarEntrada({ practicanteID });

        if (!res.success) {
            alert(res.message || 'Ocurrió un error al registrar la entrada.');
        } else {
            alert('Entrada registrada exitosamente.');
        }

        await cargarAsistencias();
    } catch (err) {
        console.error('Error en registrarEntrada:', err);
        // Si el backend envía un mensaje JSON, intentamos mostrarlo
        alert((err.message || err));
    }
}

async function registrarSalida(practicanteID) {
    try {
        console.log({ practicanteID });
        const res = await api.registrarSalida({ practicanteID });

        if (!res.success) {
            alert(res.message || 'Ocurrió un error al registrar la salida.');
        } else {
            alert('Salida registrada exitosamente.');
        }

        await cargarAsistencias();
    } catch (err) {
        console.error('Error en registrarSalida:', err);
        alert('Error: ' + (err.message || err));
    }
}




function actualizarStats(data) {
    const presentes = data.filter(d => d.HoraEntrada && !d.HoraSalida).length;
    const ausentes = data.filter(d => !d.HoraEntrada).length;
    document.getElementById('presentesHoy').textContent = presentes;
    document.getElementById('ausentesHoy').textContent = ausentes;
}
