<?php 
session_start(); // Inicia la sesión PHP para mantener datos del usuario autenticado

// Carga la parte superior del layout (navbar, sidebar, head con CSS)
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';

// ========================================
// DETERMINAR SI ESTAMOS EN MODO EDICIÓN
// ========================================
$modoEdicion = false; // Por defecto asumimos registro nuevo
$institucion = null;  // Contendrá los datos de la institución a editar

// Si viene un ID válido por GET → modo edición
if (isset($_GET['IdInstitucion']) && is_numeric($_GET['IdInstitucion'])) {
    $modoEdicion = true;
    $idInstitucion = intval($_GET['IdInstitucion']); // Entero para prevenir SQL Injection

    require_once __DIR__ . '/../../Core/Conexion.php';
    try {
        $conexion = new Conexion();
        $db = $conexion->getConexion();

        // Consulta preparada: previene SQL Injection con parámetro vinculado
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

    <!-- Barra superior con título dinámico y botón de regreso -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-university me-2"></i>
            <?php echo $modoEdicion ? 'Editar Institución' : 'Registrar Institución'; ?>
        </h1>

        <!-- Botón para volver a la lista -->
        <a href="InstitutoLista.php" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Instituciones
        </a>
    </div>

    <!-- Tarjeta contenedora del formulario -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php echo $modoEdicion ? 'Modificar Datos' : 'Formulario de Registro'; ?>
            </h6>
        </div>

        <div class="card-body">

            <!-- El submit es interceptado por jQuery (AJAX), no recarga la página -->
            <form id="formInstituto">

                <!-- Indica al controlador qué acción ejecutar: registrar o editar -->
                <input type="hidden" name="accion" value="<?php echo $modoEdicion ? 'editar' : 'registrar'; ?>">

                <!-- Solo se envía el ID en modo edición -->
                <?php if ($modoEdicion): ?>
                    <input type="hidden" name="IdInstitucion" value="<?php echo $institucion['IdInstitucion']; ?>">
                <?php endif; ?>

                <!-- Estado siempre Activo al registrar — no se muestra al usuario -->
                <!-- En edición tampoco se muestra: el estado se controla desde la lista con el candado -->
                <input type="hidden" name="EstadoInstitucion" value="Activo">

                <div class="row">
                    <!-- Campo: Nombre de la institución -->
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

                    <!-- Campo: NIT numérico de 10 dígitos -->
                    <div class="col-md-6 mb-3">
                        <label for="Nit_Codigo" class="form-label">NIT</label>
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

                    <!-- Campo: Tipo de institución -->
                    <div class="col-md-6 mb-3">
                        <label for="TipoInstitucion" class="form-label">Tipo de Institución</label>
                        <select id="TipoInstitucion"
                                name="TipoInstitucion"
                                class="form-control shadow-sm"
                                required>
                            <option value="">Seleccione tipo...</option>
                            <option value="Universidad" <?php echo ($modoEdicion && $institucion['TipoInstitucion'] == 'Universidad') ? 'selected' : ''; ?>>Universidad</option>
                            <option value="Colegio"     <?php echo ($modoEdicion && $institucion['TipoInstitucion'] == 'Colegio')     ? 'selected' : ''; ?>>Colegio</option>
                            <option value="Empresa"     <?php echo ($modoEdicion && $institucion['TipoInstitucion'] == 'Empresa')     ? 'selected' : ''; ?>>Empresa</option>
                            <option value="ONG"         <?php echo ($modoEdicion && $institucion['TipoInstitucion'] == 'ONG')         ? 'selected' : ''; ?>>ONG</option>
                            <option value="Hospital"    <?php echo ($modoEdicion && $institucion['TipoInstitucion'] == 'Hospital')    ? 'selected' : ''; ?>>Hospital</option>
                            <option value="Otro"        <?php echo ($modoEdicion && $institucion['TipoInstitucion'] == 'Otro')        ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>

                    <!-- Campo: Dirección (opcional) -->
                    <div class="col-md-6 mb-3">
                        <label for="DireccionInstitucion" class="form-label">
                            Dirección <small class="text-muted">(opcional)</small>
                        </label>
                        <input type="text"
                               id="DireccionInstitucion"
                               name="DireccionInstitucion"
                               class="form-control shadow-sm"
                               placeholder="Ej: Calle 45 # 23-10, Bogotá"
                               maxlength="150"
                               value="<?php echo ($modoEdicion && !empty($institucion['DireccionInstitucion'])) ? htmlspecialchars($institucion['DireccionInstitucion']) : ''; ?>">
                        <!-- Guía visual para el usuario sobre los caracteres permitidos -->
                        <small class="text-muted">Solo letras, números, espacios, guiones, # y comas.</small>
                    </div>

                </div>

                <!-- Botones alineados a la derecha -->
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
<!-- SweetAlert2: librería externa para alertas modales elegantes -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Script de validación y envío AJAX del formulario -->
<script src="../../../Public/js/javascript/js/ValidacionInstituto.js"></script>