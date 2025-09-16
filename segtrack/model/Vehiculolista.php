<?php require_once __DIR__ . '/../models/parte_superior.php'; ?>
<?php require_once "../backed/conexion.php"; ?>

<?php
$conexion = new Conexion();
$conn = $conexion->getConexion();

$sql = "SELECT * FROM Parqueadero ORDER BY IdParqueadero DESC";
$result = $conn->query($sql);
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-car me-2"></i>Vehículos Registrados</h1>
        <a href="../models/Parqueadero.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Vehículo
        </a>
    </div>

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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr id="fila-<?php echo $row['IdParqueadero']; ?>">
                                <td><?php echo $row['IdParqueadero']; ?></td>
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
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">⚠ No hay vehículos registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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
<script>
let vehiculoIdAEliminar = null;

function cargarDatosEdicionVehiculo(row){
    $('#editIdVehiculo').val(row.IdParqueadero);
    $('#editTipoVehiculo').val(row.tipoVehiculo);
    $('#editDescripcionVehiculo').val(row.DescripcionVehiculo);
    $('#editIdSede').val(row.IdSede);

    $('#editPlacaVehiculoDisabled').val(row.PlacaVehiculo);
    $('#editPlacaVehiculo').val(row.PlacaVehiculo);

    $('#editTarjetaPropiedadDisabled').val(row.TarjetaPropiedad);
    $('#editTarjetaPropiedad').val(row.TarjetaPropiedad);

    let fechaHora = row.FechaParqueadero;
    if(fechaHora){
        fechaHora = fechaHora.replace(' ', 'T').substring(0,16);
    }
    $('#editFechaParqueaderoDisabled').val(fechaHora);
    $('#editFechaParqueadero').val(fechaHora);
}

function confirmarEliminacionVehiculo(id){
    vehiculoIdAEliminar = id;
    $('#confirmarEliminarModalVehiculo').modal('show');
}

$('#btnConfirmarEliminarVehiculo').click(function(){
    if(!vehiculoIdAEliminar) return;

    $.ajax({
        url: '../backed/EliminarVehiculo.php',
        type: 'POST',
        data: { id: vehiculoIdAEliminar },
        dataType: 'json',
        success: function(response){
            $('#confirmarEliminarModalVehiculo').modal('hide');
            if(response.success){
                alert('Vehículo eliminado correctamente');
                $('#fila-' + vehiculoIdAEliminar).remove();
                if($('tbody tr').length === 1) location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(){
            $('#confirmarEliminarModalVehiculo').modal('hide');
            alert('Error al intentar eliminar el vehículo');
        }
    });
});

$('#btnGuardarCambiosVehiculo').click(function(){
    const formData = {
        id: $('#editIdVehiculo').val(),
        tipo: $('#editTipoVehiculo').val(),
        placa: $('#editPlacaVehiculo').val(),
        descripcion: $('#editDescripcionVehiculo').val(),
        tarjeta: $('#editTarjetaPropiedad').val(),
        fecha: $('#editFechaParqueadero').val(),
        idsede: $('#editIdSede').val()
    };

    for(let key in formData){
        if(!formData[key] || formData[key].toString().trim() === ''){
            alert('Complete todos los campos obligatorios');
            return;
        }
    }

    $.ajax({
        url: '../backed/ActualizarVehiculo.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response){
            $('#modalEditarVehiculo').modal('hide');
            if(response.success){
                alert('Vehículo actualizado correctamente');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(){
            $('#modalEditarVehiculo').modal('hide');
            alert('Error al intentar actualizar el vehículo');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../models/parte_inferior.php'; ?>
