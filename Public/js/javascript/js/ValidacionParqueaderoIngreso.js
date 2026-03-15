const App = {
    table: null,
    qrReader: null,
    ultimaLectura: null,
    escaneando: false,
    tipoMovimiento: null,
    btnCapturar: null,
    btnDescargarPDF: null,
    mensajeExito: null,
    mensajeError: null,
    infoVehiculo: null,

    config: {
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngresoParqueadero.php",
        urlPDF:         "/SEGTRACK/App/Controller/ParqueaderoIngresoPDF.php?accion=pdf",
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
// TARJETA DE INFO DEL VEHÍCULO ESCANEADO
// ========================================

function mostrarInfoVehiculo(data) {

    if (!App.infoVehiculo || !data) return;

    document.getElementById("infoDueno").textContent   = data.dueno         || "No registrado";
    document.getElementById("infoPlaca").textContent   = data.placa         || "—";
    document.getElementById("infoTipo").textContent    = data.tipo          || "—";
    document.getElementById("infoEspacio").textContent = data.numeroEspacio || "Sin asignar";

    App.infoVehiculo.classList.remove("d-none");
}

function ocultarInfoVehiculo() {
    if (App.infoVehiculo) App.infoVehiculo.classList.add("d-none");
}


// ========================================
// ENVIAR QR AL SERVIDOR
// ========================================

async function enviarQr(qr, tipo) {

    if (App.btnCapturar) App.btnCapturar.disabled = true;

    try {

        const res  = await fetch(App.config.urlControlador, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ qr_codigo: qr, tipoMovimiento: tipo })
        });

        const data = await res.json();

        mostrarMensaje(data.success, data.message);

        if (data.success) {
            mostrarInfoVehiculo(data.data);
            if (App.table) App.table.ajax.reload(null, false);
        } else {
            ocultarInfoVehiculo();
        }

    } catch (e) {
        console.error(e);
        mostrarMensaje(false, "Error de conexión.");
        ocultarInfoVehiculo();
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

    App.escaneando    = true;
    App.ultimaLectura = qr;

    enviarQr(qr, App.tipoMovimiento.value).finally(() => {
        setTimeout(() => {
            App.escaneando    = false;
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

        const droidcam = devices.find(d => d.label.toLowerCase().includes("droid"));
        const cameraId = droidcam ? droidcam.id : devices[0].id;

        await App.qrReader.start(
            cameraId,
            { fps: App.config.fps, qrbox: { width: App.config.qrboxSize, height: App.config.qrboxSize } },
            onScanQR,
            () => {}
        );

    } catch (e) {
        console.error("Error iniciando cámara:", e);
        mostrarMensaje(false, "No se pudo iniciar la cámara.");
    }
}


// ========================================
// DESCARGAR PDF
// ========================================

async function descargarPDF() {

    try {

        const res = await fetch(App.config.urlPDF);
        if (!res.ok) throw new Error();

        const blob = await res.blob();
        const url  = window.URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = `Vehiculos_${new Date().toISOString().slice(0, 10)}.pdf`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        mostrarMensaje(true, "PDF descargado correctamente.");

    } catch (e) {
        console.error(e);
        mostrarMensaje(false, "No se pudo descargar el PDF.");
    }
}


// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener("DOMContentLoaded", () => {

    App.tipoMovimiento  = document.getElementById("tipoMovimiento");
    App.btnCapturar     = document.getElementById("btnCapturar");
    App.btnDescargarPDF = document.getElementById("btnDescargarPDF");
    App.mensajeExito    = document.getElementById("mensajeExito");
    App.mensajeError    = document.getElementById("mensajeError");
    App.infoVehiculo    = document.getElementById("infoVehiculo");

    App.table = $("#tablaParqueaderoDT").DataTable({
        ajax: {
            url: App.config.urlControlador,
            dataSrc: json => json.data || []
        },
        columns: [
            { data: "QrVehiculo",          defaultContent: "—" },
            { data: "DuenoVehiculo",       defaultContent: "No registrado" },
            { data: "PlacaVehiculo",       defaultContent: "—" },
            { data: "TipoVehiculo",        defaultContent: "—" },
            { data: "DescripcionVehiculo", defaultContent: "—" },
            { data: "NumeroEspacio",       defaultContent: "Sin asignar" },
            { data: "TipoMovimiento",      defaultContent: "—" },
            {
                data: "FechaIngreso",
                defaultContent: "—",
                render: d => d ? new Date(d).toLocaleString("es-ES") : "—"
            }
        ],
        order: [[7, "desc"]],
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" }
    });

    if (App.btnCapturar)     App.btnCapturar.addEventListener("click", iniciarCamara);
    if (App.btnDescargarPDF) App.btnDescargarPDF.addEventListener("click", descargarPDF);
});