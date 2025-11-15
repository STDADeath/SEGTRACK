<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tie me-2"></i>Funcionarios Registrados</h1>
        <a href="../View/Funcionario.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Funcionario
        </a>
    </div>

    <?php
    require_once __DIR__ . "../../../Core/conexion.php";
    $conexionObj = new Conexion();
    $conn = $conexionObj->getConexion();

    // Filtros dinámicos
    $filtros = [];
    $params = [];

    if (!empty($_GET['cargo'])) {
        $filtros[] = "CargoFuncionario LIKE :cargo";
        $params[':cargo'] = '%' . $_GET['cargo'] . '%';
    }
    if (!empty($_GET['nombre'])) {
        $filtros[] = "NombreFuncionario LIKE :nombre";
        $params[':nombre'] = '%' . $_GET['nombre'] . '%';
    }
    if (!empty($_GET['sede'])) {
        $filtros[] = "IdSede = :sede";
        $params[':sede'] = $_GET['sede'];
    }

    $where = "";
    if (count($filtros) > 0) {
        $where = "WHERE " . implode(" AND ", $filtros);
    }

    $sql = "SELECT * FROM funcionario $where ORDER BY IdFuncionario DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- 🔍 Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Funcionarios</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="cargo" class="form-label">Cargo</label>
                    <input type="text" name="cargo" id="cargo" class="form-control" value="<?= $_GET['cargo'] ?? '' ?>" placeholder="Buscar por cargo">
                </div>
                <div class="col-md-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" value="<?= $_GET['nombre'] ?? '' ?>" placeholder="Buscar por nombre">
                </div>
                <div class="col-md-3">
                    <label for="sede" class="form-label">ID Sede</label>
                    <input type="number" name="sede" id="sede" class="form-control" value="<?= $_GET['sede'] ?? '' ?>" placeholder="ID de sede">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <a href="FuncionarioLista.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- 📋 Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Funcionarios</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>QR</th>
                            <th>Cargo</th>
                            <th>Nombre</th>
                            <th>Sede</th>
                            <th>Teléfono</th>
                            <th>Documento</th>
                            <th>Correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && count($result) > 0) : ?>
                            <?php foreach ($result as $row) : ?>
                                <tr id="fila-<?php echo $row['IdFuncionario']; ?>">
                                    <td><?php echo $row['IdFuncionario']; ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['QrCodigoFuncionario'])) : ?>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="verQR('<?php echo htmlspecialchars($row['QrCodigoFuncionario']); ?>', <?php echo $row['IdFuncionario']; ?>)"
                                                    title="Ver código QR">
                                                <i class="fas fa-qrcode me-1"></i> Ver QR
                                            </button>
                                        <?php else : ?>
                                            <span class="badge bg-warning text-dark">Sin QR</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['CargoFuncionario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['NombreFuncionario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['IdSede']); ?></td>
                                    <td><?php echo htmlspecialchars($row['TelefonoFuncionario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['DocumentoFuncionario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['CorreoFuncionario']); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="cargarDatosEdicion(
                                                    <?php echo $row['IdFuncionario']; ?>,
                                                    '<?php echo htmlspecialchars($row['CargoFuncionario']); ?>',
                                                    '<?php echo htmlspecialchars($row['NombreFuncionario']); ?>',
                                                    '<?php echo htmlspecialchars($row['IdSede']); ?>',
                                                    '<?php echo htmlspecialchars($row['TelefonoFuncionario']); ?>',
                                                    '<?php echo htmlspecialchars($row['DocumentoFuncionario']); ?>',
                                                    '<?php echo htmlspecialchars($row['CorreoFuncionario']); ?>')"
                                                title="Editar funcionario"
                                                data-toggle="modal" data-target="#modalEditar">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay funcionarios registrados con los filtros seleccionados</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Modal para visualizar QR -->
