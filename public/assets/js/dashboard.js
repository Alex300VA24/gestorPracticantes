document.addEventListener('DOMContentLoaded', async () => {
    let BASE_URL = '/gestorPracticantes/public/';

    // Navegación
    window.showPage = function (pageId, element) {
        // 🔹 Oculta todas las páginas y activa la actual
        document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));
        const pageEl = document.getElementById('page' + capitalize(pageId));
        if (pageEl) pageEl.classList.add('active');

        // Buscar el elemento ".option" si no fue pasado
        const optionEl = element ||
                        document.querySelector(`.option[data-page="${pageId}"]`) ||
                        document.querySelector(`#btn${capitalize(pageId)}`);

        // Limpiar clases .active en el menú y marcar la opción encontrada (si existe)
        document.querySelectorAll('.option').forEach(o => o.classList.remove('active'));
        if (optionEl) optionEl.classList.add('active');

        // 🔹 Guarda la sección actual en localStorage
        localStorage.setItem('currentPage', pageId);

        // 🔹 Si el usuario va al inicio, cargamos los datos
        if (pageId === 'inicio') {
            cargarInicio();
        }
    };


    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Restaurar la última página visitada (fallback robusto)
    (function restoreLastPage() {
        const lastPage = localStorage.getItem("currentPage") || 'inicio';

        // buscamos el botón/menú asociado
        const option = document.querySelector(`.option[data-page="${lastPage}"]`) ||
                    document.querySelector(`#btn${capitalize(lastPage)}`);

        const pageEl = document.getElementById('page' + capitalize(lastPage));

        if (pageEl) {
            // usamos showPage, que marca la opción activa y carga el contenido si es "inicio"
            showPage(lastPage, option);
        } else {
            // fallback: mostrar inicio
            const defaultOption = document.querySelector(`.option[data-page="inicio"]`) ||
                                document.querySelector(`#btnInicio`);
            showPage('inicio', defaultOption);
        }
    })();

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
            // Limpiar la página guardada para que en el próximo inicio no restaure la última página
            localStorage.removeItem('currentPage');
            window.location.href = BASE_URL + 'login';
        } catch (error) {
            alert('Error al cerrar sesión');
        }
    });


    // Ya no necesitamos el window.load para restaurar la página, lo hicimos arriba.
});


async function cargarInicio() {
    try {
        // Recuperar área del usuario logueado
        const areaID = sessionStorage.getItem('areaID');
        const nombreArea = sessionStorage.getItem('nombreArea');
        console.log(areaID, nombreArea);

        // Si RRHH, no se envía áreaID (deja ver todo)
        const params = (nombreArea === 'Gerencia de Recursos Humanos' || !areaID) 
            ? {} 
            : { areaID: areaID };

        // Llamada al backend con parámetro (usa tu helper API o fetch)
        const response = await api.obtenerDatosInicio(params);
        console.log("Datos del dashboard:", response);

        if (!response.success || !response.data) {
            console.error("Formato inválido en la respuesta del backend:", response);
            return;
        }

        const data = response.data;

        // === Actualizar estadísticas ===
        document.getElementById('totalPracticantes').textContent = data.totalPracticantes || 0;
        document.getElementById('pendientesAprobacion').textContent = data.pendientesAprobacion || 0;
        document.getElementById('practicantesActivos').textContent = data.practicantesActivos || 0;
        document.getElementById('asistenciaHoy').textContent = data.asistenciaHoy || 0;

        // === Actividad reciente ===
        const actividadDiv = document.getElementById('actividadReciente');
        actividadDiv.innerHTML = '';

        if (data.actividadReciente && data.actividadReciente.length > 0) {
            data.actividadReciente.forEach(act => {
                const div = document.createElement('div');
                div.classList.add('actividad-item');
                div.innerHTML = `
                    <strong>${act.Practicante}</strong> - ${act.Accion}
                    <span class="fecha">${act.Fecha}</span>
                `;
                actividadDiv.appendChild(div);
            });
        } else {
            actividadDiv.innerHTML = '<p>No hay actividad reciente.</p>';
        }

    } catch (error) {
        console.error('❌ Error al cargar el inicio:', error);
    }
}

async function ejecutarUnaVez(boton, accionAsync) {
    if (!boton) {
        console.warn("ejecutarUnaVez: botón no encontrado");
        return await accionAsync();
    }

    boton.disabled = true;
    const textoOriginal = boton.innerHTML;
    boton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Procesando...`;

    try {
        const resultado = await accionAsync();
        return resultado;
    } catch (error) {
        console.error("❌ Error en ejecutarUnaVez:", error);
        throw error;
    } finally {
        boton.disabled = false;
        boton.innerHTML = textoOriginal;
    }
}










