<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<!-- DataTables Responsive CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<div class="container py-4">
    <div class="card border-0 shadow-lg rounded-4 bg-light">
        <div class="card-body px-3 px-md-4 py-4 py-md-5">

            <h4 class="text-center fw-bold text-primary mb-4">
                <i class="fas fa-car me-2"></i>Control de Ingreso de Vehículos
            </h4>

            <!-- ===== ESCANER QR ===== -->
            <div class="text-center mb-4">

                <h5 class="fw-semibold mb-3 text-secondary">
                    <i class="fas fa-qrcode me-2"></i>Escanear Código QR del Vehículo
                </h5>

                <div class="mx-auto mb-3" style="width: 100%; max-width: 400px;">
                    <div id="qr-reader" style="width: 100%;"></div>
                </div>

                <div class="mt-3 mx-auto" style="max-width: 100%; width: 320px;">
                    <label for="tipoMovimiento" class="form-label fw-semibold text-secondary">
                        Tipo de movimiento
                    </label>
                    <select id="tipoMovimiento" class="form-select text-center border-primary shadow-sm">
                        <option value="Entrada">Entrada</option>
                        <option value="Salida">Salida</option>
                    </select>
                </div>

                <div class="mt-3 px-3 px-md-0">
                    <button id="btnCapturar" class="btn btn-primary px-4 py-2 shadow-sm fw-semibold w-100" style="max-width: 320px;">
                        <i class="fas fa-camera me-2"></i>Capturar Código QR
                    </button>
                </div>

            </div>
            <!-- ===== FIN ESCANER QR ===== -->

            <!-- ===== CARD VEHÍCULO + FOTO FUNCIONARIO ===== -->
            <div id="cardVehiculo" class="d-none mb-4">
                <div class="card border-0 shadow rounded-4 mx-auto" style="max-width: 450px; width: 100%;">
                    <div class="card-body text-center py-4 px-3">

                        <img id="fotoFuncionario"
                             src=""
                             alt="Foto Funcionario"
                             class="rounded-circle mb-3 border border-4 border-primary shadow"
                             style="width: 160px; height: 160px; object-fit: cover;">

                        <h5 id="nombreDueno" class="fw-bold text-primary mb-1 fs-5"></h5>

                        <hr class="my-2">

                        <p class="mb-1">
                            <i class="fas fa-car text-primary me-1"></i>
                            <span id="tipoVehiculo" class="fw-semibold"></span>
                            — <span id="placaVehiculo" class="text-muted"></span>
                        </p>
                        <p class="mb-1 text-muted small">
                            Descripción: <span id="descripcionVehiculo"></span>
                        </p>
                        <p class="mb-2 text-muted small">
                            Espacio N°: <span id="espacioVehiculo"></span>
                        </p>

                        <span id="badgeMovimiento" class="badge fs-6 px-3 py-2 mb-2"></span>
                        <p id="fechaVehiculo" class="text-muted small mt-1 mb-0"></p>

                    </div>
                </div>
            </div>
            <!-- ===== FIN CARD ===== -->

            <!-- ===== MENSAJES ===== -->
            <div class="mb-4 px-2">
                <div id="mensajeExito" class="alert alert-success text-center d-none mb-3 shadow-sm fw-semibold"></div>
                <div id="mensajeError"  class="alert alert-danger  text-center d-none mb-3 shadow-sm fw-semibold"></div>
            </div>
            <!-- ===== FIN MENSAJES ===== -->

            <!-- ===== TABLA ===== -->
            <div class="bg-white p-3 p-md-4 rounded-4 shadow-sm">

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                    <h5 class="fw-semibold text-secondary mb-0">
                        <i class="fas fa-list me-2"></i>Lista de Movimientos de Vehículos
                    </h5>
                    <a href="/SEGTRACK/App/Controller/ParqueaderoIngresoPDF.php?accion=pdf"
                       target="_blank"
                       class="btn btn-danger shadow-sm fw-semibold w-100 w-sm-auto">
                        <i class="fas fa-file-pdf me-2"></i>Descargar PDF
                    </a>
                </div>

                <div class="table-responsive">
                    <table id="tablaParqueaderoDT" class="table table-hover align-middle text-center mb-0 small">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Placa</th>
                                <th>Tipo</th>
                                <th>Dueño</th>
                                <th>N° Espacio</th>
                                <th>Movimiento</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
            <!-- ===== FIN TABLA ===== -->

        </div>
    </div>
</div>

<!-- QR Scanner -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<!-- DataTables Responsive JS -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>

<script src="../../../Public/js/javascript/js/ValidacionParqueaderoIngreso.js"></script>