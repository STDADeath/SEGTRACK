// Public/js/javascript/js/ValidacionesSede.js

$(document).ready(function () {

    console.log("=== SISTEMA DE REGISTRO DE SEDE INICIADO ===");

    // ============================
    // FUNCIONES VISUALES (CON !important PARA QUE BOOTSTRAP NO DAÑE ESTILOS)
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

    function inicializarValidacion() {
        marcarNeutral($("#TipoSede"));
        marcarNeutral($("#Ciudad"));
        marcarNeutral($("#IdInstitucion"));
    }

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
    // ENVÍO AJAX
    // ============================

    $("#formRegistrarSede").submit(function (e) {
        e.preventDefault();

        let errores = [];

        const tipo = $("#TipoSede");
        const ciudad = $("#Ciudad");
        const institucion = $("#IdInstitucion");

        // Validaciones finales
        if (tipo.val().length < 3 || !soloTexto(tipo.val())) {
            errores.push("• El tipo de sede debe contener solo letras (mínimo 3 caracteres).");
            marcarInvalido(tipo);
        } else {
            marcarValido(tipo);
        }

        if (ciudad.val().length < 3 || !soloTexto(ciudad.val())) {
            errores.push("• La ciudad debe contener solo letras (mínimo 3 caracteres).");
            marcarInvalido(ciudad);
        } else {
            marcarValido(ciudad);
        }

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

        // Loading
        Swal.fire({
            title: 'Registrando sede...',
            html: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const btn = $(this).find("button[type='submit']");
        const originalText = btn.html();
        btn.prop("disabled", true);

        $.ajax({
            url: '../../Controller/ControladorSede.php',
            type: "POST",
            data: $(this).serialize() + "&accion=registrar",
            dataType: "json",

            success: function (response) {
                Swal.close();

                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Registro Exitoso!",
                        text: response.message,
                        confirmButtonColor: "#10b981"
                    }).then(() => {
                        $("#formRegistrarSede")[0].reset();
                        inicializarValidacion();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error en el Registro",
                        text: response.message,
                        confirmButtonColor: "#ef4444"
                    });
                }
            },

            error: function () {
                Swal.close();
                inicializarValidacion();

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error de conexión con el servidor.",
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
