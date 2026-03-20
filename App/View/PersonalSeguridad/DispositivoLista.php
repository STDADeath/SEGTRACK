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
if (!empty($_GET['funcionario'])) {
    $filtros[] = "d.IdFuncionario = :funcionario";
    $params[':funcionario'] = $_GET['funcionario'];
}
if (!empty($_GET['visitante'])) {
    $filtros[] = "d.IdVisitante = :visitante";
    $params[':visitante'] = $_GET['visitante'];
}
if (!empty($_GET['serial'])) {
    $filtros[] = "d.NumeroSerial LIKE :serial";
    $params[':serial'] = '%' . $_GET['serial'] . '%';
}

$where = "WHERE " . implode(" AND ", $filtros);

$sql = "SELECT
            d.*,
            f.NombreFuncionario,
            f.CorreoFuncionario,
            v.NombreVisitante
        FROM dispositivo d
        LEFT JOIN funcionario f ON d.IdFuncionario = f.IdFuncionario
        LEFT JOIN visitante   v ON d.IdVisitante   = v.IdVisitante
        $where
        ORDER BY d.IdDispositivo DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <a href="Dispositivolista.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-broom mr-1"></i>Limpiar filtros
            </a>
        </div>
        <div class="card-body">
            <form method="get">
                <div class="row align-items-end">

                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-laptop mr-1 text-primary"></i>Tipo
                        </label>
                        <select name="tipo" id="tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="Portatil"   <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Portatil')   ? 'selected' : '' ?>>💻 Portátil</option>
                            <option value="Tablet"     <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Tablet')     ? 'selected' : '' ?>>📱 Tablet</option>
                            <option value="Computador" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Computador') ? 'selected' : '' ?>>🖥️ Computador</option>
                            <option value="Otro"       <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Otro')       ? 'selected' : '' ?>>📦 Otro</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-tag mr-1 text-primary"></i>Marca
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="marca" id="marca" class="form-control"
                                value="<?= htmlspecialchars($_GET['marca'] ?? '') ?>"
                                placeholder="Buscar por marca">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold text-gray-700 small text-uppercase">
                            <i class="fas fa-barcode mr-1 text-primary"></i>Número Serial
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="serial" id="serial" class="form-control"
                                value="<?= htmlspecialchars($_GET['serial'] ?? '') ?>"
                                placeholder="Buscar por serial">
                        </div>
                    </div>

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
                        <th>Funcionario</th>
                        <th>Visitante</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <?php
                            $correoDisponible = $row['CorreoFuncionario'] ?? '';
                            $tieneCorreo      = !empty($correoDisponible);
                            ?>
                            <tr id="fila-<?= $row['IdDispositivo'] ?>">
                                <td class="text-center">
                                    <?php if (!empty($row['QrDispositivo'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-success mb-1"
                                                onclick="verQRDispositivo('<?= htmlspecialchars($row['QrDispositivo']) ?>', <?= $row['IdDispositivo'] ?>)"
                                                title="Ver código QR">
                                            <i class="fas fa-qrcode me-1"></i> Ver
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-info <?= !$tieneCorreo ? 'disabled' : '' ?>"
                                                onclick="enviarQRPorCorreo(<?= $row['IdDispositivo'] ?>, '<?= htmlspecialchars($correoDisponible) ?>')"
                                                title="<?= $tieneCorreo ? 'Enviar QR por correo' : 'Solo funcionarios pueden recibir correo' ?>"
                                                <?= !$tieneCorreo ? 'disabled' : '' ?>>
                                            <i class="fas fa-envelope me-1"></i> Enviar
                                        </button>
                                    <?php else : ?>
                                        <span class="badge badge-warning">Sin QR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['TipoDispositivo']) ?></td>
                                <td><?= htmlspecialchars($row['MarcaDispositivo']) ?></td>
                                <td>
                                    <?php if (!empty($row['NumeroSerial'])) : ?>
                                        <?= htmlspecialchars($row['NumeroSerial']) ?>
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No tiene número serial</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['NombreFuncionario'])) : ?>
                                        <?= htmlspecialchars($row['NombreFuncionario']) ?>
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No aplica</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['NombreVisitante'])) : ?>
                                        <?= htmlspecialchars($row['NombreVisitante']) ?>
                                    <?php else : ?>
                                        <span class="badge bg-info text-white">No aplica</span>
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
                            <td colspan="7" class="text-center py-4">
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

<!-- Modal Ver QR -->
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

<!-- Modal Editar Dispositivo -->
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
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" id="editMarcaDispositivo" class="form-control" name="marca"
                                       required placeholder="Ej: HP, Dell, Lenovo">
                            </div>
                            <small class="text-muted">Puede modificar la marca del dispositivo</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número Serial</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" id="editNumeroSerial" class="form-control" name="serial"
                                       placeholder="Ej: SN123456789" maxlength="50">
                            </div>
                            <small class="text-muted">Campo opcional - Solo letras, números, guiones (-) y guiones bajos (_)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Funcionario <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editNombreFuncionario" class="form-control bg-light" readonly>
                            <input type="hidden" id="editIdFuncionario" name="id_funcionario">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Visitante <small class="text-muted">(Solo lectura)</small></label>
                            <input type="text" id="editNombreVisitante" class="form-control bg-light" readonly>
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

<script src="/SEGTRACK/Public/js/javascript/js/ValidacionDispositivo.js"></script>