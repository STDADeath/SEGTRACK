<?php require_once __DIR__ . '/../models/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tie me-2"></i>Funcionarios Registrados</h1>
        <a href="../vistas/RegistroFuncionario.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Funcionario
        </a>
    </div>

    <?php
    require_once "../backed/conexion.php";

    $conexion = new Conexion();
    $conn = $conexion->getConexion();

    $sql = "SELECT * FROM funcionario ORDER BY IdFuncionario DESC";
    $result = $conn->query($sql);
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Funcionarios</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Cargo</th>
                            <th>ID Sede</th>
                            <th>Código QR</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0) : ?>
                            <?php while ($row = $result->fetch_assoc()) : ?>
                                <tr id="fila-<?php echo $row['IdFuncionario']; ?>">
                                    <td><?php echo $row['IdFuncionario']; ?></td>
                                    <td><?php echo $row['Nombre']; ?></td>
                                    <td><?php echo $row['Documento']; ?></td>
                                    <td><?php echo $row['Telefono']; ?></td>
                                    <td><?php echo $row['Correo']; ?></td>
                                    <td><?php echo $row['Cargo']; ?></td>
                                    <td><?php echo $row['IdSede']; ?></td>
                                    <td><?php echo $row['QrCodigo']; ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="cargarDatosEdicionFuncionario(
                                                        <?php echo $row['IdFuncionario']; ?>,
                                                        '<?php echo $row['Nombre']; ?>',
                                                        '<?php echo $row['Documento']; ?>',
                                                        '<?php echo $row['Telefono']; ?>',
                                                        '<?php echo $row['Correo']; ?>',
                                                        '<?php echo $row['Cargo']; ?>',
                                                        <?php echo $row['IdSede']; ?>,
                                                        '<?php echo $row['QrCodigo']; ?>'
                                                    )"
                                                    title="Editar funcionario" data-toggle="modal" data-target="#modalEditarFuncionario">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmarEliminacionFuncionario(<?php echo $row['IdFuncionario']; ?>)"
                                                    title="Eliminar funcionario">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay registros de funcionarios</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmarEliminarModalFuncionario" tabindex="-1" role="dialog" aria-labelledby="confirmarEliminarLabelFuncionario" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarLabelFuncionario">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este funcionario? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarFuncionario">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarFuncionario" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabelFuncionario" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarLabelFuncionario">Editar Funcionario</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarFuncionario">
                    <input type="hidden" id="editIdFuncionario" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editNombre" class="form-label">Nombre</label>
                            <input type="text" id="editNombre" class="form-control" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editDocumento" class="form-label">Documento</label>
                            <input type="text" id="editDocumento" class="form-control" name="documento" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editTelefono" class="form-label">Teléfono</label>
                            <input type="text" id="editTelefono" class="form-control" name="telefono" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCorreo" class="form-label">Correo</label>
                            <input type="email" id="editCorreo" class="form-control" name="correo" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCargo" class="form-label">Cargo</label>
                            <input type="text" id="editCargo" class="form-control" name="cargo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editSede" class="form-label">ID Sede</label>
                            <input type="number" id="editSede" class="form-control" name="id_sede" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editQr" class="form-label">Código QR</label>
                            <input type="text" id="editQr" class="form-control" name="qr" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCambiosFuncionario">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>

<script>
let funcionarioIdAEliminar = null;

function cargarDatosEdicionFuncionario(id, nombre, documento, telefono, correo, cargo, idSede, qr) {
    $('#editIdFuncionario').val(id);
    $('#editNombre').val(nombre);
    $('#editDocumento').val(documento);
    $('#editTelefono').val(telefono);
    $('#editCorreo').val(correo);
    $('#editCargo').val(cargo);
    $('#editSede').val(idSede);
    $('#editQr').val(qr);
}

function confirmarEliminacionFuncionario(id) {
    funcionarioIdAEliminar = id;
    $('#confirmarEliminarModalFuncionario').modal('show');
}

function eliminarFuncionario() {
    if (funcionarioIdAEliminar) {
        $.ajax({
            url: '../backed/EliminarFuncionario.php',
            type: 'POST',
            data: { id: funcionarioIdAEliminar },
            dataType: 'json',
            success: function(response) {
                $('#confirmarEliminarModalFuncionario').modal('hide');
                if (response.success) {
                    alert('Funcionario eliminado correctamente');
                    $('#fila-' + funcionarioIdAEliminar).remove();
                    if ($('tbody tr').length === 1) { location.reload(); }
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                $('#confirmarEliminarModalFuncionario').modal('hide');
                alert('Error al intentar eliminar el funcionario');
            }
        });
    }
}

$('#btnGuardarCambiosFuncionario').click(function() {
    const formData = {
        id: $('#editIdFuncionario').val(),
        nombre: $('#editNombre').val(),
        documento: $('#editDocumento').val(),
        telefono: $('#editTelefono').val(),
        correo: $('#editCorreo').val(),
        cargo: $('#editCargo').val(),
        id_sede: $('#editSede').val(),
        qr: $('#editQr').val()
    };
    
    if (!formData.nombre || !formData.documento || !formData.telefono || !formData.correo || !formData.cargo) {
        alert('Por favor, complete todos los campos obligatorios');
        return;
    }
    
    $.ajax({
        url: '../backed/ActualizarFuncionario.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            $('#modalEditarFuncionario').modal('hide');
            if (response.success) {
                alert('Funcionario actualizado correctamente');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#modalEditarFuncionario').modal('hide');
            alert('Error al intentar actualizar el funcionario');
        }
    });
});

document.getElementById('btnConfirmarEliminarFuncionario').addEventListener('click', eliminarFuncionario);
</script>

<?php require_once __DIR__ . '/../models/parte_inferior.php'; ?>
