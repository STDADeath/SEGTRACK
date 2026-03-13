// ========================================
// CONTROL DE DISPOSITIVOS - SISTEMA SEGTRACK
// Lectura continua de QR
// Compatible con DroidCam, webcam y móvil
// ========================================

const App = {
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

function mostrarMensaje(esExito, texto) {

    const { mensajeExito, mensajeError } = App;

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

async function enviarQr(qr, tipo) {

    if (App.btnCapturar) App.btnCapturar.disabled = true;

    try {

        const res = await fetch(App.config.urlControlador, {
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

        mostrarMensaje(data.success, data.message);

        if (data.success && App.table) {
            App.table.ajax.reload(null, false);
        }

    } catch (e) {

        console.error(e);
        mostrarMensaje(false, "Error de conexión.");

    } finally {

        if (App.btnCapturar) App.btnCapturar.disabled = false;

    }

}


// ========================================
// CUANDO SE DETECTA UN QR
// ========================================

function onScanQR(qr) {

    qr = qr.trim();

    if (!qr || App.escaneando || qr === App.ultimaLectura) return;

    App.escaneando = true;
    App.ultimaLectura = qr;

    enviarQr(qr, App.tipoMovimiento.value).finally(() => {

        setTimeout(() => {

            App.escaneando = false;
            App.ultimaLectura = null;

        }, App.config.bloqueoQRms);

    });

}


// ========================================
// INICIAR CÁMARA
// Compatible con DroidCam y cualquier webcam
// ========================================

async function iniciarCamara() {

    if (App.qrReader) return;

    App.qrReader = new Html5Qrcode("qr-reader");

    try {

        const devices = await Html5Qrcode.getCameras();

        if (!devices || devices.length === 0) {
            mostrarMensaje(false, "No se detectaron cámaras.");
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

        await App.qrReader.start(
            cameraId,
            {
                fps: App.config.fps,
                qrbox: {
                    width: App.config.qrboxSize,
                    height: App.config.qrboxSize
                }
            },
            onScanQR,
            error => {
                // Errores normales de lectura (ignorar)
            }
        );

    } catch (error) {

        console.error("Error iniciando cámara:", error);
        mostrarMensaje(false, "No se pudo iniciar la cámara.");

    }

}


// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener("DOMContentLoaded", () => {

    App.tipoMovimiento = document.getElementById("tipoMovimiento");
    App.btnCapturar    = document.getElementById("btnCapturar");
    App.mensajeExito   = document.getElementById("mensajeExito");
    App.mensajeError   = document.getElementById("mensajeError");


    // ========================================
    // TABLA DE MOVIMIENTOS DE DISPOSITIVOS
    // ========================================

    App.table = $("#tablaDispositivosDT").DataTable({

        ajax: {
            url: App.config.urlControlador,
            dataSrc: function (json) {
                return json.data || [];
            }
        },

        columns: [
            { data: "TipoDispositivo"  },
            { data: "MarcaDispositivo" },
            { data: "NumeroSerial"     },
            { data: "NombreFuncionario"},
            { data: "TipoMovimiento"   },
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

    if (App.btnCapturar)
        App.btnCapturar.addEventListener("click", iniciarCamara);

});