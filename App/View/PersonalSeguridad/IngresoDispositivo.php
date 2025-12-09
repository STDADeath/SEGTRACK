<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container py-5">
    <div class="card border-0 shadow-lg rounded-4 bg-light">
        <div class="card-body px-4 py-5">
            
            <!-- TÍTULO PRINCIPAL -->
            <h4 class="text-center fw-bold text-primary mb-5">
                <i class="fas fa-laptop me-2"></i>Control de Ingreso de Dispositivos
            </h4>

            <!-- SECCIÓN DE ESCANEO QR -->
            <div class="text-center mb-5">
                <h5 class="fw-semibold mb-4 text-secondary">
                    <i class="fas fa-qrcode me-2"></i>Escanear Código QR del Dispositivo
                </h5>

                <!-- CONTENEDOR DEL LECTOR QR -->
                <div class="d-flex justify-content-center mb-3">
                    <div id="qr-reader" style="width: 100%; max-width: 500px;"></div>
                </div>

                <!-- SELECCIÓN DEL TIPO DE MOVIMIENTO -->
                <div class="mt-4 w-50 mx-auto">
                    <label for="tipoMovimiento" class="form-label fw-semibold text-secondary">
                        Tipo de movimiento
                    </label>
                    <select id="tipoMovimiento" class="form-select text-center border-primary shadow-sm">
                        <option value="Entrada">Entrada</option>
                        <option value="Salida">Salida</option>
                    </select>
                </div>

                <!-- BOTÓN PARA ACTIVAR CÁMARA -->
                <button id="btnCapturar" class="btn btn-primary mt-4 px-4 py-2 shadow-sm fw-semibold">
                    <i class="fas fa-camera me-2"></i>Capturar Código QR
                </button>
            </div>

            <!-- MENSAJES DE RESULTADO -->
            <div class="mb-4">
                <div id="mensajeExito" class="alert alert-success text-center d-none mb-3 shadow-sm"></div>
                <div id="mensajeError" class="alert alert-danger text-center d-none mb-3 shadow-sm"></div>
            </div>

            <!-- TABLA DE INGRESOS RECIENTES -->
            <div class="bg-white p-4 rounded-4 shadow-sm">
                
                <!-- Encabezado + Botón PDF alineado -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold text-secondary mb-0">
                        <i class="fas fa-list me-2"></i>Lista de Movimientos de Dispositivos
                    </h5>

                    <!-- BOTÓN PDF (OPCIONAL) -->
                    <a href="/SEGTRACK/App/Controller/ControladorDispositivoPDF.php?accion=pdf" 
                       target="_blank" 
                       class="btn btn-danger shadow-sm fw-semibold">
                        <i class="fas fa-file-pdf me-2"></i>Descargar PDF
                    </a>
                </div>

                <!-- TABLA -->
                <div class="table-responsive">
                    <table id="tablaDispositivosDT" class="table table-hover align-middle text-center mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Código QR</th>
                                <th>Tipo</th>
                                <th>Marca</th>
                                <th>Tipo Movimiento</th>
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

<!-- LIBRERÍAS EXTERNAS -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap4.min.js"></script>

<!-- SCRIPT PERSONALIZADO -->
<script src="../../../Public/js/javascript/js/ValidacionIngresoDispositivo.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>