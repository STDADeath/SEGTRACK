// ========================================
// FUNCIONARIOS - SEGTRACK
// ========================================

const FuncionariosApp = {
    config: {
        urlControlador:'../../Controller/ControladorFuncionarios.php',
    }
};

$(document).ready(function () {

    console.log("✅ Funcionarios.js cargado");
    console.log("📍 URL del controlador:", FuncionariosApp.config.urlControlador);

    $('.form-control, .form-select').addClass('no-interactuado');

    function aplicarEstiloValidacion(element, isValid) {
        const input = $(element);
        if (input.hasClass('no-interactuado')) return;

        input.removeClass('is-valid is-invalid border-primary');
        input.addClass(isValid ? 'is-valid' : 'is-invalid');
    }

    function handleInteraction(element) {
        $(element).removeClass('no-interactuado');
        $(element).trigger('validate');
    }

    $(".form-control").on('input', function () {
        $(this).hasClass('no-interactuado')
            ? handleInteraction(this)
            : $(this).trigger('validate');
    });

    $(".form-select").on('change', function () {
        $(this).hasClass('no-interactuado')
            ? handleInteraction(this)
            : $(this).trigger('validate');
    });

    // ================= VALIDACIONES =================

    $("#NombreFuncionario").on('validate', function () {
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/;
        aplicarEstiloValidacion(this, regex.test($(this).val().trim()));
    });

    $("#TelefonoFuncionario").on('validate', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        aplicarEstiloValidacion(this, this.value.length === 10);
    });

    $("#DocumentoFuncionario").on('validate', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
        aplicarEstiloValidacion(this, this.value.length >= 8);
    });

    $("#CorreoFuncionario").on('validate', function () {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        aplicarEstiloValidacion(this, regex.test($(this).val().trim()));
    });

    $("#CargoFuncionario, #IdSede").on('validate', function () {
        aplicarEstiloValidacion(this, $(this).val() !== '');
    });

    // ================= SUBMIT =================

    $("#formRegistrarFuncionario").on("submit", function (e) {
        e.preventDefault();

        console.log("📝 Formulario enviado");

        const campos = [
            '#NombreFuncionario',
            '#TelefonoFuncionario',
            '#DocumentoFuncionario',
            '#CorreoFuncionario',
            '#CargoFuncionario',
            '#IdSede'
        ];

        let hayError = false;

        campos.forEach(id => {
            const input = $(id);
            input.removeClass('no-interactuado').trigger('validate');
            if (input.hasClass('is-invalid')) hayError = true;
        });

        if (hayError) {
            Swal.fire({
                icon: 'error',
                title: 'Validación Pendiente',
                text: 'Corrige los campos en rojo'
            });
            return;
        }

        const idFuncionario = $("#IdFuncionario").val() || 0;
        const accion = (idFuncionario > 0) ? "actualizar" : "registrar";

        console.log("🚀 Acción:", accion);
        ejecutarAjaxFormulario($(this), accion);
    });

    // ================= AJAX =================

    function ejecutarAjaxFormulario(form, accion) {
        const btn = $("#btnRegistrar");
        const textoOriginal = btn.html();

        btn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...').prop('disabled', true);

        const datosForm = form.serialize() + "&accion=" + accion;
        console.log("📤 Datos enviados:", datosForm);
        console.log("🌐 URL:", FuncionariosApp.config.urlControlador);

        $.ajax({
            url: FuncionariosApp.config.urlControlador,
            type: "POST",
            data: datosForm,
            dataType: "json",

            success: function (response) {
                console.log("✅ Respuesta recibida:", response);

                if (typeof response === "string") {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error("❌ JSON inválido:", response);
                        Swal.fire("Error", "Respuesta inválida del servidor", "error");
                        return;
                    }
                }

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Operación exitosa',
                        text: response.message || "Funcionario guardado correctamente"
                    }).then(() => {
                        $("#formRegistrarFuncionario")[0].reset();
                        $('.form-control, .form-select')
                            .removeClass('is-valid is-invalid')
                            .addClass('no-interactuado');
                        $("#IdFuncionario").val(0);
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || "Error desconocido"
                    });
                }
            },

            error: function (xhr, status, error) {
                console.error("❌ Error AJAX:");
                console.error("- Status:", xhr.status);
                console.error("- Error:", error);
                console.error("- Respuesta:", xhr.responseText);

                let mensajeError = "No se pudo conectar con el servidor";
                
                if (xhr.status === 404) {
                    mensajeError = "El controlador no fue encontrado. Verifica que el archivo exista en:<br><code>" + 
                                  FuncionariosApp.config.urlControlador + "</code>";
                } else if (xhr.status === 500) {
                    mensajeError = "Error interno del servidor. Revisa los logs de PHP.";
                } else if (xhr.responseText) {
                    mensajeError = "Error del servidor:<br><pre>" + xhr.responseText.substring(0, 500) + "</pre>";
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    html: mensajeError,
                    width: '600px'
                });
            },

            complete: function () {
                btn.html(textoOriginal).prop('disabled', false);
            }
        });
    }

    // ================= CAMBIAR ESTADO =================

    window.cambiarEstadoFuncionario = function (id, estado) {
        Swal.fire({
            title: "¿Cambiar estado?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, cambiar"
        }).then(result => {
            if (!result.isConfirmed) return;

            $.post(FuncionariosApp.config.urlControlador, {
                accion: "cambiar_estado",
                id: id,
                estado: estado
            }, function (response) {
                if (response.success) {
                    Swal.fire("Estado actualizado", "", "success")
                        .then(() => location.reload());
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            }, "json").fail(function() {
                Swal.fire("Error", "No se pudo cambiar el estado", "error");
            });
        });
    };

    // ================= REGENERAR QR =================

    window.regenerarQRFuncionario = function (id) {
        Swal.fire({
            title: "¿Regenerar QR?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí"
        }).then(result => {
            if (!result.isConfirmed) return;

            $.post(FuncionariosApp.config.urlControlador, {
                accion: "actualizar_qr",
                id: id
            }, function (response) {
                if (response.success) {
                    Swal.fire("QR actualizado", "", "success")
                        .then(() => location.reload());
                } else {
                    Swal.fire("Error", response.message || "No se pudo generar QR", "error");
                }
            }, "json").fail(function() {
                Swal.fire("Error", "No se pudo regenerar el QR", "error");
            });
        });
    };

}); // ← ESTA ES LA CORRECCIÓN: Cerrar el document.ready 