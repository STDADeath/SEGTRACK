<?php require_once __DIR__ . '/../layouts/parte_superior_Administrador.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tie me-2"></i>Funcionarios Registrados</h1>
        <a href="./Funcionario.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Funcionario
        </a>
    </div>

    <?php
    require_once __DIR__ . "../../../Core/conexion.php";
    $conexionObj = new Conexion();
    $conn = $conexionObj->getConexion();

    // ============================
    // CARGAR SEDES PARA EL SELECT
    // ============================
    $sqlSede = "SELECT IdSede, TipoSede FROM sede ORDER BY TipoSede ASC";
    $stmtSede = $conn->prepare($sqlSede);
    $stmtSede->execute();
    $sedes = $stmtSede->fetchAll(PDO::FETCH_ASSOC);

    // Mapa id => nombre para mostrar en la tabla
    $mapSedes = [];
    foreach ($sedes as $s) {
        $mapSedes[$s['IdSede']] = $s['TipoSede'];
    }

    // ============================
    // Construcción de filtros dinámicos
    // ============================
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
    if (!empty($_GET['estado'])) {
        $filtros[] = "Estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }
    if (!empty($_GET['documento'])) {
        $filtros[] = "DocumentoFuncionario LIKE :documento";
        $params[':documento'] = '%' . $_GET['documento'] . '%';
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

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Funcionarios</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <!-- Filtro por Cargo -->
                <div class="col-md-3">
                    <label for="cargo" class="form-label">Cargo</label>
                    <input type="text" name="cargo" id="cargo" class="form-control" 
                           value="<?= $_GET['cargo'] ?? '' ?>" placeholder="Buscar por cargo">
                </div>
                <!-- Filtro por Nombre -->
                <div class="col-md-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" 
                           value="<?= $_GET['nombre'] ?? '' ?>" placeholder="Buscar por nombre">
                </div>
                <!-- Filtro por Estado -->
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="Activo" <?= (isset($_GET['estado']) && $_GET['estado'] === 'Activo') ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= (isset($_GET['estado']) && $_GET['estado'] === 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <!-- Filtro por Documento -->
                <div class="col-md-2">
                    <label for="documento" class="form-label">Documento</label>
                    <input type="text" name="documento" id="documento" class="form-control" 
                           value="<?= $_GET['documento'] ?? '' ?>" placeholder="Número">
                </div>
                <!-- Botones de acción -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <a href="FuncionarioLista.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Funcionarios</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>QR</th>
                            <th>Cargo</th>
                            <th>Nombre</th>
                            <th>Sede</th>
                            <th>Teléfono</th>
                            <th>Documento</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && count($result) > 0) : ?>
                            <?php foreach ($result as $row) : ?>

                                <tr id="fila-<?php echo $row['IdFuncionario']; ?>">

                                    <!-- QR -->
                                    <td class="text-center">
                                        <?php if ($row['QrCodigoFuncionario']) : ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="verQR('<?php echo htmlspecialchars($row['QrCodigoFuncionario']); ?>', <?php echo $row['IdFuncionario']; ?>)">
                                                <i class="fas fa-qrcode"></i> Ver QR
                                            </button>
                                        <?php else : ?>
                                            <span class="badge bg-warning">Sin QR</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($row['CargoFuncionario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['NombreFuncionario']); ?></td>

                                    <!-- Mostrar el NOMBRE de la sede usando el mapa -->
                                    <td><?php echo htmlspecialchars($mapSedes[$row['IdSede']] ?? 'Sin Sede'); ?></td>

                                    <td><?php echo htmlspecialchars($row['TelefonoFuncionario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['DocumentoFuncionario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['CorreoFuncionario']); ?></td>

                                    <!-- ESTADO -->
                                    <td class="text-center">
                                        <?php if ($row['Estado'] === 'Activo') : ?>
                                            <span class="badge bg-success" id="badge-estado-<?php echo $row['IdFuncionario']; ?>">Activo</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger" id="badge-estado-<?php echo $row['IdFuncionario']; ?>">Inactivo</span>
                                        <?php endif; ?>
                                        
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-outline-secondary mt-1"
                                            onclick="cambiarEstado(<?php echo $row['IdFuncionario']; ?>, '<?php echo $row['Estado']; ?>')"
                                            title="Cambiar estado">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </td>

                                    <td class="text-center">
                                        <!-- Botón para editar funcionario -->
                                        <button type="button" 
                                            class="btn btn-sm btn-outline-primary"
                                            onclick="cargarDatosEdicion(
                                                <?php echo $row['IdFuncionario']; ?>, 
                                                '<?php echo htmlspecialchars(addslashes($row['CargoFuncionario'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($row['NombreFuncionario'])); ?>', 
                                                <?php echo (int)$row['IdSede']; ?>, 
                                                '<?php echo htmlspecialchars(addslashes($row['TelefonoFuncionario'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($row['DocumentoFuncionario'])); ?>', 
                                                '<?php echo htmlspecialchars(addslashes($row['CorreoFuncionario'])); ?>'
                                            )"
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
                                    <p class="text-muted">No hay funcionarios registrados</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal QR -->
<div class="modal fade" id="modalVerQR" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Código QR - Funcionario #<span id="qrFuncionarioId"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImagen" src="" class="img-fluid" style="max-width:300px;">
            </div>
            <div class="modal-footer">
                <a id="btnDescargarQR" class="btn btn-success" download>Descargar</a>
                <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Funcionario</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">

                <form id="formEditar">
                    <!-- Campo oculto para almacenar el ID del funcionario que se está editando -->
                    <input type="hidden" id="editId">

                    <div class="row">
                        <!-- Campo de selección de Cargo -->
                        <div class="col-md-6 mb-3">
                            <label for="editCargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <select id="editCargo" class="form-control" required>
                                <option value="">Seleccione un cargo</option>
                                <option value="Personal Seguridad">Personal Seguridad</option>
                                <option value="Funcionario">Funcionario</option>
                            </select>
                        </div>

                        <!-- Campo de Nombre -->
                        <div class="col-md-6 mb-3">
                            <label for="editNombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" id="editNombre" class="form-control" placeholder="Ingrese el nombre completo" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Campo de Sede: SELECT con nombres (no número) -->
                        <div class="col-md-4 mb-3">
                            <label for="editSede" class="form-label">Sede <span class="text-danger">*</span></label>
                            <select id="editSede" class="form-control" required>
                                <option value="">Seleccione una sede</option>
                                <?php foreach ($sedes as $s) : ?>
                                    <option value="<?= $s['IdSede']; ?>"><?= htmlspecialchars($s['TipoSede']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Campo de Teléfono -->
                        <div class="col-md-4 mb-3">
                            <label for="editTelefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" id="editTelefono" class="form-control" placeholder="Ej: 3001234567" required>
                        </div>

                        <!-- Campo de Documento -->
                        <div class="col-md-4 mb-3">
                            <label for="editDocumento" class="form-label">Documento <span class="text-danger">*</span></label>
                            <input type="text" id="editDocumento" class="form-control" placeholder="Ej: 1234567890" required>
                        </div>
                    </div>

                    <!-- Campo de Correo Electrónico -->
                    <div class="mb-3">
                        <label for="editCorreo" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" id="editCorreo" class="form-control" placeholder="ejemplo@correo.com" required>
                    </div>

                    <!-- Nota informativa -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Los campos marcados con <span class="text-danger">*</span> son obligatorios
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <!-- Botón para cancelar y cerrar el modal -->
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <!-- Botón para guardar los cambios -->
                <button type="button" class="btn btn-primary" id="btnGuardarCambios">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery (ya lo tenías) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// ========================================
// FUNCIÓN PARA VER EL CÓDIGO QR
// ========================================
function verQR(rutaQR, idFuncionario) {
    const rutaCompleta = '../../../Public/' + rutaQR;
    $('#qrFuncionarioId').text(idFuncionario);
    $('#qrImagen').attr('src', rutaCompleta);
    $('#btnDescargarQR').attr('href', rutaCompleta);
    $('#modalVerQR').modal('show');
}

// ========================================
// FUNCIÓN PARA CARGAR DATOS EN EL MODAL DE EDICIÓN
// ========================================
function cargarDatosEdicion(id, cargo, nombre, sede, telefono, documento, correo) {
    $('#editId').val(id);
    $('#editCargo').val(cargo);
    $('#editNombre').val(nombre);
    // Sede es el IdSede: seteamos el select y forzamos .change() para que se aplique
    $('#editSede').val(sede).change();
    $('#editTelefono').val(telefono);
    $('#editDocumento').val(documento);
    $('#editCorreo').val(correo);
    // Abrir modal (por si se llama sin data-toggle)
    $('#modalEditar').modal('show');
}

// ========================================
// FUNCIÓN: CAMBIAR ESTADO DEL FUNCIONARIO
// ========================================
function cambiarEstado(idFuncionario, estadoActual) {
    const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';

    if (!confirm(`¿Está seguro que desea cambiar el estado a "${nuevoEstado}"?`)) {
        return;
    }

    $.ajax({
        url: '../../Controller/ControladorFuncionarios.php',
        type: 'POST',
        data: {
            accion: 'cambiar_estado',
            id: idFuncionario,
            estado: nuevoEstado
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Estado cambiado correctamente');
                const badgeEstado = $('#badge-estado-' + idFuncionario);
                if (nuevoEstado === 'Activo') {
                    badgeEstado.removeClass('bg-danger').addClass('bg-success').text('Activo');
                } else {
                    badgeEstado.removeClass('bg-success').addClass('bg-danger').text('Inactivo');
                }
                const botonCambiar = badgeEstado.siblings('button');
                botonCambiar.attr('onclick', `cambiarEstado(${idFuncionario}, '${nuevoEstado}')`);
            } else {
                alert('Error: ' + (response.message || 'No se pudo cambiar el estado'));
            }
        },
        error: function(xhr, status, error) {
            alert('Error al cambiar el estado. Por favor, intente nuevamente.');
            console.error('Error AJAX:', error);
        }
    });
}

// ========================================
// EVENTO: GUARDAR CAMBIOS DE EDICIÓN CON VALIDACIÓN
// ========================================
$('#btnGuardarCambios').click(function () {

    const id = $('#editId').val();
    const cargo = $('#editCargo').val().trim();
    const nombre = $('#editNombre').val().trim();
    const sede = $('#editSede').val().trim();
    const telefono = $('#editTelefono').val().trim();
    const documento = $('#editDocumento').val().trim();
    const correo = $('#editCorreo').val().trim();

    if (!cargo) { alert('Por favor seleccione un cargo'); $('#editCargo').focus(); return; }
    if (!nombre) { alert('Por favor ingrese el nombre del funcionario'); $('#editNombre').focus(); return; }
    if (!sede) { alert('Por favor ingrese la sede'); $('#editSede').focus(); return; }
    if (!telefono) { alert('Por favor ingrese el teléfono'); $('#editTelefono').focus(); return; }
    if (!documento) { alert('Por favor ingrese el documento'); $('#editDocumento').focus(); return; }
    if (!correo) { alert('Por favor ingrese el correo electrónico'); $('#editCorreo').focus(); return; }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(correo)) { alert('Por favor ingrese un correo electrónico válido'); $('#editCorreo').focus(); return; }

    if (!confirm('¿Está seguro que desea actualizar los datos de este funcionario?')) { return; }

    const formData = {
        accion: "actualizar",
        id: id,
        cargo: cargo,
        nombre: nombre,
        sede: sede,
        telefono: telefono,
        documento: documento,
        correo: correo
    };

    const btnGuardar = $('#btnGuardarCambios');
    const textoOriginal = btnGuardar.html();
    btnGuardar.prop('disabled', true);
    btnGuardar.html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '../../Controller/ControladorFuncionarios.php',
        type: 'POST',
        data: formData,
        dataType: 'json',

        success: function(response) {
            btnGuardar.prop('disabled', false);
            btnGuardar.html(textoOriginal);
            $('#modalEditar').modal('hide');

            if (response.success) {
                // Si la actualización fue exitosa, intentamos solicitar regenerar el QR
                // Si tu controlador implementó la acción 'actualizar_qr', esto regenerará el QR y devolverá la ruta.
                $.post('../../Controller/ControladorFuncionarios.php', { accion: 'actualizar_qr', id: id }, function(resQR) {
                    // resQR puede no existir si no implementaste la acción; por eso validamos.
                    if (resQR && resQR.success) {
                        // opcional: mostrar mensaje o actualizar vista del QR
                        console.log('QR regenerado:', resQR.ruta_qr);
                        alert('Funcionario actualizado correctamente y QR regenerado.');
                        location.reload();
                    } else {
                        // La actualización principal ya fue exitosa; QR no regenerado (o acción no disponible)
                        alert('Funcionario actualizado correctamente.');
                        location.reload();
                    }
                }, 'json').fail(function() {
                    // Si falla el request de actualizar_qr (acción no encontrada u otro error)
                    alert('Funcionario actualizado correctamente.');
                    location.reload();
                });

            } else {
                alert('Error al actualizar: ' + (response.message || 'Error desconocido'));
            }
        },

        error: function(xhr, status, error) {
            btnGuardar.prop('disabled', false);
            btnGuardar.html(textoOriginal);
            alert("Error al actualizar. Por favor, intente nuevamente.");
            console.error('Error AJAX:', error);
            console.error('Respuesta del servidor:', xhr.responseText);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_Administrador.php'; ?>
