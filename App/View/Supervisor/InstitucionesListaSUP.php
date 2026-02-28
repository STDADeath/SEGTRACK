<?php
/**
 * VISTA: LISTA DE INSTITUCIONES
 * Acceso permitido: Personal Seguridad y Supervisor
 */

// ==============================
// VALIDAR SESIÓN
// ==============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../Login/Login.php");
    exit();
}

// ==============================
// VALIDAR ROL PERMITIDO
// ==============================
$rol = trim($_SESSION['usuario']['TipoRol']);

if ($rol !== 'Personal Seguridad' && $rol !== 'Supervisor') {
    header("Location: ../Login/Login.php");
    exit();
}

require_once __DIR__ . '/../layouts/parte_superior.php';
require_once __DIR__ . '/../../Core/Conexion.php';
?>
<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-school me-2"></i>Instituciones Registradas
        </h1>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-primary fw-bold">Lista de Instituciones</h5>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaInstitutos" class="table table-hover align-middle" width="100%">

                <thead class="text-white" style="background-color:#5f636e;">
                    <tr>
                        <th>Nombre</th>
                        <th>NIT</th>
                        <th>Tipo</th>
                        <th>Dirección</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    try {
                        $conexion = new Conexion();
                        $db       = $conexion->getConexion();
                        $sql  = "SELECT NombreInstitucion, Nit_Codigo, TipoInstitucion,
                                        DireccionInstitucion, EstadoInstitucion
                                 FROM institucion ORDER BY NombreInstitucion ASC";
                        $stmt = $db->prepare($sql);
                        $stmt->execute();

                        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)):
                            $dir = trim($fila['DireccionInstitucion'] ?? '');
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['NombreInstitucion']) ?></td>
                            <td><?= htmlspecialchars($fila['Nit_Codigo']) ?></td>
                            <td><?= htmlspecialchars($fila['TipoInstitucion']) ?></td>
                            <td>
                                <?php if ($dir !== ''): ?>
                                    <?= htmlspecialchars($dir) ?>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Sin dirección registrada</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($fila['EstadoInstitucion'] === 'Activo'): ?>
                                    <span class="badge bg-success text-white">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-primary text-white">Inactivo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    } catch (PDOException $e) {
                        echo '<tr><td colspan="5" class="text-center py-4 text-danger">
                                Error al cargar los datos
                              </td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<!-- CSS DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">

<!-- JS -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tablaInstitutos').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        pageLength: 10,
        order: []
    });
});
</script>