<div class="modal fade" id="modalVerQR" tabindex="-1" role="dialog" aria-labelledby="modalVerQRLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalVerQRLabel">
                    <i class="fas fa-qrcode me-2"></i>Código QR - Funcionario #<span id="qrFuncionarioId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImagen" src="" alt="Código QR" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; padding: 10px; border-radius: 5px;">
                <p class="text-muted mt-3">Escanea este código con tu dispositivo móvil</p>
                <small class="text-muted d-block mt-2" id="rutaDebug"></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a id="btnDescargarQR" href="#" class="btn btn-success" download>
                    <i class="fas fa-download me-1"></i> Descargar QR
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 🧩 Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarLabel">Editar Funcionario</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="editId" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCargo" class="form-label">Cargo</label>
                            <input type="text" id="editCargo" class="form-control" name="CargoFuncionario" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editNombre" class="form-label">Nombre</label>
                            <input type="text" id="editNombre" class="form-control" name="NombreFuncionario" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="editSede" class="form-label">Sede</label>
                            <input type="number" id="editSede" class="form-control" name="IdSede" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editTelefono" class="form-label">Teléfono</label>
                            <input type="text" id="editTelefono" class="form-control" name="TelefonoFuncionario">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editDocumento" class="form-label">Documento</label>
                            <input type="text" id="editDocumento" class="form-control" name="DocumentoFuncionario" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editCorreo" class="form-label">Correo</label>
                        <input type="email" id="editCorreo" class="form-control" name="CorreoFuncionario" required>
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
<script src="../js/javascript/demo/sb-admin-2.min.js"></script>

<script>
let funcionarioIdEditar = null;

// 🔥 FUNCIÓN CORREGIDA PARA VER QR
function verQR(nombreArchivo, idFuncionario) {
    console.log("Nombre archivo recibido:", nombreArchivo);
    
    // Construir la ruta correcta desde View hacia qr
    const rutaCompleta = '../qr/' + nombreArchivo;
    
    console.log("Ruta completa construida:", rutaCompleta);
    
    $('#qrFuncionarioId').text(idFuncionario);
    $('#qrImagen').attr('src', rutaCompleta);
    $('#rutaDebug').text('Ruta: ' + rutaCompleta);
    $('#btnDescargarQR').attr('href', rutaCompleta).attr('download', 'QR-Funcionario-' + idFuncionario + '.png');
    
    // Verificar si la imagen se carga correctamente
    $('#qrImagen').on('error', function() {
        console.error("Error al cargar la imagen desde:", rutaCompleta);
        alert("No se pudo cargar la imagen QR. Verifica la ruta: " + rutaCompleta);
    });
    
    $('#qrImagen').on('load', function() {
        console.log("Imagen cargada exitosamente");
    });
    
    $('#modalVerQR').modal('show');
}

// ✅ Cargar datos en modal
function cargarDatosEdicion(id, cargo, nombre, sede, telefono, documento, correo) {
    funcionarioIdEditar = id;
    $('#editId').val(id);
    $('#editCargo').val(cargo);
    $('#editNombre').val(nombre);
    $('#editSede').val(sede);
    $('#editTelefono').val(telefono);
    $('#editDocumento').val(documento);
    $('#editCorreo').val(correo);
}

// ✅ Guardar cambios
$('#btnGuardarCambios').click(function() {
    const formData = {
        accion: 'actualizar',
        id: $('#editId').val(),
        CargoFuncionario: $('#editCargo').val(),
        NombreFuncionario: $('#editNombre').val(),
        IdSede: $('#editSede').val(),
        TelefonoFuncionario: $('#editTelefono').val(),
        DocumentoFuncionario: $('#editDocumento').val(),
        CorreoFuncionario: $('#editCorreo').val()
    };

    $.ajax({
        url: '../Controller/sede_institucion_funcionario_usuario/ControladorFuncionarios.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            $('#modalEditar').modal('hide');
            if (response.success) {
                alert('Funcionario actualizado correctamente');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#modalEditar').modal('hide');
            alert('Error al intentar actualizar el funcionario');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>