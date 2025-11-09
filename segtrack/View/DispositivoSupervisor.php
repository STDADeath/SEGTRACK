<?php require_once __DIR__ . '/../Plantilla/parte_superior_supervisor.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Dispositivos Registrados</h1>
        <a href="../View/Dispositivos.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Dispositivo
        </a>
    </div>

    <?php
    require_once __DIR__ . "/../Core/conexion.php";
    $conexionObj = new Conexion();
    $conn = $conexionObj->getConexion();

    // Construcción de filtros dinámicos
    $filtros = [];
    $params = [];

    if (!empty($_GET['tipo'])) {
        $filtros[] = "TipoDispositivo = :tipo";
        $params[':tipo'] = $_GET['tipo'];
    }
    if (!empty($_GET['marca'])) {
        $filtros[] = "MarcaDispositivo LIKE :marca";
        $params[':marca'] = '%' . $_GET['marca'] . '%';
    }
    if (!empty($_GET['funcionario'])) {
        $filtros[] = "IdFuncionario = :funcionario";
        $params[':funcionario'] = $_GET['funcionario'];
    }
    if (!empty($_GET['visitante'])) {
        $filtros[] = "IdVisitante = :visitante";
        $params[':visitante'] = $_GET['visitante'];
    }
    if (!empty($_GET['estado'])) {
        $filtros[] = "Estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }

    $where = "";
    if (count($filtros) > 0) {
        $where = "WHERE " . implode(" AND ", $filtros);
    }

    $sql = "SELECT * FROM dispositivo $where ORDER BY 
            CASE WHEN Estado = 'Activo' THEN 1 ELSE 2 END, 
            IdDispositivo DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Filtrar Dispositivos</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-2">
                    <label for="tipo" class="form-label">Tipo de Dispositivo</label>
                    <select name="tipo" id="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Portatil" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Portatil') ? 'selected' : '' ?>>Portátil</option>
                        <option value="Tablet" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Tablet') ? 'selected' : '' ?>>Tablet</option>
                        <option value="Computador" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Computador') ? 'selected' : '' ?>>Computador</option>
                        <option value="Otro" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="marca" class="form-label">Marca</label>
                    <input type="text" name="marca" id="marca" class="form-control" value="<?= $_GET['marca'] ?? '' ?>" placeholder="Buscar por marca">
                </div>
                <div class="col-md-2">
                    <label for="funcionario" class="form-label">ID Funcionario</label>
                    <input type="text" name="funcionario" id="funcionario" class="form-control" value="<?= $_GET['funcionario'] ?? '' ?>" placeholder="ID">
                </div>
                <div class="col-md-2">
                    <label for="visitante" class="form-label">ID Visitante</label>
                    <input type="text" name="visitante" id="visitante" class="form-control" value="<?= $_GET['visitante'] ?? '' ?>" placeholder="ID">
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="Activo" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Activo') ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= (isset($_GET['estado']) && $_GET['estado'] == 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <a href="Dispositivos_lista.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
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
                            <th>QR</th>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>ID Funcionario</th>
                            <th>ID Visitante</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && count($result) > 0) : ?>
                            <?php foreach ($result as $row) : ?>
                                <tr id="fila-<?php echo $row['IdDispositivo']; ?>" class="<?php echo $row['Estado'] === 'Inactivo' ? 'fila-inactiva' : ''; ?>">
                                    <td><?php echo $row['IdDispositivo']; ?></td>
                                    <td class="text-center">
                                        <?php if ($row['QrDispositivo']) : ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="verQR('<?php echo htmlspecialchars($row['QrDispositivo']); ?>', <?php echo $row['IdDispositivo']; ?>)"
                                                    title="Ver código QR">
                                                <i class="fas fa-qrcode me-1"></i> Ver QR
                                            </button>
                                        <?php else : ?>
                                            <span class="badge badge-warning">Sin QR</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['TipoDispositivo']; ?></td>
                                    <td><?php echo $row['MarcaDispositivo']; ?></td>
                                    <td><?php echo $row['IdFuncionario'] ?? '-'; ?></td>
                                    <td><?php echo $row['IdVisitante'] ?? '-'; ?></td>
                                    <td class="text-center">
                                        <?php if ($row['Estado'] === 'Activo') : ?>
                                            <span class="badge badge-success badge-estado">Activo</span>
                                        <?php else : ?>
                                            <span class="badge badge-secondary badge-estado">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                                onclick="cargarDatosEdicion(<?php echo $row['IdDispositivo']; ?>, '<?php echo htmlspecialchars($row['QrDispositivo'] ?? ''); ?>', '<?php echo htmlspecialchars($row['TipoDispositivo']); ?>', '<?php echo htmlspecialchars($row['MarcaDispositivo']); ?>', <?php echo $row['IdFuncionario'] ?? 'null'; ?>, <?php echo $row['IdVisitante'] ?? 'null'; ?>)"
                                                title="Editar dispositivo" data-toggle="modal" data-target="#modalEditar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm <?php echo $row['Estado'] === 'Activo' ? 'btn-outline-warning' : 'btn-outline-success'; ?>" 
                                                onclick="confirmarCambioEstado(<?php echo $row['IdDispositivo']; ?>, '<?php echo $row['Estado']; ?>')"
                                                title="<?php echo $row['Estado'] === 'Activo' ? 'Desactivar' : 'Activar'; ?> dispositivo">
                                            <i class="fas <?php echo $row['Estado'] === 'Activo' ? 'fa-lock' : 'fa-lock-open'; ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay dispositivos registrados con los filtros seleccionados</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver QR -->
