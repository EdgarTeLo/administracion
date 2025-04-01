// scripts.js
// Funciones JavaScript para el sistema de administración

document.addEventListener('DOMContentLoaded', function() {
    // Confirmación de eliminación
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            if (!confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                event.preventDefault();
            }
        });
    });

    // Validación del formulario de subida de archivos
    const uploadForm = document.querySelector('form[action$="/facturas/upload"]');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(event) {
            const fileInput = document.querySelector('input[name="file"]');
            const fileType = document.querySelector('select[name="file_type"]').value;

            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Por favor, selecciona un archivo para subir.');
                event.preventDefault();
                return;
            }

            const file = fileInput.files[0];
            const maxFileSize = 10 * 1024 * 1024; // 10 MB
            if (file.size > maxFileSize) {
                alert('El archivo excede el tamaño máximo permitido de 10 MB.');
                event.preventDefault();
                return;
            }

            const allowedExtensions = {
                'xml': ['.xml'],
                'csv': ['.csv'],
                'pdf': ['.pdf'],
                'csv_facturas': ['.csv']
            };
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions[fileType].includes(fileExtension)) {
                alert(`El archivo debe tener la extensión ${allowedExtensions[fileType].join(' o ')} para el tipo seleccionado (${fileType}).`);
                event.preventDefault();
                return;
            }
        });
    }
});