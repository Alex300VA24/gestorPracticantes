function mostrarSeccion(id) {
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });
    document.getElementById(id).classList.add('active');

    document.querySelectorAll('.menu li').forEach(li => li.classList.remove('active'));
    event.target.closest('li').classList.add('active');
}

function cerrarSesion() {
    if (confirm("¿Deseas cerrar sesión?")) {
        window.location.href = "logout.php";
    }
}
