const AppParqueadero = {
    table: null,
    qrReader: null,
    ultimaLectura: null,
    escaneando: false,
    tipoMovimiento: null,
    btnCapturar: null,
    mensajeExito: null,
    mensajeError: null,
    config: {
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngresoParqueadero.php",
        fps: 10,
        qrboxSize: 250,
        bloqueoQRms: 1500
    }
};

function mostrarMensaje(esExito, texto) {
    const { mensajeExito, mensajeError } = AppParqueadero;
    if (!mensajeExito || !mensajeError) return;
    mensajeExito.classList.toggle("d-none", !esExito);
    mensajeError.classList.toggle("d-none", esExito);
    (esExito ? mensajeExito : mensajeError).textContent = texto;
    setTimeout(() => {
        mensajeExito.classList.add("d-none");
        mensajeError.classList.add("d-none");
    }, 5000);
}

async function enviarQr(qr, tipo) {
    if (AppParqueadero.btnCapturar) AppParqueadero.btnCapturar.disabled = true;
    try {
        const res = await fetch(AppParqueadero.config.urlControlador, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ qr_codigo: qr, tipoMovimiento: tipo })
        });
        const data = await res.json();
        mostrarMensaje(data.success, data.message);
        if (data.success && AppParqueadero.table) AppParqueadero.table.ajax.reload(null, false);
    } catch (e) {
        mostrarMensaje(false, "Error de conexión.");
    } finally {
        if (AppParqueadero.btnCapturar) AppParqueadero.btnCapturar.disabled = false;
    }
}

function onScanQR(qr) {
    qr = qr.trim();
    if (!qr || AppParqueadero.escaneando || qr === AppParqueadero.ultimaLectura) return;
    AppParqueadero.escaneando = true;
    AppParqueadero.ultimaLectura = qr;
    enviarQr(qr, AppParqueadero.tipoMovimiento.value).finally(() => {
        setTimeout(() => {
            AppParqueadero.escaneando = false;
            AppParqueadero.ultimaLectura = null;
        }, AppParqueadero.config.bloqueoQRms);
    });
}

async function iniciarCamara() {
    if (AppParqueadero.qrReader) return;
    AppParqueadero.qrReader = new Html5Qrcode("qr-reader-parqueadero");
    try {
        await AppParqueadero.qrReader.start(
            { facingMode: "environment" },
            { fps: AppParqueadero.config.fps, qrbox: AppParqueadero.config.qrboxSize },
            onScanQR
        );
    } catch (e) {
        console.error("Error al iniciar cámara:", e);
        mostrarMensaje(false, "No se pudo iniciar la cámara.");
    }
}

document.addEventListener("DOMContentLoaded", () => {
    AppParqueadero.tipoMovimiento = document.getElementById("tipoMovimientoParqueadero");
    AppParqueadero.btnCapturar = document.getElementById("btnCapturarParqueadero");
    AppParqueadero.mensajeExito = document.getElementById("mensajeExitoParqueadero");
    AppParqueadero.mensajeError = document.getElementById("mensajeErrorParqueadero");

    AppParqueadero.table = $("#tablaParqueaderoDT").DataTable({
        ajax: {
            url: AppParqueadero.config.urlControlador,
            dataSrc: json => json.data || []
        },
        columns: [
            { data: "DescripcionVehiculo" },
            { data: "PlacaVehiculo" },
            { data: "TipoVehiculo" },
            { data: "TipoMovimiento" },
            { data: "FechaIngreso", render: d => new Date(d).toLocaleString('es-ES') }
        ],
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
        order: [[4, 'desc']]
    });

    if (AppParqueadero.btnCapturar) AppParqueadero.btnCapturar.addEventListener("click", iniciarCamara);
});
