<?php
session_start();

// Carga la parte superior del layout
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';

// ========================================
// DETERMINAR SI ESTAMOS EN MODO EDICIÓN
// ========================================
$modoEdicion = false;
$institucion = null;

if (isset($_GET['IdInstitucion']) && is_numeric($_GET['IdInstitucion'])) {
    $modoEdicion = true;
    $idInstitucion = intval($_GET['IdInstitucion']);
    
    // Cargar datos de la institución
    require_once __DIR__ . '/../../Core/Conexion.php';
    try {
        $conexion = new Conexion();
        $db = $conexion->getConexion();
        
        $sql = "SELECT * FROM institucion WHERE IdInstitucion = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $idInstitucion, PDO::PARAM_INT);
        $stmt->execute();
        $institucion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$institucion) {
            echo "<script>
                alert('Institución no encontrada.');
                window.location.href = 'InstitutoLista.php';
            </script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>
            alert('Error al cargar institución.');
            window.location.href = 'InstitutoLista.php';
        </script>";
        exit;
    }
}
?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-university me-2"></i>
            <?php echo $modoEdicion ? 'Editar Institución' : 'Registrar Institución'; ?>
        </h1>

        <a href="InstitutoLista.php" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Instituciones
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php echo $modoEdicion ? 'Modificar Datos' : 'Formulario de Registro'; ?>
            </h6>
        </div>

        <div class="card-body">

            <form id="formInstituto">

                <!-- CAMPO OCULTO: ACCIÓN -->
                <input type="hidden" name="accion" value="<?php echo $modoEdicion ? 'editar' : 'registrar'; ?>">

                <!-- CAMPO OCULTO: ID (solo en edición) -->
                <?php if ($modoEdicion): ?>
                    <input type="hidden" name="IdInstitucion" value="<?php echo $institucion['IdInstitucion']; ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="NombreInstitucion" class="form-label">Nombre de la Institución</label>
                        <input type="text" 
                               id="NombreInstitucion" 
                               name="NombreInstitucion" 
                               class="form-control shadow-sm" 
                               placeholder="Ej: Universidad Nacional" 
                               value="<?php echo $modoEdicion ? htmlspecialchars($institucion['NombreInstitucion']) : ''; ?>"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="Nit_Codigo" class="form-label">NIT / Código</label>
                        <input type="text" 
                               id="Nit_Codigo" 
                               name="Nit_Codigo" 
                               class="form-control shadow-sm" 
                               placeholder="Ej: 9001234567" 
                               maxlength="10"
                               value="<?php echo $modoEdicion ? htmlspecialchars($institucion['Nit_Codigo']) : ''; ?>"
                               required>
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="TipoInstitucion" class="form-label">Tipo de Institución</label>
                        <select id="TipoInstitucion" 
                                name="TipoInstitucion" 
                                class="form-control shadow-sm" 
                                required>
                            <option value="">Seleccione tipo...</option>
                            <option value="Universidad" <?php echo ($modoEdicion && $institucion['TipoInstitucion'] == 'Universidad') ? 'selected' : ''; ?>>Universidad</option>
                            <option value="Colegio" <?php echo ($modoEdicion && $institucion['TipoInstitucion'] == 'Colegio') ? 'selected' : ''; ?>>Colegio</option>
                            <option value="Otro" <?php echo ($modoEdicion && $institucion['TipoInstitucion'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="EstadoInstitucion" class="form-label">Estado</label>
                        <select id="EstadoInstitucion" 
                                name="EstadoInstitucion" 
                                class="form-control shadow-sm" 
                                required>
                            <option value="Activo" <?php echo (!$modoEdicion || $institucion['EstadoInstitucion'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="Inactivo" <?php echo ($modoEdicion && $institucion['EstadoInstitucion'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>

                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success" id="btnGuardar">
                        <i class="fas fa-save me-1"></i>
                        <?php echo $modoEdicion ? 'Actualizar Institución' : 'Registrar Institución'; ?>
                    </button>
                    <a href="InstitutoLista.php" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- jQuery -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script inline (reemplaza tu Instituto.js por ahora) -->
<script>
$(document).ready(function () {
    console.log('=== SISTEMA DE REGISTRO/EDICIÓN DE INSTITUTO INICIADO ===');
    
    // ===== FUNCIONES DE VALIDACIÓN VISUAL INLINE =====
    
    function marcarInvalido(campo) {
        campo.css("border", "2px solid #ef4444");
        campo.css("box-shadow", "0 0 0 0.25rem rgba(239, 68, 68, 0.25)");
    }

    function marcarValido(campo) {
        campo.css("border", "2px solid #10b981");
        campo.css("box-shadow", "0 0 0 0.25rem rgba(16, 185, 129, 0.25)");
    }

    function marcarNeutral(campo) {
        campo.css("border", ""); 
        campo.css("box-shadow", "");
    }
    
    // ===== DETECTAR MODO EDICIÓN =====
    var modoEdicion = $('input[name="IdInstitucion"]').length > 0;
    
    // ===== FUNCIÓN DE INICIALIZACIÓN VISUAL =====
    // Fuerza a que los campos de selección inicien neutrales.
    function inicializarValidacion() {
        marcarNeutral($("#TipoInstitucion"));
        marcarNeutral($("#EstadoInstitucion"));
    }

    // Ejecutar la inicialización al cargar la página
    inicializarValidacion();

    // ===== SI ESTAMOS EN MODO EDICIÓN, MARCAR CAMPOS EN ROJO AL INICIO =====
    if (modoEdicion) {
        console.log('=== MODO EDICIÓN DETECTADO ===');
        setTimeout(function() {
            marcarInvalido($("#NombreInstitucion"));
            marcarInvalido($("#Nit_Codigo"));
        }, 100);
    }


    // ===== VALIDACIÓN EN TIEMPO REAL (VERDE/ROJO INMEDIATO) =====

    // 1. NOMBRE DE INSTITUCIÓN
    $("#NombreInstitucion").on("input", function () {
        let campo = $(this);
        let valor = campo.val();
        
        // La validación en tiempo real para campos de texto es correcta (inician neutral)
        let valorLimpio = valor.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ ]/g, "");
        campo.val(valorLimpio);

        if (valorLimpio.length >= 3 && /^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/.test(valorLimpio)) {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 2. NIT/CÓDIGO
    $("#Nit_Codigo").on("input", function () {
        let campo = $(this);
        let valor = campo.val();
        
        let valorLimpio = valor.replace(/\D/g, "");
        valorLimpio = valorLimpio.substring(0, 10);
        campo.val(valorLimpio);

        if (valorLimpio.length === 10) {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 3. TIPO DE INSTITUCIÓN
    $("#TipoInstitucion").on("change", function () {
        let campo = $(this);
        if (campo.val() !== "") {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    // 4. ESTADO
    $("#EstadoInstitucion").on("change", function () {
        let campo = $(this);
        if (campo.val() !== "") {
            marcarValido(campo);
        } else {
            marcarInvalido(campo);
        }
    });

    
    // ===== FUNCIÓN DE ENVÍO DE REGISTRO/EDICIÓN (AJAX) =====
    $("#formInstituto").submit(function (e) {
        e.preventDefault();

        const nombre = $("#NombreInstitucion");
        const nit = $("#Nit_Codigo");
        const tipo = $("#TipoInstitucion");

        let errores = [];

        // Forzamos las validaciones finales
        if (nombre.val().length < 3 || !/^[A-Za-zÁÉÍÓÚÑáéíóúñ ]+$/.test(nombre.val())) {
            errores.push("• El nombre debe contener solo letras (mínimo 3 caracteres).");
            marcarInvalido(nombre);
        } else {
            marcarValido(nombre);
        }

        if (nit.val().length !== 10) {
            errores.push("• El NIT debe tener exactamente 10 números.");
            marcarInvalido(nit);
        } else {
            marcarValido(nit);
        }

        if (tipo.val() === "") {
            errores.push("• Debe seleccionar un tipo de institución.");
            marcarInvalido(tipo);
        } else {
            marcarValido(tipo);
        }

        // Estado (aunque tiene valor por defecto, lo validamos)
        if ($("#EstadoInstitucion").val() === "") {
            errores.push("• Debe seleccionar un estado.");
            marcarInvalido($("#EstadoInstitucion"));
        } else {
            marcarValido($("#EstadoInstitucion"));
        }


        if (errores.length > 0) {
            Swal.fire({
                icon: "error",
                title: "Error de validación",
                html: "<div style='text-align: left;'>" + errores.join("<br>") + "</div>",
                confirmButtonText: "OK",
                confirmButtonColor: "#ef4444",
            });
            return;
        }
        
        // --- PROCESO AJAX ---
        
        // Detectar si es edición o registro
        var esEdicion = $('input[name="IdInstitucion"]').length > 0;
        var tituloAccion = esEdicion ? 'Actualizando institución...' : 'Registrando institución...';
        var tituloExito = esEdicion ? '¡Actualización Exitosa!' : '¡Registro Exitoso!';
        
        Swal.fire({ 
            title: tituloAccion,
            html: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true); 

        $.ajax({
            url: '../../Controller/Controladorinstituto.php', 
            type: "POST",
            data: $(this).serialize(),
            dataType: 'json', 
            
            success: function (response) {
                Swal.close(); 
                if (response.ok === true) {
                    Swal.fire({
                        icon: "success",
                        title: tituloExito,
                        text: response.message, 
                        confirmButtonText: "OK",
                        confirmButtonColor: "#10b981"
                    }).then(() => {
                        if (esEdicion) {
                            // Si es edición, redirigir a la lista
                            window.location.href = 'InstitutoLista.php';
                        } else {
                            // Si es registro, limpiar formulario
                            $("#formInstituto")[0].reset();
                            // Limpiar estilos después del éxito
                            inicializarValidacion(); // Vuelve a dejarlos neutrales
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error en " + (esEdicion ? "la Actualización" : "el Registro"),
                        text: response.message || 'Ocurrió un error inesperado al guardar.', 
                        confirmButtonText: "OK",
                        confirmButtonColor: "#ef4444"
                    });
                }
            },
            error: function (xhr) {
                Swal.close(); 
                
                // Limpiar estilos en caso de error (solo en registro)
                if (!esEdicion) {
                    inicializarValidacion(); // Vuelve a dejarlos neutrales
                }

                let mensaje = `Error de conexión con el servidor. Revisar logs de PHP.`;
                let responseMessage = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : xhr.responseText;
                
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    html: `<p>${mensaje}</p><p>Detalle: ${responseMessage.substring(0, 100)}...</p><small>Código: ${xhr.status}</small>`,
                    confirmButtonText: "OK",
                    confirmButtonColor: "#ef4444"
                });
            },
            complete: function () {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
});
</script>