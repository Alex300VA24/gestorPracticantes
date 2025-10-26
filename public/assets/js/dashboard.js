document.addEventListener('DOMContentLoaded', async () => {
    let BASE_URL = '/gestorPracticantes/public/';

    // 🔹 Al cargar el dashboard, también se puede mostrar el inicio
    await cargarInicio();


    // Navegación
    window.showPage = function (pageId, element) {
        document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));
        document.getElementById('page' + capitalize(pageId)).classList.add('active');
        document.querySelectorAll('.option').forEach(o => o.classList.remove('active'));
        element.classList.add('active');

        // 🔹 Si el usuario va al inicio, cargamos los datos
        if (pageId === 'inicio') {
            cargarInicio();
        }
    };

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Logout
    document.getElementById('btnLogout').addEventListener('click', () => {
        const modal = document.getElementById('logoutModal');
        modal.style.display = 'flex';
    });

    document.getElementById('cancelLogout').addEventListener('click', () => {
        const modal = document.getElementById('logoutModal');
        modal.style.display = 'none';
    });

    document.getElementById('confirmLogout').addEventListener('click', async () => {
        const modal = document.getElementById('logoutModal');
        modal.style.display = 'none';
        try {
            await api.logout();
            window.location.href = BASE_URL + 'login';
        } catch (error) {
            alert('Error al cerrar sesión');
        }
    });

    
});

async function cargarInicio() {
    try {
        const response = await api.obtenerDatosInicio();

        // Validar estructura del JSON
        if (!response.success || !response.data) {
            console.error("Formato inválido en la respuesta del backend:", response);
            return;
        }

        // Extraemos los datos reales
        const data = response.data;

        // === Actualizar estadísticas ===
        document.getElementById('totalPracticantes').textContent = data.totalPracticantes || 0;
        document.getElementById('pendientesAprobacion').textContent = data.pendientesAprobacion || 0;
        document.getElementById('practicantesActivos').textContent = data.practicantesActivos || 0;
        document.getElementById('asistenciaHoy').textContent = data.asistenciaHoy || 0;

        // === Actividad reciente (opcional si más adelante la agregas) ===
        const actividadDiv = document.getElementById('actividadReciente');
        actividadDiv.innerHTML = '';

        if (data.actividadReciente && data.actividadReciente.length > 0) {
            data.actividadReciente.forEach(act => {
                const div = document.createElement('div');
                div.classList.add('actividad-item');
                div.innerHTML = `
                    <strong>${act.practicante}</strong> - ${act.accion}
                    <span class="fecha">${act.fecha}</span>
                `;
                actividadDiv.appendChild(div);
            });
        } else {
            actividadDiv.innerHTML = '<p>No hay actividad reciente.</p>';
        }

    } catch (error) {
        console.error('Error al cargar el inicio:', error);
    }
}

