document.addEventListener('DOMContentLoaded', function () {
    // confirmar borrado con id específico (compatibilidad)
    var formBorrar = document.getElementById('formBorrar');
    if (formBorrar) {
        formBorrar.addEventListener('submit', function (e) {
            if (!confirm('¿Seguro que quieres borrar este elemento?')) {
                e.preventDefault();
            }
        });
    }

    // patrón genérico: cualquier form con data-confirm="mensaje"
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});