/**
 * input-validator.js
 * Módulo global de validación visual de campos de entrada para el sistema.
 * Aplica restricciones en tiempo real para DNI (8 dígitos exactos), solo letras y solo números.
 */

(function () {
    'use strict';

    /**
     * Valida si un DNI tiene exactamente 8 dígitos numéricos.
     * @param {string} dni 
     * @returns {{ valid: boolean, message?: string }}
     */
    function validateDNI(dni) {
        var str = String(dni || '').trim();
        if (!str) {
            return { valid: false, message: 'El campo DNI es obligatorio.' };
        }
        if (!/^\d+$/.test(str)) {
            return { valid: false, message: 'El DNI debe contener solo números.' };
        }
        if (str.length !== 8) {
            return { valid: false, message: 'El DNI debe tener exactamente 8 números.' };
        }
        return { valid: true };
    }

    /**
     * Aplica la limpieza y restricciones en tiempo real sobre los campos.
     */
    function setupInputRestrictionListeners() {
        document.addEventListener('input', function (e) {
            var target = e.target;
            if (!target || target.tagName !== 'INPUT') return;

            var id = (target.id || '').toLowerCase();
            var name = (target.name || '').toLowerCase();
            var dataType = (target.getAttribute('data-type') || '').toLowerCase();
            var isDNI = dataType === 'dni' || id.indexOf('dni') !== -1 || name.indexOf('dni') !== -1;
            var isOnlyNumbers = target.hasAttribute('data-only-numbers') || dataType === 'phone' || target.type === 'tel' || id.indexOf('phone') !== -1 || id.indexOf('telefono') !== -1;
            var isOnlyLetters = target.hasAttribute('data-only-letters') || dataType === 'letters' || id.indexOf('nombre') !== -1 || id.indexOf('paterno') !== -1 || id.indexOf('materno') !== -1;

            // Restricción DNI: solo dígitos y máximo 8 caracteres
            if (isDNI) {
                var cleanDni = target.value.replace(/\D/g, '').slice(0, 8);
                if (target.value !== cleanDni) {
                    target.value = cleanDni;
                }
                
                // Indicar visualmente estado de longitud
                if (target.value.length > 0 && target.value.length < 8) {
                    target.setCustomValidity && target.setCustomValidity('El DNI debe tener exactamente 8 dígitos.');
                } else {
                    target.setCustomValidity && target.setCustomValidity('');
                }
                return;
            }

            // Restricción Solo Números
            if (isOnlyNumbers) {
                var cleanNumbers = target.value.replace(/\D/g, '');
                if (target.value !== cleanNumbers) {
                    target.value = cleanNumbers;
                }
                return;
            }

            // Restricción Solo Letras (letras, espacios, tildes y ñ)
            if (isOnlyLetters) {
                var cleanLetters = target.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
                if (target.value !== cleanLetters) {
                    target.value = cleanLetters;
                }
                return;
            }
        }, true);

        // Validación al desenfocar (blur) en campos DNI
        document.addEventListener('blur', function (e) {
            var target = e.target;
            if (!target || target.tagName !== 'INPUT') return;

            var id = (target.id || '').toLowerCase();
            var name = (target.name || '').toLowerCase();
            var dataType = (target.getAttribute('data-type') || '').toLowerCase();
            var isDNI = dataType === 'dni' || id.indexOf('dni') !== -1 || name.indexOf('dni') !== -1;

            if (isDNI) {
                var result = validateDNI(target.value);
                var errorEl = document.getElementById(target.id + '-error') || target.nextElementSibling;
                
                if (target.value.length > 0 && !result.valid) {
                    target.classList.add('is-invalid');
                    target.style.borderColor = '#dc2626';
                    if (errorEl && errorEl.classList && errorEl.classList.contains('input-error-msg')) {
                        errorEl.textContent = result.message;
                        errorEl.style.display = 'block';
                    }
                } else {
                    target.classList.remove('is-invalid');
                    target.style.borderColor = '';
                    if (errorEl && errorEl.classList && errorEl.classList.contains('input-error-msg')) {
                        errorEl.style.display = 'none';
                    }
                }
            }
        }, true);
    }

    // Inicializar listeners al cargar el script
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupInputRestrictionListeners);
    } else {
        setupInputRestrictionListeners();
    }

    // Exponer helper global
    window.InputValidator = {
        validateDNI: validateDNI
    };
})();
