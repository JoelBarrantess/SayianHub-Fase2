document.addEventListener('DOMContentLoaded', function () {
    // --- Lógica para el Listado (crud_recursos.php) ---

    // Botones de eliminar
    const deleteButtons = document.querySelectorAll('.btn-delete-resource');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const resourceId = this.getAttribute('data-id');
            confirmarEliminar(resourceId);
        });
    });

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esto. Se eliminará el recurso permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../../../../private/proc/eliminar_recurso.php?id=' + id;
            }
        });
    }

    // --- Lógica para el Formulario (formulario_recurso.php) ---

    const formRecurso = document.getElementById('form-recurso');

    if (formRecurso) {
        formRecurso.addEventListener('submit', function (e) {
            if (!validarFormulario()) {
                e.preventDefault();
            }
        });

        // Preview de imagen
        const inputImagen = document.getElementById('input-imagen');
        const imgPreview = document.getElementById('img-preview');

        if (inputImagen && imgPreview) {
            inputImagen.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    function validarFormulario() {
        const nombre = document.getElementById('input-nombre').value.trim();
        const capacidad = document.getElementById('input-capacidad').value;

        if (nombre === '') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El nombre del recurso es obligatorio.'
            });
            return false;
        }

        if (capacidad < 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La capacidad no puede ser negativa.'
            });
            return false;
        }

        return true;
    }

    // Alertas de éxito/error desde PHP (URL params)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('msg')) {
        const msg = urlParams.get('msg');
        if (msg === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Operación realizada correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (msg === 'deleted') {
            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: 'El recurso ha sido eliminado.',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }
});
