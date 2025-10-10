<script src="/assets/js/api.js"></script>
    <script>
        async function logout() {
            if (confirm('¿Está seguro que desea cerrar sesión?')) {
                try {
                    await api.logout();
                    window.location.href = '/login';
                } catch (error) {
                    console.error('Error al cerrar sesión:', error);
                    window.location.href = '/login';
                }
            }
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Función para mostrar alertas
        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.querySelector('.main-content').insertBefore(
                alertDiv,
                document.querySelector('.main-content').firstChild
            );
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Función para mostrar loader
        function showLoader() {
            const loader = document.createElement('div');
            loader.className = 'loading-overlay';
            loader.id = 'globalLoader';
            loader.innerHTML = '<div class="loader"></div>';
            document.body.appendChild(loader);
        }

        function hideLoader() {
            const loader = document.getElementById('globalLoader');
            if (loader) {
                loader.remove();
            }
        }
    </script>
</body>
</html>