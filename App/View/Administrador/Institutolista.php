<?php
/**
 * ==========================================================
 * VISTA: LISTA DE INSTITUCIONES
 * ==========================================================
 * - Muestra todas las instituciones registradas
 * - Estado en penúltima columna
 * - Acciones en última columna
 */

require_once __DIR__ . '/../layouts/parte_superior_administrador.php';
require_once __DIR__ . '/../../Core/Conexion.php';
?>

<div class="container-fluid px-4 py-4">

    <!-- TÍTULO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">
            <i class="fas fa-school me-2"></i>Lista de Instituciones
        </h4>

        <a href="Instituto.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Registrar
        </a>
    </div>

    <!-- CARD CONTENEDOR -->
    <div class="card shadow">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tablaInstitutos"
                        class="table table-bordered table-hover table-striped align-middle text-center"
                        width="100%">

                    <!-- ENCABEZADO -->
                    <thead class="table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>NIT</th>
                            <th>Tipo</th>
                            <th>Estado</th> <!-- PENÚLTIMA -->
                            <th>Acciones</th> <!-- ÚLTIMA -->
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        try {

                            $conexion = new Conexion();
                            $db = $conexion->getConexion();

                            // Consulta ordenada descendente
                            $sql = "SELECT *
                                    FROM institucion
                                    ORDER BY IdInstitucion DESC";

                            $stmt = $db->prepare($sql);
                            $stmt->execute();

                            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>

                            <tr>

                                <!-- NOMBRE -->
                                <td><?= htmlspecialchars($fila['NombreInstitucion']); ?></td>

                                <!-- NIT -->
                                <td><?= htmlspecialchars($fila['Nit_Codigo']); ?></td>

                                <!-- TIPO -->
                                <td><?= htmlspecialchars($fila['TipoInstitucion']); ?></td>

                                <!-- ESTADO (PENÚLTIMA COLUMNA) -->
                                <td>
                                    <?php if ($fila['EstadoInstitucion'] === 'Activo'): ?>

                                        <!-- ACTIVO VERDE -->
                                        <span class="badge bg-success px-3 py-2">
                                            Activo
                                        </span>

                                    <?php else: ?>

                                        <!-- INACTIVO AZUL CLARO -->
                                        <span class="badge px-3 py-2"
                                              style="background-color:#60a5fa;">
                                            Inactivo
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <!-- ACCIONES (ÚLTIMA COLUMNA) -->
                                <td>

                                    <!-- BOTÓN EDITAR CUADRADO AZUL -->
                                    <a href="Instituto.php?IdInstitucion=<?= $fila['IdInstitucion']; ?>"
                                       class="btn btn-outline-primary btn-sm rounded-3"
                                       style="width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center;"
                                       title="Editar">

                                        <i class="fas fa-pen-to-square"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php
                            endwhile;

                        } catch (PDOException $e) {

                            echo '<tr>
                                    <td colspan="5" class="text-danger text-center">
                                        Error al cargar los datos
                                    </td>
                                  </tr>';
                        }
                        ?>

                    </tbody>

                </table>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/parte_inferior_administrador.php'; ?>

<!-- DATATABLES -->
<link rel="stylesheet" href="../../../Public/vendor/datatables/dataTables.bootstrap4.css">
<script src="../../../Public/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../../../Public/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- FONT AWESOME (ICONOS) -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- JS PERSONALIZADO -->
<script src="../../../Public/js/javascript/js/ValidacionInstitutolista.js"></script>
