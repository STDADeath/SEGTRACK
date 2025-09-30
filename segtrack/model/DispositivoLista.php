<?php require_once __DIR__ . '/../model/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Dispositivos Registrados</h1>
        <a href="../model/Dispositivos.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Dispositivo
        </a>
    </div>

    <?php
    require_once "../Controller/Conexion/conexion.php";
    $conexion = new Conexion();
    $conn = $conexion->getConexion();
    $sql = "SELECT * FROM Dispositivo ORDER BY IdDispositivo DESC";
    $result = $conn->query($sql);
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Dispositivos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>QR Dispositivo</th>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>ID Funcionario</th>
                            <th>ID Visitante</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0) : ?>
                            <?php while ($row = $result->fetch_assoc()) : ?>
                                <tr id="fila-<?php echo $row['IdDispositivo']; ?>">
                                    <td><?php echo $row['IdDispositivo']; ?></td>
                                    <td><?php echo $row['QrDispositivo']; ?></td>
                                    <td><?php echo $row['TipoDispositivo']; ?></td>
                                    <td><?php echo $row['MarcaDispositivo']; ?></td>
                                    <td><?php echo $row['IdFuncionario']; ?></td>
                                    <td><?php echo $row['IdVisitante']; ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="cargarDatosEdicion(<?php echo $row['IdDispositivo']; ?>, '<?php echo $row['QrDispositivo']; ?>', '<?php echo $row['TipoDispositivo']; ?>', '<?php echo $row['MarcaDispositivo']; ?>', <?php echo $row['IdFuncionario']; ?>, <?php echo $row['IdVisitante']; ?>)"
                                                    title="Editar dispositivo" data-toggle="modal" data-target="#modalEditar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmarEliminacion(<?php echo $row['IdDispositivo']; ?>)"
                                                    title="Eliminar dispositivo">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay dispositivos registrados</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog" aria-labelledby="confirmarEliminarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este dispositivo? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarLabel">Editar Dispositivo</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="editId" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editQr" class="form-label">QR Dispositivo</label>
                            <input type="text" id="editQr" class="form-control" name="qr" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editTipo" class="form-label">Tipo de Dispositivo</label>
                            <select id="editTipo" class="form-control" name="tipo" required>
                                <option value="">-- Seleccione un tipo --</option>
                                <option value="Computador">Computador</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Portátil">Portátil</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editMarca" class="form-label">Marca</label>
                            <input type="text" id="editMarca" class="form-control" name="marca" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editFuncionario" class="form-label">ID Funcionario</label>
                            <input type="number" id="editFuncionario" class="form-control" name="id_funcionario" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editVisitante" class="form-label">ID Visitante</label>
                            <input type="number" id="editVisitante" class="form-control" name="id_visitante" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCambios">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/javascript/js/sb-admin-2.min.js"></script>

<script>
let dispositivoIdAEliminar = null;
let dispositivoIdAEditar = null;

function cargarDatosEdicion(id, qr, tipo, marca, idFuncionario, idVisitante) {
    dispositivoIdAEditar = id;
    $('#editId').val(id);
    $('#editQr').val(qr);
    $('#editTipo').val(tipo);
    $('#editMarca').val(marca);
    $('#editFuncionario').val(idFuncionario);
    $('#editVisitante').val(idVisitante);
}

function confirmarEliminacion(id) {
    dispositivoIdAEliminar = id;
    $('#confirmarEliminarModal').modal('show');
}

function eliminarDispositivo() {
    if (dispositivoIdAEliminar) {
        $.ajax({
            url: '../Controller/parqueadero_dispositivo/ControladorDispositivo.php',
            type: 'POST',
            data: { id: dispositivoIdAEliminar },
            dataType: 'json',
            success: function(response) {
                $('#confirmarEliminarModal').modal('hide');
                if (response.success) {
                    alert('Dispositivo eliminado correctamente');
                    $('#fila-' + dispositivoIdAEliminar).remove();
                    if ($('tbody tr').length === 1) {
                        location.reload();
                    }
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                $('#confirmarEliminarModal').modal('hide');
                alert('Error al intentar eliminar el dispositivo');
            }
        });
    }
}

$('#btnGuardarCambios').click(function() {
    const formData = {
        id: $('#editId').val(),
        qr: $('#editQr').val(),
        tipo: $('#editTipo').val(),
        marca: $('#editMarca').val(),
        id_funcionario: $('#editFuncionario').val(),
        id_visitante: $('#editVisitante').val()
    };
    if (!formData.qr || !formData.tipo || !formData.marca) {
        alert('Por favor, complete todos los campos obligatorios');
        return;
    }
    $.ajax({
        url: '../Controller/parqueadero_dispositivo/ControladorDispositivo.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            $('#modalEditar').modal('hide');
            if (response.success) {
                alert('Dispositivo actualizado correctamente');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#modalEditar').modal('hide');
            alert('Error al intentar actualizar el dispositivo');
        }
    });
});

document.getElementById('btnConfirmarEliminar').addEventListener('click', eliminarDispositivo);
</script>

<?php require_once __DIR__ . '/../model/parte_inferior.php'; ?>
