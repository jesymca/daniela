// Scripts personalizados para Michele C.A.
// Aquí puedes agregar JavaScript adicional si es necesario

document.addEventListener('DOMContentLoaded', function() {
    // Inicialización de tooltips de Bootstrap si se usan
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Puedes agregar más funcionalidades aquí
});