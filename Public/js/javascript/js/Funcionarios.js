/**
 * Lógica JavaScript para el formulario de Funcionarios (Registro y Actualización)
 *
 * Incluye:
 * 1. Validaciones en tiempo real verde/rojo.
 * 2. Filtros de números.
 * 3. Registro / actualización con FormData manual (soporta archivos y base64).
 * 4. Sistema de cámara para foto.
 * 5. Modal de edición con validaciones verde/rojo.
 * 6. Ver QR, Enviar QR por correo.
 */

$(document).ready(function () {

    // ====================================================================
    // VARIABLES GLOBALES
    // ====================================================================

    let stream        = null;
    let fotoCapturada = null;


    // ====================================================================
    // DATATABLES (si existe la tabla en esta página)
    // ====================================================================

    if ($.fn.DataTable && $('#tablaFuncionarios').length) {
        $('#tablaFuncionarios').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
            },
            pageLength: 10,
            order: [[0, 'desc']]
        });
    }


    // ====================================================================
    // ABRIR CÁMARA
    // ====================================================================

    $("#btnAbrirCamara").click(async function () {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            document.getElementById("videoCamera").srcObject = stream;
            $("#areaCamera").removeClass("d-none");
        } catch (error) {
            Swal.fire({
                icon : "error",
                title: "Cámara no disponible",
                text : "No se pudo acceder a la cámara del dispositivo."
            });
        }
    });


    // ====================================================================
    // CAPTURAR FOTO DESDE CÁMARA
    // ====================================================================

    $("#btnCapturar").click(function () {
        const video  = document.getElementById("videoCamera");
        const canvas = document.getElementById("canvasCaptura");
        const ctx    = canvas.getContext("2d");

        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        fotoCapturada = canvas.toDataURL("image/jpeg", 0.9);
        $("#FotoCapturaBase64").val(fotoCapturada);
        $("#FotoFuncionario").val("");

        $("#previewFoto").attr("src", fotoCapturada).show();
        $("#previewPlaceholder").hide();

        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
        }
        $("#areaCamera").addClass("d-none");
    });


    // ====================================================================
    // CANCELAR CÁMARA
    // ====================================================================

    $("#btnCerrarCamara").click(function () {
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
        }
        $("#areaCamera").addClass("d-none");
    });


    // ====================================================================
    // PREVIEW FOTO DESDE ARCHIVO
    // ====================================================================

    $("#FotoFuncionario").on("change", function (e) {
        const archivo = e.target.files[0];
        if (!archivo) return;

        fotoCapturada = null;
        $("#FotoCapturaBase64").val("");

        const reader = new FileReader();
        reader.onload = function (event) {
            $("#previewFoto").attr("src", event.target.result).show();
            $("#previewPlaceholder").hide();
        };
        reader.readAsDataURL(archivo);
    });


    // ====================================================================
    // HELPERS DE VALIDACIÓN VERDE / ROJO
    // ====================================================================

    function marcarValido(id) {
        $(id)
            .removeClass('is-invalid border-primary no-interactuado')
            .addClass('is-valid');
    }

    function marcarInvalido(id) {
        $(id)
            .removeClass('is-valid border-primary no-interactuado')
            .addClass('is-invalid');
    }

    function limpiarValidacion(id) {
        $(id).removeClass('is-valid is-invalid no-interactuado').addClass('border-primary');
    }

    // Valida un campo y devuelve true/false
    function validarCampo(id, reglaFn) {
        const val = $(id).val();
        const ok  = reglaFn(val);
        ok ? marcarValido(id) : marcarInvalido(id);
        return ok;
    }


    // ====================================================================
    // VALIDACIONES EN TIEMPO REAL — FORMULARIO REGISTRO
    // ====================================================================

    $('.form-control, .form-select').addClass('no-interactuado');

    function aplicarEstiloValidacion(elemento, isValid) {
        const input = $(elemento);
        if (input.hasClass('no-interactuado')) return;
        input.removeClass('is-valid is-invalid border-primary');
        input.addClass(isValid ? 'is-valid' : 'is-invalid');
    }

    function handleInteraction(elemento) {
        $(elemento).removeClass('no-interactuado');
        $(elemento).trigger('validate');
    }

    $(".form-control").on('input', function () {
        if ($(this).hasClass('no-interactuado')) handleInteraction(this);
        else $(this).trigger('validate');
    });

    $("select.form-control, .form-select").on('change', function () {
        if ($(this).hasClass('no-interactuado')) handleInteraction(this);
        else $(this).trigger('validate');
    });

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
        const len  = this.value.length;
        aplicarEstiloValidacion(this, len >= 8 && len <= 11);
    });

    $("#CorreoFuncionario").on('validate', function () {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        aplicarEstiloValidacion(this, regex.test($(this).val().trim()));
    });

    $("#CargoFuncionario, #IdSede").on('validate', function () {
        const val = $(this).val();
        aplicarEstiloValidacion(this, val !== '' && val !== null && val !== '0');
    });


    // ====================================================================
    // VALIDACIONES EN TIEMPO REAL — MODAL EDITAR
    // ====================================================================

    $("#editNombre").on('input', function () {
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/;
        const ok    = regex.test($(this).val().trim());
        $(this).removeClass('is-valid is-invalid').addClass(ok ? 'is-valid' : 'is-invalid');
    });

    $("#editTelefono").on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        const ok   = this.value.length === 10;
        $(this).removeClass('is-valid is-invalid').addClass(ok ? 'is-valid' : 'is-invalid');
    });

    $("#editCargo").on('change', function () {
        const ok = $(this).val() !== '' && $(this).val() !== null;
        $(this).removeClass('is-valid is-invalid').addClass(ok ? 'is-valid' : 'is-invalid');
    });

    $("#editSede").on('change', function () {
        const ok = $(this).val() !== '' && $(this).val() !== null;
        $(this).removeClass('is-valid is-invalid').addClass(ok ? 'is-valid' : 'is-invalid');
    });

    // Limpiar validaciones al abrir el modal
    $('#modalEditar').on('show.bs.modal', function () {
        $('#editNombre, #editTelefono, #editCargo, #editSede')
            .removeClass('is-valid is-invalid');
    });


    // ====================================================================
    // ENVÍO FORMULARIO REGISTRO
    // ====================================================================

    $("#formRegistrarFuncionario").on("submit", function (e) {
        e.preventDefault();

        const camposRequeridos = [
            '#NombreFuncionario',
            '#TelefonoFuncionario',
            '#DocumentoFuncionario',
            '#CorreoFuncionario',
            '#CargoFuncionario',
            '#IdSede'
        ];

        let hayErrores = false;

        camposRequeridos.forEach(function (id) {
            const input = $(id);
            input.removeClass('no-interactuado');
            input.trigger('validate');
            if (input.hasClass('is-invalid')) hayErrores = true;
        });

        if (hayErrores) {
            Swal.fire({
                icon : 'error',
                title: 'Campos incompletos',
                text : 'Por favor corrija los campos marcados en rojo.'
            });
            return false;
        }

        const idFuncionario = $("#IdFuncionario").val();
        const accion = (idFuncionario && parseInt(idFuncionario) > 0)
            ? "actualizar" : "registrar";

        const formData = new FormData();
        formData.append("accion",               accion);
        formData.append("CargoFuncionario",     $("#CargoFuncionario").val());
        formData.append("NombreFuncionario",    $("#NombreFuncionario").val().trim());
        formData.append("IdSede",               $("#IdSede").val());
        formData.append("TelefonoFuncionario",  $("#TelefonoFuncionario").val().trim());
        formData.append("DocumentoFuncionario", $("#DocumentoFuncionario").val().trim());
        formData.append("CorreoFuncionario",    $("#CorreoFuncionario").val().trim());

        if (idFuncionario) formData.append("IdFuncionario", idFuncionario);

        const inputArchivo = document.getElementById("FotoFuncionario");
        const base64Camara = $("#FotoCapturaBase64").val();

        if (inputArchivo && inputArchivo.files && inputArchivo.files.length > 0) {
            formData.append("FotoFuncionario", inputArchivo.files[0], inputArchivo.files[0].name);
        } else if (base64Camara && base64Camara.trim() !== "") {
            formData.append("FotoCapturaBase64", base64Camara);
        }

        const btn           = $("#btnRegistrar");
        const textoOriginal = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        btn.prop('disabled', true);

        $.ajax({
            url        : "../../Controller/ControladorFuncionarios.php",
            type       : "POST",
            data       : formData,
            dataType   : "json",
            contentType: false,
            processData: false,

            success: function (response) {
                if (response.success) {

                    // ✅ Marcar todos los campos en verde al éxito
                    camposRequeridos.forEach(function (id) {
                        marcarValido(id);
                    });

                    Swal.fire({
                        icon             : 'success',
                        title            : accion === 'registrar' ? '¡Registrado!' : '¡Actualizado!',
                        text             : response.message,
                        confirmButtonText: 'Aceptar'
                    }).then(function () {
                        $("#formRegistrarFuncionario")[0].reset();

                        // Limpiar estilos
                        $('.form-control, .form-select')
                            .removeClass('is-valid is-invalid')
                            .addClass('no-interactuado');

                        fotoCapturada = null;
                        $("#FotoCapturaBase64").val("");
                        $("#IdFuncionario").val("");
                        $("#previewFoto").hide().attr("src", "");
                        $("#previewPlaceholder").show();
                    });

                } else {

                    // ✅ Marcar en rojo los campos con error del servidor
                    Swal.fire({
                        icon : 'error',
                        title: 'Error',
                        text : response.message || 'Ocurrió un error inesperado.'
                    });

                    // Si el error es de duplicado marcar correo y documento en rojo
                    if (response.message && response.message.toLowerCase().includes('duplicad')) {
                        marcarInvalido('#DocumentoFuncionario');
                        marcarInvalido('#CorreoFuncionario');
                    }
                }
            },

            error: function (xhr) {
                console.error("❌ Error AJAX:", xhr.responseText);

                // ✅ Marcar todos en rojo cuando hay error de servidor
                camposRequeridos.forEach(function (id) {
                    marcarInvalido(id);
                });

                Swal.fire({
                    icon : 'error',
                    title: 'Error de servidor',
                    text : 'No se pudo conectar con el servidor. Intente nuevamente.'
                });
            },

            complete: function () {
                btn.html(textoOriginal);
                btn.prop('disabled', false);
            }
        });
    });


    // ====================================================================
    // VER QR
    // ====================================================================

    window.verQR = function (rutaQR, idFuncionario) {
        const urlCompleta = window.location.origin + '/SEGTRACK/Public' + rutaQR;
        $('#qrImagen').attr('src', urlCompleta);
        $('#btnDescargarQR').attr('href', urlCompleta);
        $('#qrFuncionarioId').text(idFuncionario);
        const modal = new bootstrap.Modal(document.getElementById('modalVerQR'));
        modal.show();
    };


    // ====================================================================
    // ENVIAR QR POR CORREO
    // ====================================================================

    window.enviarQR = function (idFuncionario) {
        Swal.fire({
            title            : '¿Enviar QR?',
            text             : `Se enviará el código QR al correo del funcionario #${idFuncionario}.`,
            icon             : 'question',
            showCancelButton : true,
            confirmButtonText: 'Sí, enviar',
            cancelButtonText : 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url     : "../../Controller/ControladorFuncionarios.php",
                type    : "POST",
                data    : { accion: 'enviar_qr', IdFuncionario: idFuncionario },
                dataType: "json",

                success: function (response) {
                    Swal.fire({
                        icon : response.success ? 'success' : 'error',
                        title: response.success ? '¡Enviado!' : 'Error',
                        text : response.message
                    });
                },
                error: function () {
                    Swal.fire({
                        icon : 'error',
                        title: 'Error de servidor',
                        text : 'No se pudo enviar el QR. Intente nuevamente.'
                    });
                }
            });
        });
    };


    // ====================================================================
    // CARGAR DATOS EN MODAL EDITAR
    // ====================================================================

    window.cargarDatosEdicion = function (id, cargo, nombre, sede, telefono, documento, correo) {
        $('#editId').val(id);
        $('#editCargo').val(cargo);
        $('#editNombre').val(nombre);
        $('#editSede').val(sede);
        $('#editTelefono').val(telefono);
        $('#editDocumento').val(documento);
        $('#editCorreo').val(correo);

        const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
        modal.show();
    };


    // ====================================================================
    // GUARDAR CAMBIOS — MODAL EDITAR
    // ====================================================================

    $('#btnGuardarCambios').on('click', function () {

        // ── Validar campos del modal ───────────────────────
        let hayErrores = false;

        const regexNombre = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/;
        const nombre      = $('#editNombre').val().trim();
        const telefono    = $('#editTelefono').val().trim();
        const cargo       = $('#editCargo').val();
        const sede        = $('#editSede').val();

        // Nombre
        if (!regexNombre.test(nombre)) {
            $('#editNombre').removeClass('is-valid').addClass('is-invalid');
            hayErrores = true;
        } else {
            $('#editNombre').removeClass('is-invalid').addClass('is-valid');
        }

        // Teléfono
        if (telefono.length !== 10 || !/^\d+$/.test(telefono)) {
            $('#editTelefono').removeClass('is-valid').addClass('is-invalid');
            hayErrores = true;
        } else {
            $('#editTelefono').removeClass('is-invalid').addClass('is-valid');
        }

        // Cargo
        if (!cargo || cargo === '') {
            $('#editCargo').removeClass('is-valid').addClass('is-invalid');
            hayErrores = true;
        } else {
            $('#editCargo').removeClass('is-invalid').addClass('is-valid');
        }

        // Sede
        if (!sede || sede === '') {
            $('#editSede').removeClass('is-valid').addClass('is-invalid');
            hayErrores = true;
        } else {
            $('#editSede').removeClass('is-invalid').addClass('is-valid');
        }

        if (hayErrores) {
            Swal.fire({
                icon : 'error',
                title: 'Campos incompletos',
                text : 'Por favor corrija los campos marcados en rojo.'
            });
            return;
        }

        // ── Enviar AJAX ────────────────────────────────────
        const btn           = $('#btnGuardarCambios');
        const textoOriginal = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);

        $.ajax({
            url     : "../../Controller/ControladorFuncionarios.php",
            type    : "POST",
            data    : {
                accion              : 'actualizar',
                IdFuncionario       : $('#editId').val(),
                CargoFuncionario    : cargo,
                NombreFuncionario   : nombre,
                IdSede              : sede,
                TelefonoFuncionario : telefono,
                DocumentoFuncionario: $('#editDocumento').val(),
                CorreoFuncionario   : $('#editCorreo').val()
            },
            dataType: "json",

            success: function (response) {
                if (response.success) {

                    // ✅ Todos los campos en verde al éxito
                    $('#editNombre, #editTelefono, #editCargo, #editSede')
                        .removeClass('is-invalid').addClass('is-valid');

                    Swal.fire({
                        icon             : 'success',
                        title            : '¡Actualizado!',
                        text             : response.message,
                        confirmButtonText: 'Aceptar'
                    }).then(function () {
                        bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
                        location.reload();
                    });

                } else {

                    // ✅ Campos en rojo al fallar
                    $('#editNombre, #editTelefono, #editCargo, #editSede')
                        .removeClass('is-valid').addClass('is-invalid');

                    Swal.fire({
                        icon : 'error',
                        title: 'Error',
                        text : response.message || 'No se pudo actualizar el funcionario.'
                    });
                }
            },

            error: function (xhr) {
                console.error("❌ Error AJAX editar:", xhr.responseText);

                // ✅ Todos en rojo al error de servidor
                $('#editNombre, #editTelefono, #editCargo, #editSede')
                    .removeClass('is-valid').addClass('is-invalid');

                Swal.fire({
                    icon : 'error',
                    title: 'Error de servidor',
                    text : 'No se pudo conectar. Intente nuevamente.'
                });
            },

            complete: function () {
                btn.html(textoOriginal).prop('disabled', false);
            }
        });
    });

});