<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container py-5">
    <div class="card border-0 shadow-lg rounded-4 bg-light">
        <div class="card-body px-4 py-5">
            
            <!-- TÍTULO PRINCIPAL -->
            <h4 class="text-center fw-bold text-primary mb-5">
                <i class="fas fa-id-card-alt me-2"></i>Control de Ingreso de Funcionarios
            </h4>

            <!-- SECCIÓN DE ESCANEO QR -->
            <div class="text-center mb-5">
                <h5 class="fw-semibold mb-4 text-secondary">
                    <i class="fas fa-qrcode me-2"></i>Escanear Código QR
                </h5>

                <!-- CONTENEDOR DEL LECTOR QR -->
                <div class="d-flex justify-content-center mb-3">
                    <div id="qr-reader" class="shadow-sm rounded-3 bg-white" style="width: 300px;"></div>
                </div>

                <!-- SELECCIÓN DEL TIPO DE MOVIMIENTO -->
                <div class="mt-4 w-50 mx-auto">
                    <label for="tipoMovimiento" class="form-label fw-semibold text-secondary">
                        Tipo de movimiento
                    </label>

                    <!-- Entrada / Salida -->
                    <select id="tipoMovimiento" class="form-select text-center border-primary shadow-sm">
                        <option value="Entrada">Entrada</option>
                        <option value="Salida">Salida</option>
                    </select>
                </div>

                <!-- BOTÓN PARA ACTIVAR CÁMARA -->
                <button id="btnCapturar" 
                        class="btn btn-primary mt-4 px-4 py-2 shadow-sm fw-semibold">
                    <i class="fas fa-camera me-2"></i>Capturar Código QR
                </button>
            </div>

            <!-- MENSAJES DE RESULTADO (ÉXITO / ERROR) -->
            <div class="mb-4">
                <div id="mensajeExito" class="alert alert-success text-center d-none mb-3 shadow-sm"></div>
                <div id="mensajeError" class="alert alert-danger text-center d-none mb-3 shadow-sm"></div>
            </div>

            <!-- TABLA DE INGRESOS RECIENTES -->
            <div class="bg-white p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold mb-3 text-secondary">
                    <i class="fas fa-list me-2"></i>Lista de Ingresos Recientes
                </h5>

                <div class="table-responsive">

                    <!-- DataTable se llena por AJAX -->
                    <table id="tablaIngresosDT" class="table table-hover align-middle text-center mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Funcionario</th>
                                <th>Cargo</th>
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
<script src="https://unpkg.com/html5-qrcode"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap4.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    // REFERENCIAS A ELEMENTOS HTML
    const msgErr = document.getElementById("mensajeError");
    const msgOk = document.getElementById("mensajeExito");
    const tipoMovimiento = document.getElementById("tipoMovimiento");
    const btn = document.getElementById("btnCapturar");

    // INSTANCIA DEL LECTOR QR
    const qrReader = new Html5Qrcode("qr-reader");
    let ultimaLectura = null; // evita lecturas repetidas

    // INICIALIZAR DATATABLE (CARGA DATOS VÍA AJAX)
    const table = $("#tablaIngresosDT").DataTable({
        ajax: {
            url: "/SEGTRACK/App/Controller/ControladorIngreso.php",
            dataSrc: "data" // el controlador devuelve un JSON { data: [...] }
        },
        columns: [
            { data: "NombreFuncionario" },
            { data: "CargoFuncionario" },
            { data: "TipoMovimiento" },

            // FORMATEA LA FECHA PARA MOSTRARLA BIEN
            { 
                data: "FechaIngreso",
                render: d => new Date(d).toLocaleString()
            }
        ],
        language: {
         url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });

    // FUNCIÓN PARA MOSTRAR MENSAJES
    const mostrarMensaje = (ok, texto) => {
        msgOk.classList.toggle("d-none", !ok);
        msgErr.classList.toggle("d-none", ok);
        (ok ? msgOk : msgErr).textContent = texto;
    };

    // CUANDO SE ESCANEA UN QR CORRECTAMENTE
    function onScanSuccess(qr) {

        if (qr === ultimaLectura) return; // evita duplicados
        ultimaLectura = qr;
        setTimeout(() => ultimaLectura = null, 2000);

        // ENVÍO DEL QR AL CONTROLADOR (POST)
        fetch("/SEGTRACK/App/Controller/ControladorIngreso.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                qr_codigo: qr,
                tipoMovimiento: tipoMovimiento.value
            })
        })
        .then(r => r.json())
        .then(data => {
            mostrarMensaje(data.success, data.message);

            if (data.success) {
                table.ajax.reload(null, false); // actualiza tabla sin reiniciar paginación
            }
        })
        .catch(() =>
            mostrarMensaje(false, "Error al enviar el código al servidor.")
        );

        qrReader.stop(); // detiene la cámara después de leer
    }

    // BOTÓN PARA INICIAR LA CÁMARA Y ESCANEAR
    btn.addEventListener("click", () => {
        qrReader.start(
            { facingMode: "environment" }, // usa cámara trasera
            { fps: 10, qrbox: 250 },
            onScanSuccess
        );
    });

});
</script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
