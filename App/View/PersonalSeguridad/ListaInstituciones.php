<?php
// ======================================================================
// VISTA: InstitutoLista.php
// Responsabilidad: SOLO visualizar datos y filtros. Solo lectura.
// Sin edición, sin candado, sin acciones.
// ======================================================================

require_once __DIR__ . '/../layouts/parte_superior.php';
require_once __DIR__ . '/../../Model/modeloinstituto.php';

$modeloInstituto = new ModeloInstituto();

// ── Recoge los filtros del GET ───────────────────────────────────────────────
$nombre = trim($_GET['nombre'] ?? '');
$tipo   = trim($_GET['tipo']   ?? '');
$estado = trim($_GET['estado'] ?? '');

// Obtiene todos los institutos y filtra en PHP
$todosInstitutos = $modeloInstituto->listarInstitutos();

$institutos = array_filter($todosInstitutos, function ($fila) use ($nombre, $tipo, $estado) {
    if ($nombre !== '' && stripos($fila['NombreInstitucion'], $nombre) === false) return false;
    if ($tipo   !== '' && $fila['TipoInstitucion']   !== $tipo)                   return false;
    if ($estado !== '' && $fila['EstadoInstitucion'] !== $estado)                 return false;
    return true;
});

// Valores únicos para los selects de filtro
$tiposUnicos = array_unique(array_column($todosInstitutos, 'TipoInstitucion'));
sort($tiposUnicos);
?>

<div class="container-fluid px-4 py-4">

    <!-- ── ENCABEZADO ── -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-school me-2"></i>Instituciones Registradas
        </h1>
    </div>

    <!-- ══════════════════════════════════════════════════════
         CARD FILTROS — estilos idénticos a VehiculoLista.php
    ══════════════════════════════════════════════════════ -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i>Filtrar Instituciones
            </h6>
            <a href="ListaInstituciones.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-broom mr-1"></i>Limpiar filtros
            </a>
        </div>
        <div class="card-body">
            <form method="get">
                <div class="row align-items-end">

                    <!-- Nombre -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-school mr-1 text-primary"></i>Nombre
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" name="nombre" id="filtroNombre"
                                   class="form-control"
                                   value="<?= htmlspecialchars($nombre) ?>"
                                   placeholder="Ej: Colegio Nacional">
                        </div>
                    </div>

                    <!-- Tipo -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-tags mr-1 text-primary"></i>Tipo
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Instituciones</h6>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaInstitutos"
                   class="table table-bordered table-hover table-striped align-middle text-center"
                   width="100%">

                <thead class="table-dark">
                    <tr>
                        <th class="text-start">Nombre</th>
                        <th>NIT</th>
                        <th>Tipo</th>
                        <th>Dirección</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($institutos)) : ?>
                        <?php foreach ($institutos as $fila) : ?>
                            <tr>
                                <td class="text-start"><?= htmlspecialchars($fila['NombreInstitucion']) ?></td>
                                <td><?= htmlspecialchars($fila['Nit_Codigo']) ?></td>
                                <td><?= htmlspecialchars($fila['TipoInstitucion']) ?></td>
                                <td>
                                    <?= !empty($fila['DireccionInstitucion'])
                                        ? htmlspecialchars($fila['DireccionInstitucion'])
                                        : '<span class="badge bg-secondary" style="color:#fff;">Sin dirección</span>' ?>
                                </td>
                                <!-- Badge de estado -->
                                <td>
                                    <?php if ($fila['EstadoInstitucion'] === 'Activo') : ?>
                                        <span class="badge bg-success text-white px-3 py-2">
                                            Activo
                                        </span>
                                    <?php else : ?>
                                        <span class="badge text-white px-3 py-2"
                                              style="background-color:#60a5fa;">
                                            Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                                <p class="text-muted mb-0">
                                    No hay instituciones registradas con los filtros seleccionados
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<!-- CSS DataTables -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<link rel="stylesheet" href="../../../Public/css/Tablas.css">

<!-- JS -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {
    $('#tablaInstitutos').DataTable({
        ordering:   false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        responsive: true,
        language: {
            emptyTable:   "No hay instituciones registradas",
            info:         "Mostrando _START_ a _END_ de _TOTAL_ instituciones",
            infoEmpty:    "Mostrando 0 a 0 de 0 instituciones",
            infoFiltered: "(filtrado de _MAX_ instituciones)",
            lengthMenu:   "Mostrar _MENU_ instituciones",
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