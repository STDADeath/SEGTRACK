<<<<<<< HEAD
<?php require_once __DIR__ . '/../layouts/parte_superior.php'; ?>
=======
<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)

<div class="container py-5">
    <div class="card border-0 shadow-lg rounded-4 bg-light">
        <div class="card-body px-4 py-5">
            
<<<<<<< HEAD
            <!-- TÍTULO PRINCIPAL -->
=======
            <!-- Título principal -->
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
            <h4 class="text-center fw-bold text-primary mb-5">
                <i class="fas fa-id-card-alt me-2"></i>Control de Ingreso de Funcionarios
            </h4>

<<<<<<< HEAD
            <!-- SECCIÓN DE ESCANEO QR -->
=======
            <!-- Sección del lector QR -->
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
            <div class="text-center mb-5">
                <h5 class="fw-semibold mb-4 text-secondary">
                    <i class="fas fa-qrcode me-2"></i>Escanear Código QR
                </h5>

<<<<<<< HEAD
                <!-- CONTENEDOR DEL LECTOR QR -->
                <div class="d-flex justify-content-center mb-3">
                    <div id="qr-reader" style="width: 100%; max-width: 500px;"></div>
                </div>

                <!-- SELECCIÓN DEL TIPO DE MOVIMIENTO -->
=======
                <!-- Cámara -->
                <div class="d-flex justify-content-center mb-3">
                    <div id="qr-reader" class="shadow-sm rounded-3 bg-white" style="width: 300px;"></div>
                </div>

                <!-- Resultado -->
                <div id="resultado-qr" class="mt-3 text-muted small fst-italic"></div>

                <!-- Tipo de movimiento -->
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                <div class="mt-4 w-50 mx-auto">
                    <label for="tipoMovimiento" class="form-label fw-semibold text-secondary">
                        Tipo de movimiento
                    </label>
                    <select id="tipoMovimiento" class="form-select text-center border-primary shadow-sm">
                        <option value="Entrada">Entrada</option>
                        <option value="Salida">Salida</option>
                    </select>
                </div>

<<<<<<< HEAD
                <!-- BOTÓN PARA ACTIVAR CÁMARA -->
                <button id="btnCapturar" class="btn btn-primary mt-4 px-4 py-2 shadow-sm fw-semibold">
=======
                <!-- Botón capturar -->
                <button id="btnCapturar" 
                        class="btn btn-success mt-4 px-4 py-2 shadow-sm fw-semibold">
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                    <i class="fas fa-camera me-2"></i>Capturar Código QR
                </button>
            </div>

<<<<<<< HEAD
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
                        <i class="fas fa-list me-2"></i>Lista de Ingresos Recientes
                    </h5>

                    <!-- BOTÓN PDF -->
                 <a href="/SEGTRACK/App/Controller/ControladorIngresoPDF.php?accion=pdf" 
                       target="_blank" 
                       class="btn btn-danger shadow-sm fw-semibold">
                        <i class="fas fa-file-pdf me-2"></i>Descargar PDF
                    </a>
                </div>

                <!-- TABLA -->
                <div class="table-responsive">
                    <table id="tablaIngresosDT" class="table table-hover align-middle text-center mb-0">
=======
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
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Funcionario</th>
                                <th>Cargo</th>
                                <th>Tipo Movimiento</th>
                                <th>Fecha Ingreso</th>
                            </tr>
                        </thead>
<<<<<<< HEAD
                        <tbody></tbody>
=======
                        <tbody id="tablaIngresos">
                            <tr>
                                <td colspan="4" class="text-muted py-4">
                                    <div class="spinner-border text-primary me-2" role="status"></div>
                                    Cargando información...
                                </td>
                            </tr>
                        </tbody>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<<<<<<< HEAD
<!-- LIBRERÍAS EXTERNAS -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap4.min.js"></script>

<!-- SCRIPT PERSONALIZADO -->
<script src="../../../Public/js/javascript/js/ValidacionIngreso.js"></script>

<?php require_once __DIR__ . '/../layouts/parte_inferior.php'; ?>
=======
<!-- Librería para la lectura de QR -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Referencias a los elementos del DOM
    const tablaIngresos = document.getElementById("tablaIngresos");
    const mensajeError = document.getElementById("mensajeError");
    const mensajeExito = document.getElementById("mensajeExito");
    const mensajeVacio = document.getElementById("mensajeVacio");
    const btnCapturar = document.getElementById("btnCapturar");
    const tipoMovimiento = document.getElementById("tipoMovimiento");

    // Instancia del lector QR
    const qrReader = new Html5Qrcode("qr-reader");

    let ultimaLectura = null; // Evita registrar el mismo QR varias veces seguidas

    // Función para obtener registros de ingreso desde el servidor
    function cargarIngresos() {
        fetch("/SEGTRACK/segtrack/Controller/Ingreso_Visitante/ControladorIngreso.php")
            .then(res => res.json())
            .then(data => {
                tablaIngresos.innerHTML = "";
                mensajeVacio.classList.add("d-none");

                // Si no hay datos, mostramos mensaje de lista vacía
                if (!data.data || data.data.length === 0) {
                    mensajeVacio.classList.remove("d-none");
                    return;
                }

                // Se recorren los registros para mostrarlos en la tabla
                data.data.forEach(ingreso => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${ingreso.NombreFuncionario}</td>
                        <td>${ingreso.CargoFuncionario}</td>
                        <td>${ingreso.TipoMovimiento}</td>
                        <td>${new Date(ingreso.FechaIngreso).toLocaleString()}</td>
                    `;
                    tablaIngresos.appendChild(row);
                });
            });
    }

    // Llamamos a la función para cargar la tabla inicialmente
    cargarIngresos();

    // Función que se ejecuta cuando se detecta un QR correctamente
    function onScanSuccess(qr) {

        // Evita registrar repetido si el lector sigue activo
        if (qr === ultimaLectura) return;
        ultimaLectura = qr;
        setTimeout(() => { ultimaLectura = null; }, 2000);

        // Envío del código QR al servidor mediante POST
        fetch("/SEGTRACK/segtrack/Controller/Ingreso_Visitante/ControladorIngreso.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                qr_codigo: qr,
                tipoMovimiento: tipoMovimiento.value // Entrada o Salida
            })
        })
        .then(res => res.json())
        .then(data => {
            // Si el servidor respondió con éxito
            if (data.success) {
                mensajeExito.textContent = data.message;
                mensajeExito.classList.remove("d-none");
                mensajeError.classList.add("d-none");
                cargarIngresos(); // Recargo la tabla
            } else {
                // Si hubo un error (ej: QR no registrado)
                mensajeError.textContent = data.message;
                mensajeError.classList.remove("d-none");
                mensajeExito.classList.add("d-none");
            }
        })
        .catch(() => {
            mensajeError.textContent = "Error al enviar el código al servidor.";
            mensajeError.classList.remove("d-none");
        });

        // Luego de leer, se detiene la cámara
        qrReader.stop();
    }

    // Al hacer clic en Capturar, iniciamos la cámara y el lector QR
    btnCapturar.addEventListener("click", async () => {
        qrReader.start(
            { facingMode: "environment" }, // Usa cámara trasera si está disponible
            { fps: 10, qrbox: 250 },
            onScanSuccess // Callback cuando se detecta un QR
        );
    });
});
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
>>>>>>> f5d2cb7 (Modificación de la estructura de carpetas del proyecto)
