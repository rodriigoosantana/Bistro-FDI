//Gestiona las confirmaciones de borrado sin usar onsubmit inline.
document.addEventListener('DOMContentLoaded', function () {
    // Buscamos el formulario de borrado por su ID. Si existe, le añadimos un listener para confirmar antes de enviar.
    var formBorrar = document.getElementById('formBorrar'); 
    if (formBorrar) { // Solo si existe el formulario de borrado, añadimos el listener
        formBorrar.addEventListener('submit', function (e) { // Al enviar el formulario, preguntamos por confirmación
            if (!confirm('¿Seguro que quieres borrar este elemento?')) {
                e.preventDefault(); // Si el usuario cancela, prevenimos el envío del formulario y no se borra nada
            }
        });
    }
});