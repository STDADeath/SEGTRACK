/**
 * Lógica JavaScript para el formulario de Funcionarios (Registro y Actualización)
 *
 * Incluye:
 * 1. Validaciones en tiempo real.
 * 2. Filtros de números.
 * 3. Registro / actualización con FormData manual (soporta archivos y base64).
 * 4. Sistema de cámara para foto.
 */

$(document).ready(function () {

    // ====================================================================
    // VARIABLES GLOBALES
    // ====================================================================

    let stream        = null;
    let fotoCapturada = null; // base64 de la cámara


    // ====================================================================
    // ABRIR CAMARA
    // ====================================================================

    $("#btnAbrirCamara").click(async function () {

        try {

            stream = await navigator.mediaDevices.getUserMedia({ video: true });

            const video     = document.getElementById("videoCamera");
            video.srcObject = stream;

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
    // CAPTURAR FOTO DESDE CAMARA
    // ====================================================================

    $("#btnCapturar").click(function () {

        const video  = document.getElementById("videoCamera");
        const canvas = document.getElementById("canvasCaptura");
        const ctx    = canvas.getContext("2d");

        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;

        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Guardar base64 en variable global y en campo hidden
        fotoCapturada = canvas.toDataURL("image/jpeg", 0.9);
        $("#FotoCapturaBase64").val(fotoCapturada);

        // Limpiar input file para evitar conflictos
        $("#FotoFuncionario").val("");

        // Mostrar preview
        $("#previewFoto").attr("src", fotoCapturada).show();
        $("#previewPlaceholder").hide();

        // Detener cámara
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        $("#areaCamera").addClass("d-none");

    });


    // ====================================================================
    // CANCELAR CAMARA
    // ====================================================================

    $("#btnCerrarCamara").click(function () {

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        $("#areaCamera").addClass("d-none");

    });


    // ====================================================================
    // PREVIEW FOTO SUBIDA DESDE ARCHIVO
    // ====================================================================

    $("#FotoFuncionario").on("change", function (e) {

        const archivo = e.target.files[0];

        if (!archivo) return;

        // Limpiar base64 de cámara para evitar conflictos
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
    // VALIDACIONES EN TIEMPO REAL
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
        if ($(this).hasClass('no-interactuado')) {
            handleInteraction(this);
        } else {
            $(this).trigger('validate');
        }
    });

    $("select.form-control, .form-select").on('change', function () {
        if ($(this).hasClass('no-interactuado')) {
            handleInteraction(this);
        } else {
            $(this).trigger('validate');
        }
    });

    // ── Nombre ───────────────────────────────────────────
    $("#NombreFuncionario").on('validate', function () {
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/;
        const valor = $(this).val().trim();
        aplicarEstiloValidacion(this, valor !== '' && regex.test(valor));
    });

    // ── Teléfono ─────────────────────────────────────────
    $("#TelefonoFuncionario").on('validate', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        aplicarEstiloValidacion(this, this.value.length === 10);
    });

    // ── Documento ────────────────────────────────────────
    $("#DocumentoFuncionario").on('validate', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
        const len  = this.value.length;
        aplicarEstiloValidacion(this, len >= 8 && len <= 11);
    });

    // ── Correo ───────────────────────────────────────────
    $("#CorreoFuncionario").on('validate', function () {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const valor = $(this).val().trim();
        aplicarEstiloValidacion(this, valor !== '' && regex.test(valor));
    });

    // ── Selects obligatorios ─────────────────────────────
    $("#CargoFuncionario, #IdSede").on('validate', function () {
        const val = $(this).val();
        aplicarEstiloValidacion(this, val !== '' && val !== null && val !== '0');
    });


    // ====================================================================
    // ENVÍO DEL FORMULARIO
    // ====================================================================

    $("#formRegistrarFuncionario").on("submit", function (e) {

        e.preventDefault();

        // ── 1. Validar todos los campos ───────────────────
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
            if (input.hasClass('is-invalid')) {
                hayErrores = true;
            }
        });

        if (hayErrores) {
            Swal.fire({
                icon : 'error',
                title: 'Campos incompletos',
                text : 'Por favor corrija los campos marcados en rojo.'
            });
            return false;
        }

        // ── 2. Determinar acción ──────────────────────────
        const idFuncionario = $("#IdFuncionario").val();
        const accion = (idFuncionario && parseInt(idFuncionario) > 0)
            ? "actualizar"
            : "registrar";

        // ── 3. Construir FormData MANUALMENTE ─────────────
        // Se agregan los campos uno a uno para garantizar
        // que el archivo binario se incluya correctamente
        const formData = new FormData();

        formData.append("accion",               accion);
        formData.append("CargoFuncionario",     $("#CargoFuncionario").val());
        formData.append("NombreFuncionario",    $("#NombreFuncionario").val().trim());
        formData.append("IdSede",               $("#IdSede").val());
        formData.append("TelefonoFuncionario",  $("#TelefonoFuncionario").val().trim());
        formData.append("DocumentoFuncionario", $("#DocumentoFuncionario").val().trim());
        formData.append("CorreoFuncionario",    $("#CorreoFuncionario").val().trim());

        if (idFuncionario) {
            formData.append("IdFuncionario", idFuncionario);
        }

        // ── 4. Agregar foto según el método usado ─────────
        const inputArchivo = document.getElementById("FotoFuncionario");
        const base64Camara = $("#FotoCapturaBase64").val();

        if (inputArchivo.files && inputArchivo.files.length > 0) {

            // ✅ Foto subida desde archivo — se agrega el objeto File directamente
            formData.append("FotoFuncionario", inputArchivo.files[0], inputArchivo.files[0].name);
            console.log("✅ Foto desde archivo:", inputArchivo.files[0].name,
                        "| Tamaño:", inputArchivo.files[0].size, "bytes");

        } else if (base64Camara && base64Camara.trim() !== "") {

            // ✅ Foto capturada desde cámara — se envía como base64
            formData.append("FotoCapturaBase64", base64Camara);
            console.log("✅ Foto desde cámara (base64), longitud:", base64Camara.length);

        } else {
            console.warn("⚠️ No se adjuntó ninguna foto");
        }

        // ── 5. Bloquear botón ─────────────────────────────
        const btn           = $("#btnRegistrar");
        const textoOriginal = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        btn.prop('disabled', true);

        // ── 6. Enviar AJAX ────────────────────────────────
        $.ajax({
            url        : "../../Controller/ControladorFuncionarios.php",
            type       : "POST",
            data       : formData,
            dataType   : "json",
            contentType: false,  // ✅ Obligatorio con FormData
            processData: false,  // ✅ Obligatorio con FormData

            success: function (response) {

                if (response.success) {

                    // Log para verificar que la foto se guardó
                    if (response.data && response.data.FotoFuncionario) {
                        console.log("📸 Foto guardada en BD:", response.data.FotoFuncionario);
                    } else {
                        console.warn("⚠️ Funcionario registrado pero sin foto en BD");
                    }

                    Swal.fire({
                        icon             : 'success',
                        title            : accion === 'registrar' ? '¡Registrado!' : '¡Actualizado!',
                        text             : response.message,
                        confirmButtonText: 'Aceptar'
                    }).then(function () {

                        // Limpiar formulario
                        $("#formRegistrarFuncionario")[0].reset();

                        // Limpiar estilos de validación
                        $('.form-control, .form-select')
                            .removeClass('is-valid is-invalid')
                            .addClass('no-interactuado');

                        // Limpiar variables de foto
                        fotoCapturada = null;
                        $("#FotoCapturaBase64").val("");
                        $("#IdFuncionario").val("");

                        // Resetear preview de foto
                        $("#previewFoto").hide().attr("src", "");
                        $("#previewPlaceholder").show();

                    });

                } else {

                    Swal.fire({
                        icon : 'error',
                        title: 'Error',
                        text : response.message || 'Ocurrió un error inesperado.'
                    });

                }

            },

            error: function (xhr) {

                console.error("❌ Error AJAX — Status:", xhr.status);
                console.error("❌ Respuesta:", xhr.responseText);

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

});