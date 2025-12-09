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
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngresoParqueadero.php",
        fps: 10,
        qrboxSize: 250,
        bloqueoQRms: 1500
    }
};

function mostrarMensaje(esExito, texto) {
    const { mensajeExito, mensajeError } = App;
    mensajeExito.classList.toggle("d-none", !esExito);
    mensajeError.classList.toggle("d-none", esExito);
    (esExito ? mensajeExito : mensajeError).textContent = texto;

    setTimeout(() => {
        mensajeExito.classList.add("d-none");
        mensajeError.classList.add("d-none");
    }, 5000);
}

async function enviarQr(qr, tipo) {
    App.btnCapturar.disabled = true;

    try {
        const res = await fetch(App.config.urlControlador, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ qr_codigo: qr, tipoMovimiento: tipo })
        });

        const data = await res.json();
        mostrarMensaje(data.success, data.message);

        if (data.success) App.table.ajax.reload(null, false);

    } catch (e) {
        mostrarMensaje(false, "Error de conexión");
    }

    App.btnCapturar.disabled = false;
}

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

async function iniciarCamara() {
    if (App.qrReader) return;

    App.qrReader = new Html5Qrcode("qr-reader");

    try {
        await App.qrReader.start(
            { facingMode: "environment" },
            { fps: App.config.fps, qrbox: App.config.qrboxSize },
            onScanQR
        );
    } catch (e) {
        mostrarMensaje(false, "No se pudo iniciar la cámara");
    }
}

document.addEventListener("DOMContentLoaded", () => {

    App.tipoMovimiento = document.getElementById("tipoMovimiento");
    App.btnCapturar = document.getElementById("btnCapturar");
    App.mensajeExito = document.getElementById("mensajeExito");
    App.mensajeError = document.getElementById("mensajeError");

    App.table = $("#tablaParqueaderoDT").DataTable({
        ajax: {
            url: App.config.urlControlador,
            dataSrc: json => json.data || []
        },
        columns: [
            { data: "QrVehiculo" },
            { data: "PlacaVehiculo" },
            { data: "TipoVehiculo" },
            { data: "DescripcionVehiculo" },
            { data: "TipoMovimiento" },
            {
                data: "FechaIngreso",
                render: d => new Date(d).toLocaleString("es-ES")
            }
        ],
        order: [[5, "desc"]],
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" }
    });

    App.btnCapturar.addEventListener("click", iniciarCamara);
});
