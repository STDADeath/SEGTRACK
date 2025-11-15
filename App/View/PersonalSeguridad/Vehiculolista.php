<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
<?php require_once(__DIR__ . "/../../Core/conexion.php");?>

<?php
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Construcción de filtros dinámicos
$filtros = [];
$params = [];

if (!empty($_GET['tipo'])) {
    $filtros[] = "TipoVehiculo = :tipo";
    $params[':tipo'] = $_GET['tipo'];
}
if (!empty($_GET['placa'])) {
    $filtros[] = "PlacaVehiculo LIKE :placa";
    $params[':placa'] = '%' . $_GET['placa'] . '%';
}
if (!empty($_GET['tarjeta'])) {
    $filtros[] = "TarjetaPropiedad LIKE :tarjeta";
    $params[':tarjeta'] = '%' . $_GET['tarjeta'] . '%';
}
if (!empty($_GET['fecha'])) {
    $filtros[] = "DATE(FechaParqueadero) = :fecha";
    $params[':fecha'] = $_GET['fecha'];
}
if (!empty($_GET['sede'])) {
    $filtros[] = "IdSede = :sede";
    $params[':sede'] = $_GET['sede'];
}

$where = "";
if (count($filtros) > 0) {
    $where = "WHERE " . implode(" AND ", $filtros);
}

