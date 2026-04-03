<?php
// ======================================================================
// VISTA: SedeLista.php
// Responsabilidad: SOLO visualizar datos y filtros. Solo lectura.
// ======================================================================

require_once __DIR__ . '/../layouts/parte_superior_Supervisor.php';
require_once __DIR__ . '/../../Model/ModeloSede.php';

$modeloSede = new ModeloSede();

// ── Recoge los filtros del GET ───────────────────────────────────────────────
$tipo   = trim($_GET['tipo']   ?? '');
$ciudad = trim($_GET['ciudad'] ?? '');
$estado = trim($_GET['estado'] ?? '');

// La vista pide los datos al modelo pasándole los filtros
// El modelo construye la consulta SQL de forma segura (LIKE con parámetros)
$sedes = $modeloSede->obtenerSedes($tipo, $ciudad, $estado);

// Para los selects del filtro se obtienen todos sin filtrar
$todasSedes     = $modeloSede->obtenerSedes();
$tiposUnicos    = array_unique(array_column($todasSedes, 'TipoSede'));
$ciudadesUnicas = array_unique(array_column($todasSedes, 'Ciudad'));
sort($tiposUnicos);
sort($ciudadesUnicas);
?>

<div class="container-fluid px-4 py-4">

    <!-- ── ENCABEZADO ── -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building me-2"></i>Sedes Registradas
        </h1>
    </div>

    <!-- ══════════════════════════════════════════════════════
         CARD FILTROS — estilos idénticos a VehiculoLista.php
    ══════════════════════════════════════════════════════ -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i>Filtrar Sedes
            </h6>
            <a href="SedeLista.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-broom mr-1"></i>Limpiar filtros
            </a>
        </div>
        <div class="card-body">
            <form method="get">
                <div class="row align-items-end">

                    <!-- Tipo de Sede -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-building mr-1 text-primary"></i>Tipo de Sede
                        </label>
                        <select name="tipo" id="filtroTipo" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($tiposUnicos as $t) : ?>
                                <option value="<?= htmlspecialchars($t) ?>"
                                    <?= ($tipo === $t) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Ciudad -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Ciudad
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" name="ciudad" id="filtroCiudad"
                                   class="form-control"
                                   value="<?= htmlspecialchars($ciudad) ?>"
                                   placeholder="Ej: Bogotá"
                                   style="text-transform:capitalize;">
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-toggle-on mr-1 text-primary"></i>Estado
                        </label>
                        <select name="estado" id="filtroEstado" class="form-control">
                            <option value="">Todos</option>
                            <option value="Activo"   <?= ($estado === 'Activo')   ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= ($estado === 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>

                    <!-- Botón Filtrar -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label d-block invisible">.</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i>Filtrar
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         CARD TABLA
    ══════════════════════════════════════════════════════ -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Sedes</h6>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaSedes"
                   class="table table-bordered table-hover table-striped align-middle text-center"
                   width="100%">

                <thead class="table-dark">
                    <tr>
                        <th>Tipo de Sede</th>
                        <th>Ciudad</th>
                        <th>Institución</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($sedes)) : ?>
                        <?php foreach ($sedes as $fila) : ?>
                            <tr id="fila-<?= $fila['IdSede'] ?>">
                                <td><?= htmlspecialchars($fila['TipoSede']) ?></td>
                                <td><?= htmlspecialchars($fila['Ciudad']) ?></td>
                                <td><?= htmlspecialchars($fila['NombreInstitucion']) ?></td>

                                <!-- Badge de estado -->
                                <td>
                                    <?php if ($fila['Estado'] === 'Activo') : ?>
                                        <span class="badge bg-success text-white px-3 py-2 estado-badge">
                                            Activo
                                        </span>
                                    <?php else : ?>
                                        <span class="badge text-white px-3 py-2 estado-badge"
                                              style="background-color:#60a5fa;">
                                            Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                                <p class="text-muted mb-0">
                                    No hay sedes registradas con los filtros seleccionados
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_Supervisor.php'; ?>

<!-- CSS DataTables -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<link rel="stylesheet" href="../../../Public/css/Tablas.css">

<!-- JS -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {
    $('#tablaSedes').DataTable({
        ordering:   false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        responsive: true,
        language: {
            emptyTable:   "No hay sedes registradas",
            info:         "Mostrando _START_ a _END_ de _TOTAL_ sedes",
            infoEmpty:    "Mostrando 0 a 0 de 0 sedes",
            infoFiltered: "(filtrado de _MAX_ sedes)",
            lengthMenu:   "Mostrar _MENU_ sedes",
            search:       "Buscar:",
            zeroRecords:  "No se encontraron resultados",
            paginate: {
                first:    "Primera",
                last:     "Última",
                next:     "Siguiente",
                previous: "Anterior"
            }
        }
    });
});
</script>