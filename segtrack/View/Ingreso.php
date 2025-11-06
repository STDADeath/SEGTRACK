<?php require_once __DIR__ . '/../Plantilla/parte_superior.php'; ?>

<div class="container mt-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h4 class="text-center fw-bold text-primary mb-4">Control de Ingreso de Funcionarios</h4>

            <!-- Lector de QR -->
            <div class="text-center mb-4">
                <h5 class="fw-semibold mb-3">Escanear Código QR</h5>
                <div id="qr-reader" style="width: 320px; margin: 0 auto;"></div>
                <div id="resultado-qr" class="mt-3"></div>

                <!-- 📸 Botón para activar el lector -->
                <button id="btnCapturar" class="btn btn-success mt-3 px-4 py-2">
                    <i class="fas fa-camera"></i> Capturar Código QR
                </button>
            </div>

            <!-- Mensajes -->
            <div id="mensajeExito" class="alert alert-success text-center d-none"></div>
            <div id="mensajeError" class="alert alert-danger text-center d-none"></div>
            <div id="mensajeVacio" class="alert alert-warning text-center d-none">No hay ingresos registrados todavía.</div>

            <!-- Tabla -->
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
                        <tr>
                            <td colspan="4">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Librería del lector QR -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
// ajax.js
document.addEventListener("DOMContentLoaded", () => {
    const tablaIngresos = document.getElementById("tablaIngresos");
    const mensajeError = document.getElementById("mensajeError");
    const mensajeExito = document.getElementById("mensajeExito");
    const mensajeVacio = document.getElementById("mensajeVacio");
    const resultadoQR = document.getElementById("resultado-qr");
    const btnCapturar = document.getElementById("btnCapturar");

    // 📦 Cargar ingresos al iniciar
    function cargarIngresos() {
        fetch("/SEGTRACK/segtrack/Controller/Ingreso_Visitante/ControladorIngreso.php")
            .then(res => res.json())
            .then(data => {
                tablaIngresos.innerHTML = "";
                mensajeError.classList.add("d-none");
                mensajeVacio.classList.add("d-none");

                if (!data.success) {
                    mensajeError.textContent = "Error al cargar los datos.";
                    mensajeError.classList.remove("d-none");
                    return;
                }

                if (!data.data || data.data.length === 0) {
                    mensajeVacio.classList.remove("d-none");
                    return;
                }

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
            })
            .catch(error => {
                console.error("Error en fetch:", error);
                tablaIngresos.innerHTML = "";
                mensajeError.textContent = "No se pudo conectar con el servidor.";
                mensajeError.classList.remove("d-none");
            });
    }

    cargarIngresos(); // Llamada inicial al cargar la vista

    // 🎥 Acción al escanear un código QR correctamente
    function onScanSuccess(decodedText, decodedResult) {
        if (window.lastScanned === decodedText) return;
        window.lastScanned = decodedText;

        resultadoQR.innerHTML = `<p class="text-success fw-bold">Código detectado: ${decodedText}</p>`;

        // 📡 Enviar el código QR al backend
        fetch("/SEGTRACK/segtrack/Controller/Ingreso_Visitante/ControladorIngreso.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ qr_codigo: decodedText })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mensajeExito.textContent = `✅ ${data.message} (${data.data.nombre} - ${data.data.cargo})`;
                mensajeExito.classList.remove("d-none");
                mensajeError.classList.add("d-none");
                cargarIngresos();
            } else {
                mensajeError.textContent = "❌ " + data.message;
                mensajeError.classList.remove("d-none");
                mensajeExito.classList.add("d-none");
            }
        })
        .catch(err => {
            console.error("Error al enviar el código:", err);
            mensajeError.textContent = "Error al enviar el código al servidor.";
            mensajeError.classList.remove("d-none");
        });

        setTimeout(() => { window.lastScanned = null; }, 3000);
    }

    // 🚀 Configurar el lector QR (solo al presionar el botón)
    const html5QrCode = new Html5Qrcode("qr-reader");

    btnCapturar.addEventListener("click", async () => {
        resultadoQR.innerHTML = `<p class="text-info">📷 Activando cámara...</p>`;

        try {
            const devices = await Html5Qrcode.getCameras();
            if (devices && devices.length) {
                const cameraId = devices[0].id;
                await html5QrCode.start(cameraId, { fps: 10, qrbox: 250 }, (decodedText, decodedResult) => {
                    html5QrCode.stop();
                    onScanSuccess(decodedText, decodedResult);
                });
            } else {
                resultadoQR.innerHTML = `<p class="text-danger">No se encontró cámara disponible.</p>`;
            }
        } catch (err) {
            console.error("Error al iniciar cámara:", err);
            resultadoQR.innerHTML = `<p class="text-danger">Error al acceder a la cámara.</p>`;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>
