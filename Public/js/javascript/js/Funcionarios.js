 $(document).ready(function () {
        
        // ====================================================================
        // 🔥 CORRECCIÓN 2: Lógica de validación para empezar DESPUÉS del primer carácter
        // ====================================================================
        
        // Esta clase auxiliar ayuda a saber si el usuario ya interactuó
        $('.form-control, .form-select').addClass('no-interactuado');

        // Función genérica para aplicar el estilo de validación (verde/rojo)
        function aplicarEstiloValidacion(elementId, isValid) {
            const input = $(elementId);
            // Si no ha interactuado, no aplicamos clases de validación (no-interactuado es la clave)
            if (input.hasClass('no-interactuado')) {
                return; 
            }
            
            // Quitar clases previas (incluyendo el border-primary)
            input.removeClass('is-valid is-invalid border-primary'); 
            
            if (isValid) {
                input.addClass('is-valid'); 
            } else {
                input.addClass('is-invalid'); 
            }
        }

        // Función para manejar la interacción inicial (se ejecuta con la primera tecla)
        function handleInteraction(element) {
            $(element).removeClass('no-interactuado');
            // Luego de quitar la clase, forzamos la validación para que aplique el estilo
            $(element).trigger('validate'); 
        }

        // Manejador de eventos para inputs (Nombre, Teléfono, Documento, Correo)
        $(".form-control").on('input', function () {
            // Si es la primera interacción, quitamos la clase 'no-interactuado'
            if ($(this).hasClass('no-interactuado')) {
                handleInteraction(this);
            } else {
                // Si ya interactuó, simplemente disparamos el evento de validación
                $(this).trigger('validate');
            }
        });
        
        // Manejador de eventos para selects (Cargo, Sede)
        $(".form-select").on('change', function () {
            if ($(this).hasClass('no-interactuado')) {
                handleInteraction(this);
            } else {
                $(this).trigger('validate');
            }
        });


        // --------------------------------------------------------------------------
        // DEFINICIÓN DE LAS VALIDACIONES REALES (Usando un evento personalizado 'validate')
        // --------------------------------------------------------------------------
        
        // 1. Validar Nombre
        $("#NombreFuncionario").on('validate', function () {
            const regexNombre = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/;
            const nombre = $(this).val().trim();
            const isValid = nombre !== '' && regexNombre.test(nombre); // Debe ser no vacío y cumplir regex
            aplicarEstiloValidacion(this, isValid);
        });

        // 2. Validar Teléfono
        $("#TelefonoFuncionario").on('validate', function () {
            // Asegurar solo números y el max length
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); 
            const telefono = $(this).val();
            const isValid = telefono.length === 10;
            aplicarEstiloValidacion(this, isValid);
        });

        // 3. Validar Documento
        $("#DocumentoFuncionario").on('validate', function () {
            // Asegurar solo números y el max length
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11); 
            const documento = $(this).val();
            const isValid = documento.length === 11;
            aplicarEstiloValidacion(this, isValid);
        });

        // 4. Validar Correo Electrónico
        $("#CorreoFuncionario").on('validate', function () {
            const correo = $(this).val().trim();
            const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            // Debe ser no vacío y cumplir regex
            const isValid = correo !== '' && regexCorreo.test(correo); 
            aplicarEstiloValidacion(this, isValid);
        });
        
        // 5. Validar Selects
        $("#CargoFuncionario, #IdSede").on('validate', function () {
            const isValid = $(this).val() !== ''; // Debe tener un valor seleccionado
            aplicarEstiloValidacion(this, isValid);
        });


        // --------------------------------------------------------------------------
        // LÓGICA DE ENVÍO (Submit) - Fuerza todas las validaciones
        // --------------------------------------------------------------------------
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

            // Al hacer submit, forzamos la interacción para que todos los campos muestren su estado
            inputsAValidar.forEach(id => {
                const input = $(id);
                // Quitamos la clase de no-interactuado y forzamos la validación
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

            // ... (El código AJAX para el registro sigue aquí) ...
            const btn = $("#btnRegistrar");
            const originalText = btn.html();

            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
            btn.prop('disabled', true);

            const formData = $(this).serialize() + "&accion=registrar";

            $.ajax({
                url: "../../Controller/ControladorFuncionarios.php",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    // ... (Manejo de la respuesta y SweetAlert se mantiene igual) ...
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Registro Exitoso!',
                            text: response.message,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $("#formRegistrarFuncionario")[0].reset();
                                // Al resetear, volvemos a aplicar la clase 'no-interactuado' y quitamos estilos
                                $('.form-control, .form-select').removeClass('is-valid is-invalid').addClass('no-interactuado');
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al Registrar',
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
                        html: '<p>No se pudo conectar con el servidor</p><small>Revisa la consola para más detalles</small>',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Aceptar'
                    });
                },
                complete: function () {
                    btn.html(originalText);
                    btn.prop('disabled', false);
                }
            });

            return false;
        });
    });