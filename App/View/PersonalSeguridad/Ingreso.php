<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>

<div class="container py-5">
    <div class="card border-0 shadow-lg rounded-4 bg-light">
        <div class="card-body px-4 py-5">
            
            <!-- Título -->
            <h4 class="text-center fw-bold text-primary mb-5">
                <i class="fas fa-id-card-alt me-2"></i>Control de Ingreso de Funcionarios
            </h4>

            <!-- Lector QR -->
            <div class="text-center mb-5">
                <h5 class="fw-semibold mb-4 text-secondary">
                    <i class="fas fa-qrcode me-2"></i>Escanear Código QR
                </h5>

                <!-- Cámara -->
                <div class="d-flex justify-content-center mb-3">
                    <div id="qr-reader" class="shadow-sm rounded-3 bg-white" style="width: 300px;"></div>
                </div>

                <!-- Tipo de movimiento -->
                <div class="mt-4 w-50 mx-auto">
                    <label for="tipoMovimiento" class="form-label fw-semibold text-secondary">
                        Tipo de movimiento
                    </label>
                    <select id="tipoMovimiento" class="form-select text-center border-primary shadow-sm">
                        <option value="Entrada">Entrada</option>
                        <option value="Salida">Salida</option>
                    </select>
                </div>

                <!-- Botón capturar (CORREGIDO) -->
                <button id="btnCapturar" 
                        class="btn btn-primary mt-4 px-4 py-2 shadow-sm fw-semibold">
                    <i class="fas fa-camera me-2"></i>Capturar Código QR
                </button>
            </div>

            <!-- Mensajes -->
            <div class="mb-4">
                <div id="mensajeExito" class="alert alert-success text-center d-none mb-3 shadow-sm"></div>
                <div id="mensajeError" class="alert alert-danger text-center d-none mb-3 shadow-sm"></div>
                <div id="mensajeVacio" class="alert alert-warning text-center d-none mb-3 shadow-sm">
                    <i class="fas fa-exclamation-circle me-2"></i>No hay ingresos registrados todavía.
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white p-4 rounded-4 shadow-sm">
                <h5 class="fw-semibold mb-3 text-secondary">
                    <i class="fas fa-list me-2"></i>Lista de Ingresos Recientes
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Funcionario</th>
                                <th>Cargo</th>
                                <th>Tipo Movimiento</th>
                                <th>Fecha Ingreso</th>
                            </tr>
                        </thead>
                        <tbody id="tablaIngresos">
                            <tr>
                                <td colspan="4" class="text-muted py-4">
                                    <div class="spinner-border text-primary me-2" role="status"></div>
                                    Cargando información...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Librería para la lectura de QR -->
<script src="https://unpkg.com/html5-qrcode"></script>

<!-- JavaScript compactado y limpio -->
<script>
document.addEventListener("DOMContentLoaded", () => {


    // REFERENCIAS A ELEMENTOS DEL DOM

    const tabla = document.getElementById("tablaIngresos");
    const msgErr = document.getElementById("mensajeError");
    const msgOk = document.getElementById("mensajeExito");
    const msgVacio = document.getElementById("mensajeVacio");
    const tipoMovimiento = document.getElementById("tipoMovimiento");
    const btn = document.getElementById("btnCapturar");

    // Instancia del lector QR
    const qrReader = new Html5Qrcode("qr-reader");

    // Evita que el mismo QR se lea varias veces seguidas
    let ultimaLectura = null;




    // Muestra alerta según si es éxito o error

    const mostrarMensaje = (ok, texto) => {
        msgOk.classList.toggle("d-none", !ok); // Oculta o muestra éxito
        msgErr.classList.toggle("d-none", ok); // Oculta o muestra error
        (ok ? msgOk : msgErr).textContent = texto; // Inserta el texto
    };



    // Obtiene los últimos ingresos desde el servidor y actualiza la tabla

    function cargarIngresos() {
        fetch("/SEGTRACK/App/Controller/ControladorIngreso.php")
            .then(r => r.json())
            .then(data => {

                tabla.innerHTML = ""; 

                const registros = data.data || []; // Si no hay data → arreglo vacío

                // Mostrar mensaje "no hay registros"
                msgVacio.classList.toggle("d-none", registros.length !== 0);

                // Inserta cada registro en la tabla
                registros.forEach(item => {
                    tabla.innerHTML += `
                        <tr>
                            <td>${item.NombreFuncionario}</td>
                            <td>${item.CargoFuncionario}</td>
                            <td>${item.TipoMovimiento}</td>
                            <td>${new Date(item.FechaIngreso).toLocaleString()}</td>
                        </tr>`;
                });
            });
    }

    // Carga inicial de los ingresos al abrir la vista
    cargarIngresos();


    // FUNCIÓN: onScanSuccess(qr)
    // Se activa cuando el lector QR detecta un código válido
    // Envía el QR al servidor y recibe confirmación

    function onScanSuccess(qr) {

        // Evita duplicados si el lector está activo
        if (qr === ultimaLectura) return;
        ultimaLectura = qr;
        setTimeout(() => ultimaLectura = null, 2000);

        // Envío del QR al controlador mediante POST
        fetch("/SEGTRACK/App/Controller/ControladorIngreso.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                qr_codigo: qr,
                tipoMovimiento: tipoMovimiento.value
            })
        })
        .then(r => r.json())
        .then(data => {

            // Muestra el mensaje devuelto por el controlador
            mostrarMensaje(data.success, data.message);

            // Si se registró bien → recargar tabla
            if (data.success) cargarIngresos();
        })
        .catch(() => 
            mostrarMensaje(false, "Error al enviar el código al servidor.")
        );

        // Detiene la cámara después de leer
        qrReader.stop();
    }


    // BOTÓN: Iniciar cámara y lector QR

    btn.addEventListener("click", () => {
        qrReader.start(
            { facingMode: "environment" }, // Cámara trasera
            { fps: 10, qrbox: 250 },       // Configuración del lector
            onScanSuccess                  // Función callback
        );
    });

});
</script>


<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
