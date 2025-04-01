// Filtrar facturas por estado
function filtrarFacturas() {
    const filtroEstado = document.getElementById('filtroEstado').value.toLowerCase();
    const filas = document.querySelectorAll('#facturasTableBody tr');

    filas.forEach(fila => {
        const estado = fila.getAttribute('data-estado').toLowerCase();
        if (filtroEstado === '' || estado === filtroEstado) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

// Validación del formulario de carga (si existe)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const fileInput = document.querySelector('input[type="file"]');
            if (fileInput && !fileInput.value) {
                e.preventDefault();
                alert('Por favor, selecciona un archivo para subir.');
            }
        });
    }
});