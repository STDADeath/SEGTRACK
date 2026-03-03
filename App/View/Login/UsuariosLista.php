<?php
/**
 * VISTA: LISTA DE USUARIOS
 * Capa Vista (MVC): solo presenta datos, sin lógica de negocio.
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
        ORDER BY u.IdUsuario DESC";

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

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-primary fw-bold">Lista de Usuarios</h5>
        </div>
        <div class="card-body table-responsive">
            <table id="tablaUsuarios" class="table table-hover align-middle" width="100%">

                <thead class="text-white" style="background-color:#5f636e;">
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
                        <?php foreach ($result as $row) :
                            $activo = $row['Estado'] === 'Activo';
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['NombreFuncionario']) ?></strong></td>
                                <td><?= htmlspecialchars($row['DocumentoFuncionario']) ?></td>
                                <td><?= htmlspecialchars($row['TipoRol']) ?></td>

                                <!-- ESTADO: badge verde Activo / gris Inactivo con tamaño fijo -->
                                <td class="text-center">
                                    <span id="badge-estado-<?= $row['IdUsuario'] ?>"
                                          class="badge <?= $activo ? 'bg-success' : 'bg-secondary' ?> text-white badge-estado">
                                        <?= $activo ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>

                                <!-- ACCIONES: editar rol (modal) + candado (toggle estado) -->
                                <td>
                                    <div class="d-flex gap-2">

                                        <!-- EDITAR ROL -->
                                        <button class="btn btn-outline-primary btn-accion btn-editar-rol"
                                                title="Editar rol"
                                                data-id="<?= $row['IdUsuario'] ?>"
                                                data-rol="<?= htmlspecialchars($row['TipoRol'], ENT_QUOTES) ?>"
                                                data-nombre="<?= htmlspecialchars($row['NombreFuncionario'], ENT_QUOTES) ?>">
                                            <i class="fas fa-edit text-primary"></i>
                                        </button>

                                        <!-- CANDADO ESTADO -->
                                        <button id="btn-estado-<?= $row['IdUsuario'] ?>"
                                                class="btn <?= $activo ? 'btn-outline-warning' : 'btn-outline-success' ?> btn-accion btn-toggle-estado"
                                                title="<?= $activo ? 'Desactivar usuario' : 'Activar usuario' ?>"
                                                data-id="<?= $row['IdUsuario'] ?>"
                                                data-estado="<?= $row['Estado'] ?>">
                                            <i class="fas <?= $activo ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                                No hay usuarios registrados.
                                <br>
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
<div class="modal fade" id="modalEditarRol" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-tag me-2"></i>Editar Rol de Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="editIdUsuario">

                <div class="mb-3">
                    <label class="form-label fw-bold">Funcionario</label>
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
                <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarRol">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>

        </div>
    </div>
</div>

<!-- CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../../Public/css/Tablas.css">

<!-- JS EN ORDEN CORRECTO -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../Public/js/javascript/js/ValidacionUsuarioLista.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>