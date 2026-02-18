<?php
/**
 * ========================================
 * LISTA DE USUARIOS - SEGTRACK
 * ========================================
 */

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

    <!-- Encabezado -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>Usuarios Registrados
        </h1>
        <a href="./Usuario.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Usuario
        </a>
    </div>

    <!-- Tarjeta tabla -->
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

                                <!-- Badge estado clickeable (igual que "No aplica" de dispositivos) -->
                                <td>
                                    <span class="badge <?= $activo ? 'bg-success' : 'bg-danger' ?>"
                                          style="padding:6px 14px;border-radius:20px;font-size:12px;
                                                 font-weight:500;cursor:pointer;transition:all .3s;"
                                          onclick="cambiarEstado(<?= $row['IdUsuario'] ?>, '<?= $row['Estado'] ?>')"
                                          title="Clic para cambiar estado">
                                        <i class="fas <?= $activo ? 'fa-check-circle' : 'fa-times-circle' ?> me-1"></i>
                                        <?= $row['Estado'] ?>
                                    </span>
                                </td>

                                <!-- Botón editar rol (igual estilo outline que botón editar dispositivo) -->
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                onclick='editarRol(
                                                    <?= $row["IdUsuario"] ?>,
                                                    "<?= htmlspecialchars($row["TipoRol"], ENT_QUOTES) ?>",
                                                    "<?= htmlspecialchars($row["NombreFuncionario"], ENT_QUOTES) ?>"
                                                )'
                                                title="Editar rol"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarRol">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
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

<!-- ================================================================
     MODAL EDITAR ROL
================================================================= -->
<div class="modal fade" id="modalEditarRol" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-user-tag me-2"></i>Editar Rol de Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="editIdUsuario">

                <div class="mb-3">
                    <label class="form-label fw-bold">Funcionario</label>
                    <p id="editNombreFuncionario"
                       class="form-control bg-light mb-0"
                       style="min-height:38px;padding:6px 12px;"></p>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btnGuardarRol">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- ── Scripts (mismo orden que DispositivoLista) ────────────── -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<link  rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="../../../Public/js/javascript/js/ValidacionListaUsuario.js"></script>