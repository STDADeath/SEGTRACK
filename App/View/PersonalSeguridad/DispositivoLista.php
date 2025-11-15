<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-laptop me-2"></i>Dispositivos Registrados</h1>
        <a href="../View/Dispositivos.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Dispositivo
        </a>
    </div>

    <?php
    require_once __DIR__ . "../../../Core/conexion.php";
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

    $where = "";
    if (count($filtros) > 0) {
        $where = "WHERE " . implode(" AND ", $filtros);
    }

    $sql = "SELECT * FROM dispositivo $where ORDER BY IdDispositivo DESC";
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
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo de Dispositivo</label>
                    <select name="tipo" id="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Portatil" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Portatil') ? 'selected' : '' ?>>Portátil</option>
                        <option value="Tablet" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Tablet') ? 'selected' : '' ?>>Tablet</option>
                        <option value="Computador" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Computador') ? 'selected' : '' ?>>Computador</option>
                        <option value="Otro" <?= (isset($_GET['tipo']) && $_GET['tipo'] == 'Otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <a href="Dispositivolista.php" class="btn btn-secondary"><i class="fas fa-broom me-1"></i> Limpiar</a>
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
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && count($result) > 0) : ?>
                            <?php foreach ($result as $row) : ?>
                                <tr id="fila-<?php echo $row['IdDispositivo']; ?>">
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
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="cargarDatosEdicion(<?php echo $row['IdDispositivo']; ?>, '<?php echo htmlspecialchars($row['QrDispositivo'] ?? ''); ?>', '<?php echo htmlspecialchars($row['TipoDispositivo']); ?>', '<?php echo htmlspecialchars($row['MarcaDispositivo']); ?>', <?php echo $row['IdFuncionario'] ?? 'null'; ?>, <?php echo $row['IdVisitante'] ?? 'null'; ?>)"
                                                title="Editar dispositivo" data-toggle="modal" data-target="#modalEditar">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
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

<!-- ✅ Modal para visualizar QR -->
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



<script>
let dispositivoIdAEditar = null;

// ✅ Función para mostrar QR
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

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>