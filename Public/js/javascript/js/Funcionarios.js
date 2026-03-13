/**
 * Lógica JavaScript para el formulario de Funcionarios (Registro y Actualización)
 *
 * Incluye:
 * 1. Validaciones en tiempo real.
 * 2. Filtros de números.
 * 3. Registro / actualización.
 * 4. Sistema de cámara para foto.
 */

$(document).ready(function () {

    // ====================================================================
    // VARIABLES PARA CAMARA
    // ====================================================================

    let stream = null;

    // ====================================================================
    // ABRIR CAMARA
    // ====================================================================

    $("#btnAbrirCamara").click(async function () {

        const areaCamera = $("#areaCamera");

        try {

            stream = await navigator.mediaDevices.getUserMedia({
                video: true
            });

            const video = document.getElementById("videoCamera");
            video.srcObject = stream;

            areaCamera.removeClass("d-none");

        } catch (error) {

            Swal.fire({
                icon: "error",
                title: "Cámara no disponible",
                text: "No se pudo acceder a la cámara del dispositivo."
            });

        }

    });


    // ====================================================================
    // CAPTURAR FOTO
    // ====================================================================

    $("#btnCapturar").click(function () {

        const video = document.getElementById("videoCamera");
        const canvas = document.getElementById("canvasCaptura");

        const ctx = canvas.getContext("2d");

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const imagen = canvas.toDataURL("image/png");

        // preview
        $("#previewFoto").attr("src", imagen).show();
        $("#previewPlaceholder").hide();

        // guardar base64
        $("#FotoCapturaBase64").val(imagen);

        // detener camara
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }

        // ocultar camara
        $("#areaCamera").addClass("d-none");

    });


    // ====================================================================
    // CANCELAR CAMARA
    // ====================================================================

    $("#btnCerrarCamara").click(function () {

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }

        $("#areaCamera").addClass("d-none");

    });


    // ====================================================================
    // PREVIEW FOTO SUBIDA
    // ====================================================================

    $("#FotoFuncionario").change(function (e) {

        const archivo = e.target.files[0];

        if (!archivo) return;

        const reader = new FileReader();

        reader.onload = function (event) {

            $("#previewFoto").attr("src", event.target.result).show();
            $("#previewPlaceholder").hide();

        };

        reader.readAsDataURL(archivo);

    });


    // ====================================================================
    // 1. LÓGICA DE INTERACCIÓN (VALIDACIONES)
    // ====================================================================

    $('.form-control, .form-select').addClass('no-interactuado');

    function aplicarEstiloValidacion(elementId, isValid) {

        const input = $(elementId);

        if (input.hasClass('no-interactuado')) {
            return;
        }

        input.removeClass('is-valid is-invalid border-primary');

        if (isValid) {
            input.addClass('is-valid');
        } else {
            input.addClass('is-invalid');
        }

    }

    function handleInteraction(element) {

        $(element).removeClass('no-interactuado');
        $(element).trigger('validate');

    }

    $(".form-control").on('input', function () {

        if ($(this).hasClass('no-interactuado')) {
            handleInteraction(this);
        } else {
            $(this).trigger('validate');
        }

    });

    $(".form-select").on('change', function () {

        if ($(this).hasClass('no-interactuado')) {
            handleInteraction(this);
        } else {
            $(this).trigger('validate');
        }

    });


    // ====================================================================
    // VALIDACIONES
    // ====================================================================

    $("#NombreFuncionario").on('validate', function () {

        const regexNombre = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/;

        const nombre = $(this).val().trim();

        const isValid = nombre !== '' && regexNombre.test(nombre);

        aplicarEstiloValidacion(this, isValid);

    });


    $("#TelefonoFuncionario").on('validate', function () {

        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);

        const telefono = $(this).val();

        const isValid = telefono.length === 10;

        aplicarEstiloValidacion(this, isValid);

    });


    $("#DocumentoFuncionario").on('validate', function () {

        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);

        const documento = $(this).val();

        const isValid = documento.length >= 8 && documento.length <= 10;

        aplicarEstiloValidacion(this, isValid);

    });


    $("#CorreoFuncionario").on('validate', function () {

        const correo = $(this).val().trim();

        const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        const isValid = correo !== '' && regexCorreo.test(correo);

        aplicarEstiloValidacion(this, isValid);

    });


    $("#CargoFuncionario, #IdSede").on('validate', function () {

        const isValid = $(this).val() !== '' && $(this).val() !== null && $(this).val() !== '0';

        aplicarEstiloValidacion(this, isValid);

    });


    // ====================================================================
    // ENVÍO FORMULARIO
    // ====================================================================

    $("#formRegistrarFuncionario").on("submit", function (e) {

        e.preventDefault();

        const inputsAValidar = [
            '#NombreFuncionario',
            '#TelefonoFuncionario',
            '#DocumentoFuncionario',
            '#CorreoFuncionario',
            '#CargoFuncionario',
            '#IdSede'
        ];

        let hayInvalidos = false;

        inputsAValidar.forEach(id => {

            const input = $(id);

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
                text: 'Por favor corrija los campos en rojo.'
            });

            return false;

        }

        const idFuncionario = $("#IdFuncionario").val();

        const accion = (idFuncionario && parseInt(idFuncionario) > 0)
            ? "actualizar"
            : "registrar";

        const btn = $("#btnRegistrar");

        const originalText = btn.html();

        btn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        btn.prop('disabled', true);

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
                        title: 'Registro Exitoso',
                        text: response.message
                    }).then(() => {

                        $("#formRegistrarFuncionario")[0].reset();

                        $('.form-control, .form-select')
                            .removeClass('is-valid is-invalid')
                            .addClass('no-interactuado');

                        $("#IdFuncionario").val(0);

                        $("#previewFoto").hide();
                        $("#previewPlaceholder").show();

                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });

                }

            },

            error: function () {

                Swal.fire({
                    icon: 'error',
                    title: 'Error de servidor'
                });

            },

            complete: function () {

                btn.html(originalText);

                btn.prop('disabled', false);

            }

        });

    });

});