<div class="modal fade" id="modalVerQR" tabindex="-1" role="dialog" aria-labelledby="modalVerQRLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalVerQRLabel">
                    <i class="fas fa-qrcode me-2"></i>Código QR - Dispositivo #<span id="qrDispositivoId"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImagen" src="" alt="Código QR" class="img-fluid" style="max-width: 300px; border: 2px solid #ddd; padding: 10px; border-radius: 5px;">
                <p class="text-muted mt-3">Escanea este código con tu dispositivo móvil</p>
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

<!-- Modal Editar Dispositivo -->
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
                            <label for="editTipo" class="form-label">Tipo de Dispositivo</label>
                            <select id="editTipo" class="form-control" name="tipo" required>
                                <option value="">-- Seleccione un tipo --</option>
                                <option value="Portatil">Portátil</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Computador">Computador</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editMarca" class="form-label">Marca</label>
                            <input type="text" id="editMarca" class="form-control" name="marca" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editFuncionario" class="form-label">ID Funcionario</label>
                            <input type="number" id="editFuncionario" class="form-control" name="IdFuncionario">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editVisitante" class="form-label">ID Visitante</label>
                            <input type="number" id="editVisitante" class="form-control" name="IdVisitante">
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

<!-- Modal Confirmar Cambio de Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" id="headerCambioEstado">
                <h5 class="modal-title" id="tituloCambioEstado"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="toggle-container">
                    <label class="btn-lock" id="toggleEstadoVisual">
                        <svg width="36" height="40" viewBox="0 0 36 40">
                            <path class="lockb" d="M27 27C27 34.1797 21.1797 40 14 40C6.8203 40 1 34.1797 1 27C1 19.8203 6.8203 14 14 14C21.1797 14 27 19.8203 27 27ZM15.6298 26.5191C16.4544 25.9845 17 25.056 17 24C17 22.3431 15.6569 21 14 21C12.3431 21 11 22.3431 11 24C11 25.056 11.5456 25.9845 12.3702 26.5191L11 32H17L15.6298 26.5191Z"></path>
                            <path class="lock" d="M6 21V10C6 5.58172 9.58172 2 14 2V2C18.4183 2 22 5.58172 22 10V21"></path>
                            <path class="bling" d="M29 20L31 22"></path>
                            <path class="bling" d="M31.5 15H34.5"></path>
                            <path class="bling" d="M29 10L31 8"></path>
                        </svg>
                    </label>
                </div>
                <p id="mensajeCambioEstado" class="mb-3 mt-2" style="font-size: 1.1rem;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarCambioEstado">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/javascript/demo/sb-admin-2.min.js"></script>

<script>
let dispositivoIdAEditar = null;
let dispositivoACambiarEstado = null;
let estadoActual = null;

// Función para mostrar QR
function verQR(rutaQR, idDispositivo) {
    const rutaCompleta = '../' + rutaQR;
    
    $('#qrDispositivoId').text(idDispositivo);
    $('#qrImagen').attr('src', rutaCompleta);
    $('#btnDescargarQR').attr('href', rutaCompleta).attr('download', 'QR-Dispositivo-' + idDispositivo + '.png');
    
    $('#modalVerQR').modal('show');
}

function cargarDatosEdicion(id, qr, tipo, marca, idFuncionario, idVisitante) {
    dispositivoIdAEditar = id;
    $('#editId').val(id);
    $('#editTipo').val(tipo);
    $('#editMarca').val(marca);
    $('#editFuncionario').val(idFuncionario);
    $('#editVisitante').val(idVisitante);
}

function confirmarCambioEstado(id, estado) {
    dispositivoACambiarEstado = id;
    estadoActual = estado;
    
    const nuevoEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
    const accion = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
    const colorHeader = nuevoEstado === 'Activo' ? 'bg-success' : 'bg-warning';
    
    // Configurar el toggle visual
    const toggleLabel = document.getElementById('toggleEstadoVisual');
    
    if (nuevoEstado === 'Activo') {
        // Estado ACTIVO: verde con candado abierto
        toggleLabel.classList.add('activo');
    } else {
        // Estado INACTIVO: rojo con candado cerrado
        toggleLabel.classList.remove('activo');
    }
    
    $('#headerCambioEstado').removeClass('bg-success bg-warning').addClass(colorHeader + ' text-white');
    $('#tituloCambioEstado').html(`<i class="fas fa-${nuevoEstado === 'Activo' ? 'lock-open' : 'lock'} me-2"></i>${accion.charAt(0).toUpperCase() + accion.slice(1)} Dispositivo`);
    $('#mensajeCambioEstado').html(`¿Está seguro que desea <strong>${accion}</strong> este dispositivo?`);
    
    $('#modalCambiarEstado').modal('show');
}

$('#btnConfirmarCambioEstado').click(function() {
    if (!dispositivoACambiarEstado) return;
    
    const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
    
    $.ajax({
        url: '../Controller/parqueadero_dispositivo/ControladorDispositivo.php',
        type: 'POST',
        data: {
            accion: 'cambiar_estado',
            id: dispositivoACambiarEstado,
            estado: nuevoEstado
        },
        dataType: 'json',
        success: function(response) {
            $('#modalCambiarEstado').modal('hide');
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#modalCambiarEstado').modal('hide');
            alert('Error al intentar cambiar el estado del dispositivo');
        }
    });
});

$('#btnGuardarCambios').click(function() {
    const formData = {
        accion: 'actualizar',
        id: $('#editId').val(),
        tipo: $('#editTipo').val(),
        marca: $('#editMarca').val(),
        id_funcionario: $('#editFuncionario').val(),
        id_visitante: $('#editVisitante').val()
    };
    if (!formData.tipo || !formData.marca) {
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
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior_supervisor.php'; ?>