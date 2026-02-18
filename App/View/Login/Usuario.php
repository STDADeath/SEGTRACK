<?php
require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Core/conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->getConexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("<div style='color: red; text-align: center; padding: 20px; border: 1px solid red;'>Error de Conexión a la Base de Datos: " . $e->getMessage() . "</div>");
}

$funcionarios = [];
$sql_funcionarios = "SELECT 
                        f.IdFuncionario, 
                        f.NombreFuncionario, 
                        f.DocumentoFuncionario 
                     FROM Funcionario f
                     LEFT JOIN Usuario u ON f.IdFuncionario = u.IdFuncionario
                     WHERE f.Estado = 'Activo' 
                     AND u.IdUsuario IS NULL
                     ORDER BY f.NombreFuncionario";

try {
    $stmt_func = $pdo->prepare($sql_funcionarios);
    $stmt_func->execute();
    $funcionarios = $stmt_func->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div style='color: red; text-align: center;'>Error al cargar los funcionarios: " . $e->getMessage() . "</div>";
}

$roles_permitidos = ['Supervisor', 'Personal Seguridad', 'Administrador'];
?>

<style>
    .input-valid { border: 2px solid #28a745 !important; background: #eaffea !important; }
    .input-invalid { border: 2px solid #dc3545 !important; background: #ffeaea !important; }
    .label-valid { color: #28a745 !important; font-weight: bold; }
    .label-invalid { color: #dc3545 !important; font-weight: bold; }
</style>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-circle me-2"></i>Registrar Usuario</h1>
        <a href="UsuariosLista.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-list me-1"></i> Ver Usuarios
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Registro</h6>
        </div>
        <div class="card-body">
            <form id="formUsuario">
                <input type="hidden" name="accion" value="registrar">
                <div class="row">
                    <!-- Funcionario Asignado -->
                    <div class="col-md-12 mb-3">
                        <label for="id_funcionario" class="form-label" id="label_funcionario">Funcionario <span class="text-danger">*</span></label>
                        <select id="id_funcionario" name="id_funcionario" class="form-control border-primary shadow-sm">
                            <option value="">Selecciona un Funcionario</option>
                            <?php foreach ($funcionarios as $func): ?>
                                <option value="<?php echo htmlspecialchars($func['IdFuncionario']); ?>">
                                    <?php echo htmlspecialchars($func['NombreFuncionario']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Solo se muestran funcionarios sin usuario asignado</small>
                    </div>
                </div>

                <div class="row">
                    <!-- Tipo de Rol -->
                    <div class="col-md-6 mb-3">
                        <label for="tipo_rol" class="form-label" id="label_tipo_rol">Tipo de Rol <span class="text-danger">*</span></label>
                        <select id="tipo_rol" name="tipo_rol" class="form-control border-primary shadow-sm">
                            <option value="">Seleccione un Rol</option>
                            <?php foreach ($roles_permitidos as $rol): ?>
                                <option value="<?php echo htmlspecialchars($rol); ?>">
                                    <?php echo htmlspecialchars($rol); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Contraseña -->
                    <div class="col-md-6 mb-3">
                        <label for="contrasena" class="form-label" id="label_contrasena">Contraseña <span class="text-danger">*</span></label>
                        <div style="position: relative;">
                            <input type="password" id="contrasena" name="contrasena"
                                   class="form-control border-primary shadow-sm"
                                   placeholder="Mínimo 7 caracteres" minlength="7">
                            <button type="button" onclick="togglePassword()"
                                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6c757d;">
                                <i class="fas fa-eye" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Todos los campos marcados con <span class="text-danger">*</span> son obligatorios
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Registrar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="../../../Public/js/javascript/js/ValidacionesUsuario.js"></script>
