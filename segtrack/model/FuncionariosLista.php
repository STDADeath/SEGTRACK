<?php
require_once "../Controller/Conexion/conexion.php";
require_once "Funcionario.php";

$conexion = (new Conexion())->getConexion();
$funcionarioModel = new Funcionario($conexion);
$funcionarios = $funcionarioModel->obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Funcionarios - Segtrack</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="text-center mb-4">Listado de Funcionarios</h2>

  <!-- Botón agregar -->
  <div class="mb-3 text-end">
    <a href="../registroFun.html" class="btn btn-success">
      <i class="bx bx-user-plus"></i> Agregar nuevo funcionario
    </a>
  </div>

  <!-- Tabla -->
  <div class="table-responsive shadow">
    <table class="table table-bordered table-striped align-middle text-center">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Documento</th>
          <th>Correo</th>
          <th>Teléfono</th>
          <th>Cargo</th>
          <th>Sede</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($funcionarios)): ?>
          <?php foreach ($funcionarios as $f): ?>
            <tr>
              <td><?= htmlspecialchars($f['IdFuncionario']) ?></td>
              <td><?= htmlspecialchars($f['NombreFuncionario']) ?></td>
              <td><?= htmlspecialchars($f['DocumentoFuncionario']) ?></td>
              <td><?= htmlspecialchars($f['CorreoFuncionario']) ?></td>
              <td><?= htmlspecialchars($f['TelefonoFuncionario']) ?></td>
              <td><?= htmlspecialchars($f['CargoFuncionario']) ?></td>
              <td><?= htmlspecialchars($f['TipoSede'] ?? 'Sin sede') ?></td>
              <td>
                <a href="editarFuncionario.php?id=<?= $f['IdFuncionario'] ?>" class="btn btn-warning btn-sm">
                  <i class='bx bx-edit'></i> Editar
                </a>
                <a href="eliminarFuncionario.php?id=<?= $f['IdFuncionario'] ?>" 
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('¿Seguro que deseas eliminar este funcionario?');">
                  <i class='bx bx-trash'></i> Eliminar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8">No hay funcionarios registrados</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</body>
</html>
