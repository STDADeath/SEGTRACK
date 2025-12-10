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
<script src="../../../Public/js/javascript/js/ValidacionInstituto.js"></script>