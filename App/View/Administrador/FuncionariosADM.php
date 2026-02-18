<?php
// Inicia la sesión para mantener variables de usuario
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /SEGTRACK1/");
    exit();
}

// Bloquear cache para que no puedan volver con flecha
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Importa la parte superior del layout (navbar, estilos, encabezado general)
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';

// -------------------------------------------------------------
// CARGA DE LAS SEDES PARA LLENAR EL SELECT DEL FORMULARIO
// -------------------------------------------------------------

require_once __DIR__ . "/../../Controller/ControladorSede.php";

// Crea instancia del controlador de sedes
$controladorSede = new ControladorSede();

// Obtiene las sedes desde la base de datos
$sedes = $controladorSede->obtenerSedes();
?>

<!-- ================================================================
     CSS PERSONALIZADO: Elimina los iconos de validación de Bootstrap
================================================================= -->
<style>
    /* Oculta los íconos verdes (✔) y rojos (✘) de los inputs de Bootstrap */
    .form-control.is-valid,
    .form-control.is-invalid,
    .form-select.is-valid,
    .form-select.is-invalid {
        background-image: none !important; /* elimina el ícono */
        padding-right: 0.75rem !important; /* asegura que el padding sea uniforme */
    }

    /* Evita que el borde azul de Bootstrap aparezca al hacer clic en el input */
    .form-control:focus,
    .form-select:focus {
        box-shadow: none;
    }
</style>

<!-- ================================================================
     CONTENEDOR PRINCIPAL
================================================================= -->
<div class="container-fluid px-4 py-4">

    <!-- Título superior y botón para ver lista de funcionarios -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-tie me-2"></i>Registrar Funcionario
        </h1>

        <a href="./FuncionarioListaADM.php" class="btn btn-primary btn-sm">
            <i class="fas fa-list me-1"></i> Ver Funcionarios
        </a>
    </div>

    <!-- ================================================================
         TARJETA CON EL FORMULARIO DE REGISTRO
    ================================================================= -->
    <div class="card shadow mb-4">

        <!-- Encabezado de la tarjeta -->
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de registro</h6>
        </div>

        <!-- Cuerpo de la tarjeta -->
        <div class="card-body">

            <!-- FORMULARIO PRINCIPAL -->
            <form id="formRegistrarFuncionario">

                <!-- FILA 1 (Cargo - Nombre) -->
                <div class="row">

                    <!-- SELECT CARGO -->
                    <div class="col-md-6 mb-3">
                        <label for="CargoFuncionario" class="form-label">Cargo *</label>

                        <!-- Lista de cargos disponibles -->
                        <select id="CargoFuncionario" name="CargoFuncionario"
                            class="form-control border-primary shadow-sm">

                            <option value="">Seleccione...</option>
                            <option value="Personal Seguridad">Personal Seguridad</option>
                            <option value="Funcionario">Funcionario</option>
                            <option value="Visitante">Visitante</option>
                            <option value="RR.HH">RR.HH</option>
                            <option value="Contador">Contador</option>
                            <option value="Financiero">Financiero</option>
                        </select>

                        <!-- Mensaje de error si no es válido -->
                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                    </div>

                    <!-- INPUT NOMBRE -->
                    <div class="col-md-6 mb-3">
                        <label for="NombreFuncionario" class="form-label">Nombre Completo *</label>

                        <input type="text" id="NombreFuncionario" name="NombreFuncionario"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: Juan Pérez">

                        <div class="invalid-feedback">
                            El nombre solo debe contener letras y espacios (Mínimo 3 caracteres).
                        </div>
                    </div>
                </div>

                <!-- FILA 2 (Sede - Teléfono - Documento) -->
                <div class="row">

                    <!-- SELECT SEDE -->
                    <div class="col-md-4 mb-3">
                        <label for="IdSede" class="form-label">Sede *</label>

                        <select id="IdSede" name="IdSede" class="form-control border-primary shadow-sm">

                            <option value="">Seleccione...</option>

                            <!-- Si hay sedes, cargarlas una por una -->
                            <?php if (!empty($sedes)): ?>
                                <?php foreach ($sedes as $sede): ?>
                                    <option value="<?= htmlspecialchars($sede['IdSede']) ?>">
                                        <?= htmlspecialchars($sede['NombreSede'] ?? $sede['TipoSede']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No hay sedes disponibles</option>
                            <?php endif; ?>

                        </select>

                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                    </div>

                    <!-- INPUT TELÉFONO -->
                    <div class="col-md-4 mb-3">
                        <label for="TelefonoFuncionario" class="form-label">Teléfono *</label>

                        <input type="text" id="TelefonoFuncionario" name="TelefonoFuncionario"
                            maxlength="10"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: 3001234567">

                        <div class="invalid-feedback">
                            Debe contener exactamente 10 dígitos numéricos.
                        </div>
                    </div>

                    <!-- INPUT DOCUMENTO -->
                    <div class="col-md-4 mb-3">
                        <label for="DocumentoFuncionario" class="form-label">Documento *</label>

                        <input type="text" id="DocumentoFuncionario" name="DocumentoFuncionario"
                            maxlength="11"
                            class="form-control border-primary shadow-sm"
                            placeholder="Ej: 10024567891">

                        <div class="invalid-feedback">
                            Debe contener exactamente 11 dígitos numéricos.
                        </div>
                    </div>
                </div>

                <!-- INPUT CORREO -->
                <div class="mb-3">
                    <label for="CorreoFuncionario" class="form-label">Correo Electrónico *</label>

                    <input type="email" id="CorreoFuncionario" name="CorreoFuncionario"
                        maxlength="100"
                        class="form-control border-primary shadow-sm"
                        placeholder="Ej: correo@dominio.com">

                    <div class="invalid-feedback">
                        Ingrese un formato de correo válido (debe incluir @ y .).
                    </div>
                </div>

                <!-- BOTÓN REGISTRAR -->
                <div class="text-end">
                    <button type="submit" class="btn btn-success" id="btnRegistrar">
                        <i class="fas fa-save me-1"></i> Registrar Funcionario
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- ================================================================
         TARJETA INFORMATIVA SOBRE EL QR
    ================================================================ -->
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
        </div>

        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                El código QR se generará automáticamente después de guardar los datos del funcionario.
            </div>
        </div>
    </div>
</div>

<!-- PIE DE PÁGINA GENERAL -->
<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- Librerías JS -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script inline (reemplaza tu Instituto.js por ahora) -->
<script>// ========================================
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

}); // ← ESTA ES LA CORRECCIÓN: Cerrar el document.ready </script>