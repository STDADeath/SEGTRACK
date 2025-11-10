<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container mt-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h4 class="text-center fw-bold text-primary mb-4">Control de Ingreso de Funcionarios</h4>

            <!-- **Sección del lector QR-->
            <div class="text-center mb-4">
                <h5 class="fw-semibold mb-3">Escanear Código QR</h5>

                <!-- Contenedor donde se mostrará la cámara -->
                <div id="qr-reader" style="width: 320px; margin: 0 auto;"></div>

                <div id="resultado-qr" class="mt-3"></div>

                <!-- Selector para elegir si el registro será Entrada o Salida -->
                <select id="tipoMovimiento" class="form-select w-50 mx-auto mt-3">
                    <option value="Entrada">Entrada</option>
                    <option value="Salida">Salida</option>
                </select>

                <!-- Botón para activar la cámara -->
                <button id="btnCapturar" class="btn btn-success mt-3 px-4 py-2">
                    <i class="fas fa-camera"></i> Capturar Código QR
                </button>
            </div>

            <!-- Mensajes dinámicos (se muestran según las acciones) -->
            <div id="mensajeExito" class="alert alert-success text-center d-none"></div>
            <div id="mensajeError" class="alert alert-danger text-center d-none"></div>
            <div id="mensajeVacio" class="alert alert-warning text-center d-none">No hay ingresos registrados todavía.</div>

            <!-- Tabla de ingresos -->
            <h5 class="mb-3 fw-semibold mt-5">Lista de Ingresos Recientes</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>Funcionario</th>
                            <th>Cargo</th>
                            <th>Tipo Movimiento</th>
                            <th>Fecha Ingreso</th>
                        </tr>
                    </thead>
                    <tbody id="tablaIngresos">
                        <!-- Se carga dinámicamente -->
                        <tr>
                            <td colspan="4">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
