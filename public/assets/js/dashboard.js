document.addEventListener('DOMContentLoaded', async () => {

    let BASE_URL = '/gestorPracticantes/public/';

    // Navegación
    window.showPage = function (pageId, element) {
        document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));
        document.getElementById('page' + capitalize(pageId)).classList.add('active');
        document.querySelectorAll('.option').forEach(o => o.classList.remove('active'));
        element.classList.add('active');
    };

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Logout
    document.getElementById('btnLogout').addEventListener('click', async () => {
        try{
            await api.logout();
            window.location.href = BASE_URL + 'login';
        }catch (error){
            alert('Error al cerrar sesion');
        }
    });


});
