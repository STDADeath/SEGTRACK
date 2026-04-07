<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php");?>

<?php
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Construcción de filtros dinámicos
$filtros = [];
$params  = [];

// FILTRO OBLIGATORIO: Solo mostrar dispositivos activos
$filtros[] = "d.Estado = :estado";
$params[':estado'] = 'Activo';

if (!empty($_GET['tipo'])) {
    $filtros[] = "d.TipoDispositivo = :tipo";
    $params[':tipo'] = $_GET['tipo'];
}
if (!empty($_GET['marca'])) {
    $filtros[] = "d.MarcaDispositivo LIKE :marca";
    $params[':marca'] = '%' . $_GET['marca'] . '%';
}
if (!empty($_GET['serial'])) {
    $filtros[] = "d.NumeroSerial LIKE :serial";
    $params[':serial'] = '%' . $_GET['serial'] . '%';
}
if (!empty($_GET['sede'])) {
    $filtros[] = "COALESCE(f.IdSede, v.IdSede) = :sede";
    $params[':sede'] = $_GET['sede'];
}
if (!empty($_GET['institucion'])) {
    $filtros[] = "COALESCE(s_func.IdInstitucion, s_vis.IdInstitucion) = :institucion";
    $params[':institucion'] = $_GET['institucion'];
}
if (!empty($_GET['propietario'])) {
    if ($_GET['propietario'] === 'Funcionario') {
        $filtros[] = "d.IdFuncionario IS NOT NULL AND d.IdVisitante IS NULL";
    } elseif ($_GET['propietario'] === 'Visitante') {
        $filtros[] = "d.IdVisitante IS NOT NULL AND d.IdFuncionario IS NULL";
    }
}

$where = "WHERE " . implode(" AND ", $filtros);

$sql = "SELECT
            d.*,
            f.NombreFuncionario,
            f.CorreoFuncionario,
            v.NombreVisitante,
            v.CorreoVisitante,
            COALESCE(s_func.TipoSede, s_vis.TipoSede) AS TipoSede,
            COALESCE(s_func.Ciudad, s_vis.Ciudad) AS CiudadSede,
            COALESCE(i_func.NombreInstitucion, i_vis.NombreInstitucion) AS NombreInstitucion
        FROM dispositivo d
        LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario AND f.Estado = 'Activo'
        LEFT JOIN visitante   v ON d.IdVisitante   = v.IdVisitante AND v.Estado = 'Activo'
        LEFT JOIN sede s_func ON f.IdSede = s_func.IdSede
        LEFT JOIN sede s_vis  ON v.IdSede = s_vis.IdSede
        LEFT JOIN institucion i_func ON s_func.IdInstitucion = i_func.IdInstitucion
        LEFT JOIN institucion i_vis  ON s_vis.IdInstitucion = i_vis.IdInstitucion
        $where
        ORDER BY d.IdDispositivo DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar datos para filtros
$sqlInstituciones = "SELECT IdInstitucion, NombreInstitucion FROM institucion WHERE EstadoInstitucion = 'Activo' ORDER BY NombreInstitucion ASC";
$stmtInst = $conn->prepare($sqlInstituciones);
$stmtInst->execute();
$instituciones = $stmtInst->fetchAll(PDO::FETCH_ASSOC);

