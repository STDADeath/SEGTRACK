<?php
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Core/conexion.php';

$conexion = new Conexion();
$conn     = $conexion->getConexion();

$sql = "SELECT 
            u.IdUsuario,
            f.NombreFuncionario,
            f.DocumentoFuncionario,
            u.TipoRol,
            u.Estado
        FROM usuario u
        INNER JOIN funcionario f ON u.IdFuncionario = f.IdFuncionario
        ORDER BY u.Estado DESC, u.IdUsuario DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roles_permitidos = ['Supervisor', 'Personal Seguridad', 'Administrador'];
?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>Usuarios Registrados
        </h1>
        <a href="./Usuario.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Usuario
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle text-center"
                   id="tablaUsuarios">
                <thead class="table-dark">
                    <tr>
                        <th>Funcionario</th>
                        <th>Documento</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && count($result) > 0) : ?>
                        <?php foreach ($result as $row) : ?>
                            <?php $activo = $row['Estado'] === 'Activo'; ?>
                            <tr id="fila-<?= $row['IdUsuario'] ?>">

                                <td class="text-start">
                                    <strong><?= htmlspecialchars($row['NombreFuncionario']) ?></strong>
                                </td>

                                <td><?= htmlspecialchars($row['DocumentoFuncionario']) ?></td>

                                <td><?= htmlspecialchars($row['TipoRol']) ?></td>

                                <td>
                                    <?php if ($activo): ?>
                                        <span class="badge bg-success px-3 py-2">Activo</span>
                                    <?php else: ?>
                                        <span class="badge px-3 py-2"
                                              style="background-color:#60a5fa;">Inactivo</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <!-- ✅ EDITAR: solo data-* sin onclick ni data-bs-toggle -->
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm rounded-3 btn-editar-rol"
                                            style="width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;"
                                            data-id="<?= $row['IdUsuario'] ?>"
                                            data-rol="<?= htmlspecialchars($row['TipoRol'], ENT_QUOTES) ?>"
                                            data-nombre="<?= htmlspecialchars($row['NombreFuncionario'], ENT_QUOTES) ?>"
                                            title="Editar rol">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>

                                    <!-- CANDADO ESTADO -->
                                    <button class="btn btn-sm btn-toggle-estado ms-1"
                                            style="width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #d4af37;background:#fff8dc;"
                                            data-id="<?= $row['IdUsuario'] ?>"
                                            data-estado="<?= $row['Estado'] ?>"
                                            title="<?= $activo ? 'Desactivar usuario' : 'Activar usuario'; ?>">
                                        <?php if ($activo): ?>
                                            <i class="fas fa-lock text-warning"></i>
                                        <?php else: ?>
                                            <i class="fas fa-unlock text-success"></i>
                                        <?php endif; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2 d-block"></i>
                                <p class="text-muted">No hay usuarios registrados</p>
                                <a href="./Usuario.php" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i> Registrar Usuario
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EDITAR ROL -->
<div class="modal fade" id="modalEditarRol" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header text-white" style="background-color:#3b82f6;">
                <h5 class="modal-title">
                    <i class="fas fa-user-tag me-2"></i>Editar Rol de Usuario
                </h5>
                <!-- ✅ btn-close normal, Bootstrap 4 no tiene btn-close-white -->
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="editIdUsuario">

                <div class="mb-3">
                    <label class="form-label fw-bold">Funcionario</label>
                    <!-- ✅ input readonly en vez de <p> -->
                    <input type="text" id="editNombreFuncionario"
                           class="form-control bg-light" readonly>
                </div>

                <div class="mb-3">
                    <label for="editTipoRol" class="form-label fw-bold">
                        Nuevo Rol <span class="text-danger">*</span>
                    </label>
                    <select id="editTipoRol" class="form-control" required>
                        <option value="">Seleccione un rol</option>
                        <?php foreach ($roles_permitidos as $rol): ?>
                            <option value="<?= htmlspecialchars($rol) ?>">
                                <?= htmlspecialchars($rol) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Solo se puede modificar el rol del usuario.
                </div>
            </div>

            <div class="modal-footer">
                <!-- ✅ data-dismiss (Bootstrap 4) -->
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn text-white" id="btnGuardarRol"
                        style="background-color:#3b82f6;">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- CSS -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- JS EN ORDEN CORRECTO -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>

<!-- ✅ Bootstrap JS es OBLIGATORIO para que funcione .modal('show') -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Tu JS siempre al final -->
<script src="../../../Public/js/javascript/js/ValidacionUsuarioLista.js"></script>

<style>
.table-striped tbody tr:nth-of-type(odd) { background-color: #f8f9fc; }
.table-hover tbody tr:hover              { background-color: #f1f3f8; transition: 0.2s ease-in-out; }
.badge { font-size: 0.85rem; }
table.dataTable thead .sorting:after,
table.dataTable thead .sorting:before,
table.dataTable thead .sorting_asc:after,
table.dataTable thead .sorting_desc:after { display: none !important; }
</style>