<?php
// App/View/Administrador/UsuariosLista.php

require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Core/conexion.php';

try {
    $db = new Conexion();
    $pdo = $db->getConexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("<div class='alert alert-danger text-center'>Error de conexión: " . $e->getMessage() . "</div>");
}

// CONSULTA DE USUARIOS + FUNCIONARIO
$sql = "SELECT 
            u.IdUsuario,
            u.TipoRol,
            u.Estado,
            f.NombreFuncionario
        FROM Usuario u
        INNER JOIN Funcionario f ON u.IdFuncionario = f.IdFuncionario
        ORDER BY u.IdUsuario DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>Lista de Usuarios
        </h1>
        <a href="Usuario.php" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-user-plus me-1"></i> Registrar Usuario
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Usuarios Registrados</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tablaUsuarios" width="100%" cellspacing="0">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>ID</th> 
                            <th>Funcionario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $user): ?>
                        <tr class="text-center">
                            <td><?php echo $user['IdUsuario']; ?></td>
                            <td><?php echo htmlspecialchars($user['NombreFuncionario']); ?></td>
                            <td><?php echo htmlspecialchars($user['TipoRol']); ?></td>

                            <td>
                                <?php 
                                $badgeClass = ($user['Estado'] == "Activo") ? 'bg-success' : 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?> px-2 py-1 estado-badge">
                                    <?php echo htmlspecialchars($user['Estado']); ?>
                                </span>
                            </td>

                            <td>
                                <a href="UsuarioEditar.php?id=<?php echo $user['IdUsuario']; ?>" 
                                    class="btn btn-warning btn-sm btn-editar"
                                    title="Editar Usuario">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <?php 
                                $btnClass = ($user['Estado'] == "Activo") ? 'btn-secondary' : 'btn-success';
                                $btnTitle = ($user['Estado'] == "Activo") ? 'Desactivar' : 'Activar';
                                ?>
                                <a href="#"
                                    class="btn <?php echo $btnClass; ?> btn-sm btn-toggle-estado"
                                    title="<?php echo $btnTitle; ?>"
                                    data-id="<?php echo $user['IdUsuario']; ?>" 
                                    data-estado-actual="<?php echo htmlspecialchars($user['Estado']); ?>" 
                                    data-funcionario="<?php echo htmlspecialchars($user['NombreFuncionario']); ?>">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php 
// Asegúrese de que estas dependencias se incluyan en el inferior de la plantilla
require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; 
?>

<script src="../../Public/js/javascript/js/ValidacionesUsuarioLista.js"></script>