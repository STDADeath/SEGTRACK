
<?php require_once __DIR__ . '/../layouts/parte_superior_supervisor.php'; ?>
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
if (!empty($_GET['estado'])) {
    $filtros[] = "Estado = :estado";
    $params[':estado'] = $_GET['estado'];
}

$where = "";
if (count($filtros) > 0) {
    $where = "WHERE " . implode(" AND ", $filtros);
}

$sql = "SELECT * FROM Parqueadero $where ORDER BY 
        CASE WHEN Estado = 'Activo' THEN 1 ELSE 2 END, 
        IdParqueadero DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-car me-2"></i>Vehículos Registrados</h1>
        <a href="../models/Parqueadero.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
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
                <div class="col-md-1">
                    <label for="sede" class="form-label">ID Sede</label>
                    <input type="text" name="sede" id="sede" class="form-control" value="<?= $_GET['sede'] ?? '' ?>" placeholder="Sede">
                </div>
                <div class="col-md-1">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="Activo" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Activo') ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                    </select>
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
                        <th>Tipo Vehículo</th>
                        <th>Placa</th>
                        <th>Descripción</th>
                        <th>Tarjeta Propiedad</th>
                        <th>Fecha Parqueadero</th>
                        <th>ID Sede</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <tr id="fila-<?php echo $row['IdParqueadero']; ?>" class="<?php echo $row['Estado'] === 'Inactivo' ? 'fila-inactiva' : ''; ?>">
                                <td><?php echo $row['IdParqueadero']; ?></td>
                                <td><?php echo $row['TipoVehiculo']; ?></td>
                                <td><?php echo $row['PlacaVehiculo']; ?></td>
                                <td><?php echo $row['DescripcionVehiculo']; ?></td>
                                <td><?php echo $row['TarjetaPropiedad']; ?></td>
                                <td><?php echo $row['FechaParqueadero']; ?></td>
                                <td><?php echo $row['IdSede']; ?></td>
                                <td class="text-center">
                                    <?php if ($row['Estado'] === 'Activo') : ?>
                                        <span class="badge badge-success badge-estado">Activo</span>
                                    <?php else : ?>
                                        <span class="badge badge-secondary badge-estado">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                        onclick='cargarDatosEdicionVehiculo(<?php echo json_encode($row); ?>)'
                                        title="Editar vehículo" data-toggle="modal" data-target="#modalEditarVehiculo">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm <?php echo $row['Estado'] === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success'; ?>" 
                                            onclick="confirmarCambioEstadoVehiculo(<?php echo $row['IdParqueadero']; ?>, '<?php echo $row['Estado']; ?>')"
                                            title="<?php echo $row['Estado'] === 'Activo' ? 'Desactivar' : 'Activar'; ?> vehículo">
                                        <i class="fas <?php echo $row['Estado'] === 'Activo' ? 'fa-lock' : 'fa-lock-open'; ?>"></i>
                                    </button>
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

<!-- Modal Confirmar Cambio de Estado -->
<div class="modal fade" id="modalCambiarEstadoVehiculo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" id="headerCambioEstadoVehiculo">
                <h5 class="modal-title" id="tituloCambioEstadoVehiculo"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="toggle-container">
                    <label class="btn-lock" id="toggleEstadoVisualVehiculo">
                        <svg width="36" height="40" viewBox="0 0 36 40">
                            <path class="lockb" d="M27 27C27 34.1797 21.1797 40 14 40C6.8203 40 1 34.1797 1 27C1 19.8203 6.8203 14 14 14C21.1797 14 27 19.8203 27 27ZM15.6298 26.5191C16.4544 25.9845 17 25.056 17 24C17 22.3431 15.6569 21 14 21C12.3431 21 11 22.3431 11 24C11 25.056 11.5456 25.9845 12.3702 26.5191L11 32H17L15.6298 26.5191Z"></path>
                            <path class="lock" d="M6 21V10C6 5.58172 9.58172 2 14 2V2C18.4183 2 22 5.58172 22 10V21"></path>
                            <path class="bling" d="M29 20L31 22"></path>
                            <path class="bling" d="M31.5 15H34.5"></path>
                            <path class="bling" d="M29 10L31 8"></path>
                        </svg>
                    </label>
                </div>
                <p id="mensajeCambioEstadoVehiculo"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarCambioEstadoVehiculo">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
let vehiculoIdAEditar = null;
let vehiculoACambiarEstado = null;
let estadoActualVehiculo = null;

// Cargar datos en el modal de edición
function cargarDatosEdicionVehiculo(row) {
    vehiculoIdAEditar = row.IdParqueadero;
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

// Confirmar cambio de estado
function confirmarCambioEstadoVehiculo(id, estado) {
    vehiculoACambiarEstado = id;
    estadoActualVehiculo = estado;
    
    const nuevoEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
    const accion = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
    const colorHeader = nuevoEstado === 'Activo' ? 'bg-success' : 'bg-warning';
    
    // Configurar el toggle visual
    const toggleLabel = document.getElementById('toggleEstadoVisualVehiculo');
    
    if (nuevoEstado === 'Activo') {
        toggleLabel.classList.add('activo');
    } else {
        toggleLabel.classList.remove('activo');
    }
    
    $('#headerCambioEstadoVehiculo').removeClass('bg-success bg-warning').addClass(colorHeader + ' text-white');
    $('#tituloCambioEstadoVehiculo').html(`<i class="fas fa-${nuevoEstado === 'Activo' ? 'lock-open' : 'lock'} me-2"></i>${accion.charAt(0).toUpperCase() + accion.slice(1)} Vehículo`);
    $('#mensajeCambioEstadoVehiculo').html(`¿Está seguro que desea <strong>${accion}</strong> este vehículo?`);
    
    $('#modalCambiarEstadoVehiculo').modal('show');
}

// Confirmar cambio de estado
$('#btnConfirmarCambioEstadoVehiculo').click(function() {
    if (!vehiculoACambiarEstado) return;
    
    const nuevoEstado = estadoActualVehiculo === 'Activo' ? 'Inactivo' : 'Activo';
    
    $.ajax({
        url: '../Controller/parqueadero_dispositivo/ControladorParqueadero.php',
        type: 'POST',
        data: {
            accion: 'cambiar_estado',
            id: vehiculoACambiarEstado,
            estado: nuevoEstado
        },
        dataType: 'json',
        success: function(response) {
            $('#modalCambiarEstadoVehiculo').modal('hide');
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#modalCambiarEstadoVehiculo').modal('hide');
            alert('Error al intentar cambiar el estado del vehículo');
        }
    });
});

// Botón guardar cambios
$('#btnGuardarCambiosVehiculo').click(function() {
    const formData = {
        accion: 'actualizar',
        id: $('#editIdVehiculo').val(),
        tipo: $('#editTipoVehiculo').val(),
        placa: $('#editPlacaVehiculo').val(),
        descripcion: $('#editDescripcionVehiculo').val(),
        tarjeta: $('#editTarjetaPropiedad').val(),
        fecha: $('#editFechaParqueadero').val(),
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

<script src="../js/javascript/js/ValidacionParqueadero.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_supervisor.php'; ?>
