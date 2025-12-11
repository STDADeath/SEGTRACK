
/**
 * Lógica JavaScript para el formulario de Funcionarios (Registro y Actualización)
 *
 * Incluye:
 * 1. Validaciones en tiempo real (al interactuar, no al cargar).
 * 2. Pre-filtros de solo números (Teléfono, Documento).
 * 3. Lógica para determinar si la acción es 'registrar' o 'actualizar'.
 * 4. Manejo de SweetAlert para mensajes de éxito/error.
 */

$(document).ready(function () {

    // ====================================================================
    // 1. LÓGICA DE INTERACCIÓN (Para evitar errores al cargar)
    // ====================================================================

    // Esta clase auxiliar ayuda a saber si el usuario ya interactuó con el campo
    $('.form-control, .form-select').addClass('no-interactuado');

    /**
     * Función genérica para aplicar el estilo de validación (verde/rojo)
     * @param {string|HTMLElement} elementId - Selector o elemento DOM del input/select.
     * @param {boolean} isValid - True si es válido, False si es inválido.
     */
    function aplicarEstiloValidacion(elementId, isValid) {
        const input = $(elementId);
        
        // Si no ha interactuado, NO aplicamos clases de validación.
        if (input.hasClass('no-interactuado')) {
            return; 
        }
        
        // Quitar clases previas
        input.removeClass('is-valid is-invalid border-primary'); 
        
        if (isValid) {
            input.addClass('is-valid'); 
        } else {
            input.addClass('is-invalid'); 
        }
    }

    /**
     * Función para manejar la interacción inicial (se ejecuta con la primera tecla/cambio)
     * @param {HTMLElement} element - Elemento DOM que interactuó.
     */
    function handleInteraction(element) {
        // Quitar la clase de "no-interactuado"
        $(element).removeClass('no-interactuado');
        // Luego de quitar la clase, forzamos la validación para que aplique el estilo
        $(element).trigger('validate'); 
    }

    // Manejador de eventos para INPUTS (Nombre, Teléfono, Documento, Correo)
    $(".form-control").on('input', function () {
        if ($(this).hasClass('no-interactuado')) {
            handleInteraction(this);
        } else {
            $(this).trigger('validate');
        }
    });
    
    // Manejador de eventos para SELECTS (Cargo, Sede)
    $(".form-select").on('change', function () {
        if ($(this).hasClass('no-interactuado')) {
            handleInteraction(this);
        } else {
            $(this).trigger('validate');
        }
    });


    // ====================================================================
    // 2. DEFINICIÓN DE LAS VALIDACIONES REALES (Evento personalizado 'validate')
    // ====================================================================
    
    // 1. Validar Nombre
    $("#NombreFuncionario").on('validate', function () {
        // Permite letras (incluyendo tildes y ñ/Ñ) y espacios. Mínimo 3 caracteres.
        const regexNombre = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/;
        const nombre = $(this).val().trim();
        const isValid = nombre !== '' && regexNombre.test(nombre);
        aplicarEstiloValidacion(this, isValid);
    });

    // 2. Validar Teléfono
    $("#TelefonoFuncionario").on('validate', function () {
        // 🚨 Pre-filtro: Asegurar solo números y máximo 10
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); 
        const telefono = $(this).val();
        // Validación: debe tener exactamente 10 dígitos
        const isValid = telefono.length === 10;
        aplicarEstiloValidacion(this, isValid);
    });

    // 3. Validar Documento
    $("#DocumentoFuncionario").on('validate', function () {
        // 🚨 Pre-filtro: Asegurar solo números y máximo 11
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11); 
        const documento = $(this).val();
        // Validación: debe tener exactamente 11 dígitos
        const isValid = documento.length === 11;
        aplicarEstiloValidacion(this, isValid);
    });

    // 4. Validar Correo Electrónico
    $("#CorreoFuncionario").on('validate', function () {
        const correo = $(this).val().trim();
        // Regex básica de correo: evita espacios, debe tener @ y al menos un punto después.
        const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        // Validación: debe ser no vacío y cumplir regex
        const isValid = correo !== '' && regexCorreo.test(correo); 
        aplicarEstiloValidacion(this, isValid);
    });
    
    // 5. Validar Selects (Cargo y Sede)
    $("#CargoFuncionario, #IdSede").on('validate', function () {
        // Validación: debe tener un valor seleccionado (no la opción vacía o deshabilitada)
        const isValid = $(this).val() !== '' && $(this).val() !== null && $(this).val() !== '0';
        aplicarEstiloValidacion(this, isValid);
    });


    // ====================================================================
    // 3. LÓGICA DE ENVÍO (Submit)
    // ====================================================================
    
    $("#formRegistrarFuncionario").on("submit", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const inputsAValidar = [
            '#NombreFuncionario',
            '#TelefonoFuncionario',
            '#DocumentoFuncionario',
            '#CorreoFuncionario',
            '#CargoFuncionario',
            '#IdSede'
        ];
        
        let hayInvalidos = false;

        // 🚨 Forzar validación en todos al hacer submit
        inputsAValidar.forEach(id => {
            const input = $(id);
            // Quitar la clase de no-interactuado para que se muestre el feedback
            input.removeClass('no-interactuado');
            input.trigger('validate');

            if (input.hasClass('is-invalid')) {
                hayInvalidos = true;
            }
        });

        if (hayInvalidos) {
            Swal.fire({
                icon: 'error',
                title: 'Validación Pendiente',
                text: 'Por favor, corrija todos los campos marcados en rojo antes de continuar.',
                confirmButtonColor: '#dc3545'
            });
            return false;
        }

        // Determinar la acción (REGISTRAR o ACTUALIZAR)
        // Se asume que existe un <input type="hidden" name="IdFuncionario" id="IdFuncionario" value="0">
        const idFuncionario = $("#IdFuncionario").val(); 
        const accion = (idFuncionario && parseInt(idFuncionario) > 0) ? "actualizar" : "registrar";
        
        const btn = $("#btnRegistrar");
        const originalText = btn.html();

        // 🚨 Deshabilitar botón y mostrar spinner
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        btn.prop('disabled', true);

        // Serializar los datos del formulario y adjuntar la acción correcta
        const formData = $(this).serialize() + "&accion=" + accion;

        $.ajax({
            url: "../../Controller/ControladorFuncionarios.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: (accion === 'registrar' ? '¡Registro Exitoso!' : '¡Actualización Exitosa!'),
                        text: response.message,
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Limpiar formulario y estilos
                            $("#formRegistrarFuncionario")[0].reset();
                            $('.form-control, .form-select').removeClass('is-valid is-invalid').addClass('no-interactuado');
                            
                            // Asegurar que el ID oculto vuelva a 0 para futuros registros
                            $("#IdFuncionario").val(0); 
                            
                            // 💡 Nota: Aquí deberías recargar tu tabla de datos si aplica
                            // cargarTablaFuncionarios(); 
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al ' + accion,
                        text: response.message || response.error || "Error desconocido",
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Error AJAX:", error);
                console.log("Respuesta completa:", xhr.responseText);

                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    html: '<p>No se pudo conectar con el servidor.</p><small>Revisa la consola para más detalles técnicos.</small>',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Aceptar'
                });
            },
            complete: function () {
                // Habilitar botón y restaurar texto original
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });

        return false;
    });
});