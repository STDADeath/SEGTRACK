// Public/js/javascript/js/ValidacionesSede.js

$(document).ready(function () {

    console.log("=== SISTEMA DE REGISTRO/EDICIÓN DE SEDE INICIADO ===");

    // ============================
    // FUNCIONES VISUALES
    // ============================

    function marcarInvalido(campo) {
        campo.attr("style",
            "border: 2px solid #ef4444 !important;" +
            "box-shadow: 0 0 0 0.25rem rgba(239, 68, 68, 0.25) !important;"
        );
    }

    function marcarValido(campo) {
        campo.attr("style",
            "border: 2px solid #10b981 !important;" +
            "box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25) !important;"
        );
    }

    function marcarNeutral(campo) {
        campo.attr("style",
            "border: 1px solid #ced4da !important;" +
            "box-shadow: none !important;"
        );
    }

    // Inicializa la validación al cargar la página (útil para el modo Edición)
    function inicializarValidacion() {
        // Ejecutar las validaciones en modo 'change' para marcar los campos precargados
        $("#TipoSede").trigger('input');
        $("#Ciudad").trigger('input');
        $("#IdInstitucion").trigger('change');
        
        // Si no hay valor o la validación es incompleta, dejarlos neutrales por defecto
        marcarNeutral($("#TipoSede"));
        marcarNeutral($("#Ciudad"));
        marcarNeutral($("#IdInstitucion"));
    }

    // Se ejecuta al inicio para limpiar y validar los campos cargados en modo edición
    inicializarValidacion();


    // ============================
    // VALIDACIÓN EN TIEMPO REAL
    // ============================

    function soloTexto(valor) {
        return /^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/.test(valor);
    }

    // 1. Tipo de Sede
    $("#TipoSede").on("input", function () {
        let campo = $(this);
        let valor = campo.val().replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ ]/g, "");
        campo.val(valor);

        if (valor.length >= 3 && soloTexto(valor)) {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 2. Ciudad
    $("#Ciudad").on("input", function () {
        let campo = $(this);
        let valor = campo.val().replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ ]/g, "");
        campo.val(valor);

        if (valor.length >= 3 && soloTexto(valor)) {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 3. Select Institución
    $("#IdInstitucion").on("change", function () {
        let campo = $(this);
        if (campo.val() !== "") {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });


    // ============================
    // ENVÍO AJAX (REGISTRO Y EDICIÓN)
    // ============================

    $("#formRegistrarSede").submit(function (e) {
        e.preventDefault();

        let errores = [];

        const tipo = $("#TipoSede");
        const ciudad = $("#Ciudad");
        const institucion = $("#IdInstitucion");

        // --- VALIDACIONES FINALES ---
        
        // Tipo de Sede
        if (tipo.val().length < 3 || !soloTexto(tipo.val())) {
            errores.push("• El tipo de sede debe contener solo letras (mínimo 3 caracteres).");
            marcarInvalido(tipo);
        } else {
            marcarValido(tipo);
        }

        // Ciudad
        if (ciudad.val().length < 3 || !soloTexto(ciudad.val())) {
            errores.push("• La ciudad debe contener solo letras (mínimo 3 caracteres).");
            marcarInvalido(ciudad);
        } else {
            marcarValido(ciudad);
        }

        // Institución
        if (institucion.val() === "") {
            errores.push("• Debe seleccionar una institución.");
            marcarInvalido(institucion);
        } else {
            marcarValido(institucion);
        }

        if (errores.length > 0) {
            Swal.fire({
                icon: "error",
                title: "Error de validación",
                html: "<div style='text-align:left;'>" + errores.join("<br>") + "</div>",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
            return;
        }
        
        // --- LÓGICA DE ENVÍO Y EDICIÓN ---

        // 🚩 Obtener la acción del campo oculto
        const accion = $("#accion").val();
        
        // Personalizar mensajes y redirección según la acción
        let titleLoading = accion === 'editar' ? 'Actualizando sede...' : 'Registrando sede...';
        let titleSuccess = accion === 'editar' ? '¡Actualización Exitosa!' : '¡Registro Exitoso!';
        
        // Loading
        Swal.fire({
            title: titleLoading,
            html: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const btn = $(this).find("button[type='submit']");
        const originalText = btn.html();
        btn.prop("disabled", true);
        
        // Serializar todos los datos del formulario, incluyendo 'accion' e 'IdSede'
        // NOTA: Ya no necesita concatenar "&accion=registrar" al final, ya que el campo oculto
        // <input type="hidden" name="accion" id="accion" value="registrar/editar">
        // ya se incluye con $(this).serialize()
        const formData = $(this).serialize();


        $.ajax({
            url: '../../Controller/ControladorSede.php',
            type: "POST",
            data: formData, // Envía 'accion=registrar' O 'accion=editar&IdSede=X...'
            dataType: "json",

            success: function (response) {
                Swal.close();

                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: titleSuccess,
                        text: response.message,
                        confirmButtonColor: "#10b981"
                    }).then(() => {
                        if (accion === 'editar') {
                            // Si es edición, redirigir a la lista
                            window.location.href = 'SedeLista.php';
                        } else {
                            // Si es registro, limpiar formulario y resetear
                            $("#formRegistrarSede")[0].reset();
                            inicializarValidacion();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error de " + (accion === 'editar' ? 'Actualización' : 'Registro'),
                        text: response.message,
                        confirmButtonColor: "#ef4444"
                    });
                }
            },

            error: function () {
                Swal.close();
                // No es necesario inicializarValidacion en error de conexión
                // inicializarValidacion(); 

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error de conexión con el servidor. Revise la ruta del controlador.",
                    confirmButtonColor: "#ef4444"
                });
            },

            complete: function () {
                btn.html(originalText);
                btn.prop("disabled", false);
            }
        });
    });
});