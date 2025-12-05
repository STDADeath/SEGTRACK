<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container py-5">
    <div class="card border-0 shadow-lg rounded-4 bg-light">
        <div class="card-body px-4 py-5">
            <h4 class="text-center fw-bold text-primary mb-5">
                <i class="fas fa-parking me-2"></i>Control de Ingreso de Vehículos
            </h4>

            <div class="text-center mb-5">
                <h5 class="fw-semibold mb-4 text-secondary">
                    <i class="fas fa-qrcode me-2"></i>Escanear Código QR
                </h5>
                <div class="d-flex justify-content-center mb-3">
                    <div id="qr-reader-parqueadero" style="width: 100%; max-width: 500px;"></div>
                </div>
                <div class="mt-4 w-50 mx-auto">
                    <label for="tipoMovimientoParqueadero" class="form-label fw-semibold text-secondary">
                        Tipo de movimiento
                    </label>
                    <select id="tipoMovimientoParqueadero" class="form-select text-center border-primary shadow-sm">
                        <option value="Entrada">Entrada</option>
                        <option value="Salida">Salida</option>
                    </select>
                </div>
                <button id="btnCapturarParqueadero" class="btn btn-primary mt-4 px-4 py-2 shadow-sm fw-semibold">
                    <i class="fas fa-camera me-2"></i>Capturar Código QR
                </button>
            </div>

            <div class="mb-4">
                <div id="mensajeExitoParqueadero" class="alert alert-success text-center d-none mb-3 shadow-sm"></div>
                <div id="mensajeErrorParqueadero" class="alert alert-danger text-center d-none mb-3 shadow-sm"></div>
            </div>

            <div class="bg-white p-4 rounded-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold text-secondary mb-0">
                        <i class="fas fa-list me-2"></i>Lista de Movimientos de Vehículos
                    </h5>
                </div>
                <div class="table-responsive">
                    <table id="tablaParqueaderoDT" class="table table-hover align-middle text-center mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Descripción</th>
                                <th>Placa</th>
                                <th>Tipo Vehículo</th>
                                <th>Movimiento</th>
                                <th>Fecha Ingreso</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap4.min.js"></script>
<script src="../../../Public/js/javascript/js/ValidacionParqueaderoIngreso.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
