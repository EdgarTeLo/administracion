// scripts.js
// Archivo para funciones JavaScript personalizadas

// Ejemplo: Confirmación de eliminación
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            if (!confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                event.preventDefault();
            }
        });
    });
});