// Incluir el campo QrVehiculo en la consulta
$sql = "SELECT *, QrVehiculo FROM Parqueadero $where ORDER BY IdParqueadero DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-car me-2"></i>Vehículos Registrados</h1>
        <a href="Parqueadero.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Vehículo
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Vehículos</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-2">
                    <label for="tipo" class="form-label">Tipo de Vehículo</label>
                    <select name="tipo" id="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Bicicleta" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Bicicleta') ? 'selected' : '' ?>>Bicicleta</option>
                        <option value="Moto" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Moto') ? 'selected' : '' ?>>Moto</option>
                        <option value="Carro" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Carro') ? 'selected' : '' ?>>Carro</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="placa" class="form-label">Placa</label>
                    <input type="text" name="placa" id="placa" class="form-control" value="<?= $_GET['placa'] ?? '' ?>" placeholder="Buscar placa">
                </div>
                <div class="col-md-2">
                    <label for="tarjeta" class="form-label">Tarjeta Propiedad</label>
                    <input type="text" name="tarjeta" id="tarjeta" class="form-control" value="<?= $_GET['tarjeta'] ?? '' ?>" placeholder="Buscar tarjeta">
                </div>
                <div class="col-md-2">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control" value="<?= $_GET['fecha'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <label for="sede" class="form-label">ID Sede</label>
                    <input type="text" name="sede" id="sede" class="form-control" value="<?= $_GET['sede'] ?? '' ?>" placeholder="ID Sede">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <a href="VehiculoLista.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Vehículos</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>QR</th>
                        <th>Tipo Vehículo</th>
                        <th>Placa</th>
                        <th>Descripción</th>
                        <th>Tarjeta Propiedad</th>
                        <th>Fecha Parqueadero</th>
                        <th>ID Sede</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <tr id="fila-<?php echo $row['IdParqueadero']; ?>">
                                <td><?php echo $row['IdParqueadero']; ?></td>
                                <td class="text-center">
                                    <?php if (!empty($row['QrVehiculo'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="verQRVehiculo('<?php echo htmlspecialchars($row['QrVehiculo']); ?>', <?php echo $row['IdParqueadero']; ?>)"
                                                title="Ver código QR">
                                            <i class="fas fa-qrcode me-1"></i> Ver QR
                                        </button>
                                    <?php else : ?>
                                        <span class="badge badge-warning">Sin QR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['TipoVehiculo']; ?></td>
                                <td><?php echo $row['PlacaVehiculo']; ?></td>
                                <td><?php echo $row['DescripcionVehiculo']; ?></td>
                                <td><?php echo $row['TarjetaPropiedad']; ?></td>
                                <td><?php echo $row['FechaParqueadero']; ?></td>
                                <td><?php echo $row['IdSede']; ?></td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick='cargarDatosEdicionVehiculo(<?php echo json_encode($row); ?>)'
                                            title="Editar vehículo" data-toggle="modal" data-target="#modalEditarVehiculo">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmarEliminacionVehiculo(<?php echo $row['IdParqueadero']; ?>)"
                                            title="Eliminar vehículo">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No hay vehículos registrados con los filtros seleccionados</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para visualizar QR de Vehículo -->
<div class="modal fade" id="modalVerQRVehiculo" tabindex="-1" role="dialog" aria-labelledby="modalVerQRVehiculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalVerQRVehiculoLabel">
                    <i class="fas fa-qrcode me-2"></i>Código QR - Vehículo #<span id="qrVehiculoId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImagenVehiculo" src="" alt="Código QR Vehículo" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; padding: 10px; border-radius: 5px;">
                <p class="text-muted mt-3">Escanea este código con tu dispositivo móvil</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a id="btnDescargarQRVehiculo" href="#" class="btn btn-success" download>
                    <i class="fas fa-download me-1"></i> Descargar QR
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Vehículo -->
<div class="modal fade" id="modalEditarVehiculo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Vehículo</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditarVehiculo">
                    <input type="hidden" id="editIdVehiculo" name="id">
                    <input type="hidden" id="editAccion" name="accion" value="actualizar">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo Vehículo</label>
                            <select id="editTipoVehiculo" class="form-control" name="tipo" required>
                                <option value="">-- Seleccione un tipo --</option>
                                <option value="Bicicleta">Bicicleta</option>
                                <option value="Moto">Moto</option>
                                <option value="Carro">Carro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Placa</label>
                            <input type="text" id="editPlacaVehiculoDisabled" class="form-control" disabled>
                            <input type="hidden" id="editPlacaVehiculo" name="placa">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea id="editDescripcionVehiculo" class="form-control" name="descripcion" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tarjeta Propiedad</label>
                            <input type="text" id="editTarjetaPropiedadDisabled" class="form-control" disabled>
                            <input type="hidden" id="editTarjetaPropiedad" name="tarjeta">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Parqueadero</label>
                            <input type="datetime-local" id="editFechaParqueaderoDisabled" class="form-control" disabled>
                            <input type="hidden" id="editFechaParqueadero" name="fecha">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ID Sede</label>
                        <input type="number" id="editIdSede" class="form-control" name="idsede" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnGuardarCambiosVehiculo">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="confirmarEliminarModalVehiculo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este vehículo? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarVehiculo">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../js/javascript/js/ValidacionParqueadero.js"></script>

<script>
let vehiculoIdAEliminar = null;

// ✅ Función para mostrar QR del vehículo
function verQRVehiculo(rutaQR, idVehiculo) {
    const rutaCompleta = '../qr' + rutaQR;
    
    $('#qrVehiculoId').text(idVehiculo);
    $('#qrImagenVehiculo').attr('src', rutaCompleta);
    $('#btnDescargarQRVehiculo').attr('href', rutaCompleta).attr('download', 'QR-Vehiculo-' + idVehiculo + '.png');
    
    $('#modalVerQRVehiculo').modal('show');
}

// Cargar datos en el modal de edición
function cargarDatosEdicionVehiculo(row) {
    $('#editIdVehiculo').val(row.IdParqueadero);
    $('#editTipoVehiculo').val(row.TipoVehiculo);
    $('#editDescripcionVehiculo').val(row.DescripcionVehiculo);
    $('#editIdSede').val(row.IdSede);

    $('#editPlacaVehiculoDisabled').val(row.PlacaVehiculo);
    $('#editPlacaVehiculo').val(row.PlacaVehiculo);

    $('#editTarjetaPropiedadDisabled').val(row.TarjetaPropiedad);
    $('#editTarjetaPropiedad').val(row.TarjetaPropiedad);

    let fechaHora = row.FechaParqueadero;
    if (fechaHora) {
        fechaHora = fechaHora.replace(' ', 'T').substring(0, 16);
    }
    $('#editFechaParqueaderoDisabled').val(fechaHora);
    $('#editFechaParqueadero').val(fechaHora);
}

// Confirmar eliminación
function confirmarEliminacionVehiculo(id) {
    vehiculoIdAEliminar = id;
    $('#confirmarEliminarModalVehiculo').modal('show');
}

// Botón confirmar eliminación
$('#btnConfirmarEliminarVehiculo').click(function() {
    if (!vehiculoIdAEliminar) return;

    $.ajax({
        url: '../Controller/parqueadero_dispositivo/ControladorParqueadero.php',
        type: 'POST',
        data: {
            accion: 'eliminar',
            id: vehiculoIdAEliminar
        },
        dataType: 'json',
        success: function(response) {
            $('#confirmarEliminarModalVehiculo').modal('hide');
            if (response.success) {
                alert('Vehículo eliminado correctamente');
                $('#fila-' + vehiculoIdAEliminar).fadeOut(400, function() {
                    $(this).remove();
                });
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#confirmarEliminarModalVehiculo').modal('hide');
            alert('Error al intentar eliminar el vehículo');
        }
    });
});

// Botón guardar cambios
$('#btnGuardarCambiosVehiculo').click(function() {
    const formData = {
        accion: 'actualizar',
        id: $('#editIdVehiculo').val(),
        tipo: $('#editTipoVehiculo').val(),
        descripcion: $('#editDescripcionVehiculo').val(),
        idsede: $('#editIdSede').val()
    };

    // Validar campos
    if (!formData.tipo || !formData.descripcion || !formData.idsede) {
        alert('Complete todos los campos obligatorios');
        return;
    }

    $.ajax({
        url: '../Controller/parqueadero_dispositivo/ControladorParqueadero.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            $('#modalEditarVehiculo').modal('hide');
            if (response.success) {
                alert('Vehículo actualizado correctamente');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#modalEditarVehiculo').modal('hide');
            alert('Error al intentar actualizar el vehículo');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>