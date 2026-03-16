// ========================================
// CONTROL DE DISPOSITIVOS - SISTEMA SEGTRACK
// Lectura continua de QR
// Compatible con DroidCam, webcam y móvil
// ========================================

const AppDispositivo = {
    table: null,
    qrReader: null,
    ultimaLectura: null,
    escaneando: false,
    tipoMovimiento: null,
    btnCapturar: null,
    mensajeExito: null,
    mensajeError: null,

    config: {
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngresoDispositivo.php",
        fps: 10,
        qrboxSize: 250,
        bloqueoQRms: 1500
    }
};


// ========================================
// MENSAJES EN PANTALLA
// ========================================

function mostrarMensajeDispositivo(esExito, texto) {

    const { mensajeExito, mensajeError } = AppDispositivo;

    if (!mensajeExito || !mensajeError) return;

    mensajeExito.classList.toggle("d-none", !esExito);
    mensajeError.classList.toggle("d-none", esExito);

    (esExito ? mensajeExito : mensajeError).textContent = texto;

    setTimeout(() => {
        mensajeExito.classList.add("d-none");
        mensajeError.classList.add("d-none");
    }, 5000);

}


// ========================================
// ENVIAR QR AL SERVIDOR
// ========================================

async function enviarQrDispositivo(qr, tipo) {

    if (AppDispositivo.btnCapturar) AppDispositivo.btnCapturar.disabled = true;

    try {

        const res = await fetch(AppDispositivo.config.urlControlador, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                qr_codigo: qr,
                tipoMovimiento: tipo
            })
        });

        const data = await res.json();

        mostrarMensajeDispositivo(data.success, data.message);

        if (data.success && AppDispositivo.table) {
            AppDispositivo.table.ajax.reload(null, false);
        }

    } catch (e) {

        console.error(e);
        mostrarMensajeDispositivo(false, "Error de conexión.");

    } finally {

        if (AppDispositivo.btnCapturar) AppDispositivo.btnCapturar.disabled = false;

    }

}


// ========================================
// CUANDO SE DETECTA UN QR
// ========================================

function onScanQRDispositivo(qr) {

    qr = qr.trim();

    if (!qr || AppDispositivo.escaneando || qr === AppDispositivo.ultimaLectura) return;

    AppDispositivo.escaneando    = true;
    AppDispositivo.ultimaLectura = qr;

    enviarQrDispositivo(qr, AppDispositivo.tipoMovimiento.value).finally(() => {

        setTimeout(() => {

            AppDispositivo.escaneando    = false;
            AppDispositivo.ultimaLectura = null;

        }, AppDispositivo.config.bloqueoQRms);

    });

}


// ========================================
// INICIAR CÁMARA
// Compatible con DroidCam y cualquier webcam
// ========================================

async function iniciarCamaraDispositivo() {

    if (AppDispositivo.qrReader) return;

    AppDispositivo.qrReader = new Html5Qrcode("qr-reader");

    try {

        const devices = await Html5Qrcode.getCameras();

        if (!devices || devices.length === 0) {
            mostrarMensajeDispositivo(false, "No se detectaron cámaras.");
            return;
        }

        console.log("Cámaras detectadas:", devices);

        let cameraId = null;

        // Buscar DroidCam primero
        const droidcam = devices.find(device =>
            device.label.toLowerCase().includes("droid")
        );

        if (droidcam) {

            cameraId = droidcam.id;
            console.log("Usando DroidCam:", droidcam.label);

        } else {

            cameraId = devices[0].id;
            console.log("Usando cámara:", devices[0].label);

        }

        await AppDispositivo.qrReader.start(
            cameraId,
            {
                fps: AppDispositivo.config.fps,
                qrbox: {
                    width: AppDispositivo.config.qrboxSize,
                    height: AppDispositivo.config.qrboxSize
                }
            },
            onScanQRDispositivo,
            error => {
                // Errores normales de lectura (ignorar)
            }
        );

    } catch (error) {

        console.error("Error iniciando cámara:", error);
        mostrarMensajeDispositivo(false, "No se pudo iniciar la cámara.");

    }

}


// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener("DOMContentLoaded", () => {

    AppDispositivo.tipoMovimiento = document.getElementById("tipoMovimiento");
    AppDispositivo.btnCapturar    = document.getElementById("btnCapturar");
    AppDispositivo.mensajeExito   = document.getElementById("mensajeExito");
    AppDispositivo.mensajeError   = document.getElementById("mensajeError");


    // ========================================
    // TABLA DE MOVIMIENTOS DE DISPOSITIVOS
    // ========================================

    AppDispositivo.table = $("#tablaDispositivosDT").DataTable({

        ajax: {
            url: AppDispositivo.config.urlControlador,
            dataSrc: function (json) {
                return json.data || [];
            }
        },

        columns: [
            { data: "TipoDispositivo"   },
            { data: "MarcaDispositivo"  },
            { data: "NumeroSerial"      },
            { data: "NombreFuncionario" },
            { data: "TipoMovimiento"    },
            {
                data: "FechaIngreso",
                render: d => new Date(d).toLocaleString('es-ES')
            }
        ],

        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },

        order: [[5, 'desc']]

    });


    // ========================================
    // EVENTOS
    // ========================================

    if (AppDispositivo.btnCapturar)
        AppDispositivo.btnCapturar.addEventListener("click", iniciarCamaraDispositivo);

});