/**
 * ============================================================================
 * PROYECTO ACADÉMICO - SISTEMA DE GESTIÓN ADMINISTRATIVO MICHELE C.A.
 * Archivo: assets/js/script.js
 * Descripción: Scripts cliente interactivos JavaScript (ES6+).
 * Funcionalidad destacada: Manipulación del DOM para alternar la visibilidad de
 * campos de contraseña (Password Show/Hide Toggle - "Ojito").
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    /**
     * Inicialización de Componentes de Interfaz
     * Permite habilitar los tooltips de Bootstrap 5 mediante inicialización manual del DOM.
     */
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    /**
     * ========================================================================
     * FUNCIONALIDAD: Alternar Visibilidad de Contraseña ("Ojito")
     * ========================================================================
     * Concepto Académico:
     * Los navegadores web renderizan los elementos de formulario <input type="password">
     * de forma enmascarada (puntos o asteriscos) por razones de seguridad visual.
     * Esta función permite al usuario alternar dinámicamente el atributo 'type'
     * del elemento HTML entre 'password' (oculto) y 'text' (visible).
     * 
     * Patrón de Implementación: Delegación de Eventos (Event Delegation)
     * Se escuchan los clics en botones identificados con el atributo `data-toggle="password"`
     * o la clase `.toggle-password-btn`.
     */
    function initPasswordToggles() {
        // Seleccionar todos los botones configurados para alternar la visibilidad de contraseña
        const toggleButtons = document.querySelectorAll('[data-toggle="password"], .toggle-password-btn');

        toggleButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                // 1. Obtener el campo de contraseña objetivo mediante atributo data-target o dentro del mismo input-group
                let targetSelector = button.getAttribute('data-target');
                let passwordInput = null;

                if (targetSelector) {
                    passwordInput = document.querySelector(targetSelector);
                } else {
                    // Buscar el input hermano más cercano dentro del contenedor .input-group
                    const parentGroup = button.closest('.input-group');
                    if (parentGroup) {
                        passwordInput = parentGroup.querySelector('input[type="password"], input[type="text"]');
                    }
                }

                if (!passwordInput) {
                    return; // Si no se encuentra el input asociado, no se realiza ninguna acción
                }

                // 2. Localizar el ícono de FontAwesome (<i class="fa-eye"> o <i class="fa-eye-slash">)
                const icon = button.querySelector('i');

                // 3. Alternar el tipo de entrada entre 'password' y 'text'
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                // 4. Actualizar el icono visual y los atributos de accesibilidad (ARIA)
                if (icon) {
                    if (isPassword) {
                        // Cambiar icono a ojo tachado (mostrar que se puede volver a ocultar)
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                        button.setAttribute('title', 'Ocultar contraseña');
                        button.setAttribute('aria-label', 'Ocultar contraseña');
                    } else {
                        // Cambiar icono a ojo abierto (mostrar que se puede visualizar)
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                        button.setAttribute('title', 'Mostrar contraseña');
                        button.setAttribute('aria-label', 'Mostrar contraseña');
                    }
                }
            });
        });
    }

    // Ejecutar la inicialización del manejador de contraseñas
    initPasswordToggles();
});