$sqlSedes = "SELECT IdSede, TipoSede, Ciudad FROM sede WHERE Estado = 'Activo' ORDER BY TipoSede ASC";
$stmtSedes = $conn->prepare($sqlSedes);
$stmtSedes->execute();
$sedesDisponibles = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Dispositivos Registrados</h1>
        <a href="./Dispositivos.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Dispositivo
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i>Filtrar Dispositivos
            </h6>
            <a href="DispositivoLista.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-broom mr-1"></i>Limpiar filtros
            </a>
        </div>
        <div class="card-body">
            <form method="get" id="formFiltrosDispositivo">
                <div class="row align-items-end">

                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-laptop mr-1 text-primary"></i>Tipo
                        </label>
                        <select name="tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="Portatil"   <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Portatil')   ? 'selected' : '' ?>>Portátil</option>
                            <option value="Tablet"     <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Tablet')     ? 'selected' : '' ?>>Tablet</option>
                            <option value="Computador" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Computador') ? 'selected' : '' ?>>Computador</option>
                            <option value="Otro"       <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Otro')       ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-tag mr-1 text-primary"></i>Marca
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="marca" class="form-control"
                                value="<?= htmlspecialchars($_GET['marca'] ?? '') ?>"
                                placeholder="Buscar por marca">
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-barcode mr-1 text-primary"></i>Número Serial
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="serial" class="form-control"
                                value="<?= htmlspecialchars($_GET['serial'] ?? '') ?>"
                                placeholder="Buscar por serial">
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-university mr-1 text-primary"></i>Institución
                        </label>
                        <select name="institucion" id="filtroInstitucion" class="form-control">
                            <option value="">Todas</option>
                            <?php foreach ($instituciones as $inst) : ?>
                                <option value="<?= $inst['IdInstitucion'] ?>"
                                    <?= (isset($_GET['institucion']) && $_GET['institucion'] == $inst['IdInstitucion']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($inst['NombreInstitucion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-map-marker-alt mr-1 text-primary"></i>Sede
                        </label>
                        <select name="sede" id="filtroSede" class="form-control">
                            <option value="">Todas</option>
                            <?php
                            if (!empty($_GET['institucion'])) {
                                $sqlSedesFiltro = "SELECT IdSede, TipoSede, Ciudad FROM sede WHERE IdInstitucion = :id AND Estado = 'Activo' ORDER BY TipoSede ASC";
                                $stmtSedeFiltro = $conn->prepare($sqlSedesFiltro);
                                $stmtSedeFiltro->execute([':id' => $_GET['institucion']]);
                                $sedesFiltro = $stmtSedeFiltro->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($sedesFiltro as $sede) {
                                    $selected = (isset($_GET['sede']) && $_GET['sede'] == $sede['IdSede']) ? 'selected' : '';
                                    echo '<option value="' . $sede['IdSede'] . '" ' . $selected . '>' . htmlspecialchars($sede['TipoSede']) . ' — ' . htmlspecialchars($sede['Ciudad']) . '</option>';
                                }
                            } else {
                                foreach ($sedesDisponibles as $sede) {
                                    $selected = (isset($_GET['sede']) && $_GET['sede'] == $sede['IdSede']) ? 'selected' : '';
                                    echo '<option value="' . $sede['IdSede'] . '" ' . $selected . '>' . htmlspecialchars($sede['TipoSede']) . ' — ' . htmlspecialchars($sede['Ciudad']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-user mr-1 text-primary"></i>Propietario
                        </label>
                        <select name="propietario" class="form-control">
                            <option value="">Todos</option>
                            <option value="Funcionario" <?= (isset($_GET['propietario']) && $_GET['propietario'] == 'Funcionario') ? 'selected' : '' ?>>Funcionario</option>
                            <option value="Visitante"   <?= (isset($_GET['propietario']) && $_GET['propietario'] == 'Visitante')   ? 'selected' : '' ?>>Visitante</option>
                        </select>
                    </div>

                </div>

                <div class="row mt-2">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i>Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Dispositivos Activos</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center" id="TablaDispositivo">
                <thead class="table-dark">
                    <tr>
                        <th>QR</th>
                        <th>Tipo</th>
                        <th>Marca</th>
                        <th>Número Serial</th>
                        <th>Propietario</th>
                        <th>Institución</th>
                        <th>Sede</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <?php
                            $nombrePropietario = !empty($row['NombreFuncionario']) 
                                ? $row['NombreFuncionario'] 
                                : ($row['NombreVisitante'] ?? 'No asignado');
                            $tipoPropietario = !empty($row['NombreFuncionario']) ? 'Funcionario' : 'Visitante';
                            $correoDisponible = !empty($row['CorreoFuncionario'])
                                ? $row['CorreoFuncionario']
                                : ($row['CorreoVisitante'] ?? '');
                            $tieneCorreo = !empty($correoDisponible);
                            ?>
                            <tr id="fila-<?= $row['IdDispositivo'] ?>">
                                <td class="text-center">
                                    <?php if (!empty($row['QrDispositivo'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-success mb-1"
                                                onclick="verQRDispositivo('<?= htmlspecialchars($row['QrDispositivo']) ?>', <?= $row['IdDispositivo'] ?>)"
                                                title="Ver código QR">
                                            <i class="fas fa-qrcode me-1"></i> Ver QR
                                        </button>
                                        <br>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-info mt-1 <?= !$tieneCorreo ? 'disabled' : '' ?>"
                                                onclick="enviarQRPorCorreo(<?= $row['IdDispositivo'] ?>, '<?= htmlspecialchars($correoDisponible) ?>')"
                                                title="<?= $tieneCorreo ? 'Enviar QR por correo' : 'No hay correo registrado' ?>"
                                                <?= !$tieneCorreo ? 'disabled' : '' ?>>
                                            <i class="fas fa-envelope me-1"></i> Enviar
                                        </button>
                                    <?php else : ?>
                                        <span class="badge bg-warning text-dark">Sin QR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['TipoDispositivo']) ?></td>
                                <td><?= htmlspecialchars($row['MarcaDispositivo']) ?></td>
                                <td>
                                    <?php if (!empty($row['NumeroSerial'])) : ?>
                                        <?= htmlspecialchars($row['NumeroSerial']) ?>
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No tiene serial</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $tipoPropietario === 'Funcionario' ? 'primary' : 'success' ?>">
                                        <?= $tipoPropietario ?>
                                    </span><br>
                                    <small><?= htmlspecialchars($nombrePropietario) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($row['NombreInstitucion'])) : ?>
                                        <?= htmlspecialchars($row['NombreInstitucion']) ?>
                                    <?php else : ?>
                                        <span class="badge bg-secondary text-white">Sin institución</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['TipoSede'])) : ?>
                                        <?= htmlspecialchars($row['TipoSede']) ?> — <?= htmlspecialchars($row['CiudadSede'] ?? '') ?>
                                    <?php else : ?>
                                        <span class="badge bg-secondary text-white">Sin sede</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick='cargarDatosEdicionDispositivo(<?= json_encode($row) ?>)'
                                        title="Editar dispositivo"
                                        data-toggle="modal" data-target="#modalEditarDispositivo">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No hay dispositivos activos registrados con los filtros seleccionados</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modales -->
<div class="modal fade" id="modalVerQRDispositivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>Código QR - Dispositivo #<span id="qrDispositivoId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImagenDispositivo" src="" alt="Código QR Dispositivo" class="img-fluid"
                    style="max-width:300px;border:2px solid #ddd;padding:10px;border-radius:5px;">
                <p class="text-muted mt-3">Escanea este código con tu dispositivo móvil</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a id="btnDescargarQRDispositivo" href="#" class="btn btn-success" download>
                    <i class="fas fa-download me-1"></i> Descargar QR
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarDispositivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Dispositivo</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditarDispositivo">
                    <input type="hidden" id="editIdDispositivo" name="id">
                    <input type="hidden" id="editAccion" name="accion" value="actualizar">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo Dispositivo <span class="text-danger">*</span></label>
                            <select id="editTipoDispositivo" class="form-control" name="tipo" required>
                                <option value="">-- Seleccione un tipo --</option>
                                <option value="Portatil">Portátil</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Computador">Computador</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marca <span class="text-danger">*</span></label>
                            <input type="text" id="editMarcaDispositivo" class="form-control" name="marca" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número Serial</label>
                            <input type="text" id="editNumeroSerial" class="form-control" name="serial">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Institución <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editNombreInstitucion" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sede <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editTipoSede" class="form-control bg-light" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Propietario <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editNombrePropietario" class="form-control bg-light" readonly>
                            <input type="hidden" id="editIdFuncionario" name="id_funcionario">
                            <input type="hidden" id="editIdVisitante" name="id_visitante">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnGuardarCambiosDispositivo">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<!-- PRIMERO jQuery, luego Bootstrap, luego SweetAlert, luego nuestro JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/SEGTRACK/Public/js/javascript/js/ValidacionDispositivo.js"></script>