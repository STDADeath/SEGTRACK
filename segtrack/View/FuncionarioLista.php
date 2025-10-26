<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista de Funcionarios</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

</head>
<body class="bg-light p-4">

<div class="container bg-white p-4 rounded shadow-sm">
  <h3 class="text-center mb-4">📋 Lista de Funcionarios</h3>

  <table id="tablaFuncionarios" class="table table-striped table-bordered" style="width:100%">
      <thead class="table-dark">
          <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Documento</th>
              <th>Teléfono</th>
              <th>Cargo</th>
              <th>Sede</th>
              <th>Ciudad</th>
          </tr>
      </thead>
      <tbody></tbody>
  </table>
</div>

<script>
$(document).ready(function() {
  $('#tablaFuncionarios').DataTable({
      "ajax": {
          "url": "../../controller/sede_institucion_funcionario_usuario/ControladorFuncionarioLista.php?action=listar",
          "dataSrc": "data"
      },
      "columns": [
          { "data": "IdFuncionario" },
          { "data": "NombreFuncionario" },
          { "data": "CorreoFuncionario" },
          { "data": "DocumentoFuncionario" },
          { "data": "TelefonoFuncionario" },
          { "data": "CargoFuncionario" },
          { "data": "Sede" },
          { "data": "Ciudad" }
      ],
      "language": {
          "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
      }
  });
});
</script>

</body>
</html>
<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
