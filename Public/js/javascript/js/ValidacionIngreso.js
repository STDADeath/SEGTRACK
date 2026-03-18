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
    _timerCard: null,

    config: {
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngreso.php",
        rutaFotos: "/SEGTRACK/Public/",
        avatarDefault: "/SEGTRACK/Public/img/avatar_default.png",
        fps: 10,
        qrboxSize: 250,
        bloqueoQRms: 1500,
        tiempoCard: 7000
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
// MOSTRAR CARD CON FOTO DEL FUNCIONARIO
// ========================================

function mostrarFuncionario(data) {

    const card   = document.getElementById("cardFuncionario");
    const foto   = document.getElementById("fotoFuncionario");
    const nombre = document.getElementById("nombreFuncionario");
    const cargo  = document.getElementById("cargoFuncionario");
    const tipo   = document.getElementById("tipoFuncionario");
    const fecha  = document.getElementById("fechaFuncionario");

    if (!card) return;

    // Foto: usa la ruta de la BD o avatar por defecto
    foto.onerror = () => { foto.src = App.config.avatarDefault; };
    foto.src = (data.foto && data.foto.trim() !== "" && data.foto !== "NULL")
        ? App.config.rutaFotos + data.foto.trim()
        : App.config.avatarDefault;

    nombre.textContent = data.nombre ?? "—";
    cargo.textContent  = data.cargo  ?? "—";
    fecha.textContent  = data.fecha
        ? new Date(data.fecha).toLocaleString('es-ES')
        : "";

    tipo.textContent = data.tipo ?? "—";
    tipo.className   = "badge fs-6 px-3 py-2 mb-2 " +
        (data.tipo === "Entrada" ? "bg-success" : "bg-danger");

    // Limpiar timer anterior
    if (App._timerCard) clearTimeout(App._timerCard);

    // Mostrar con fade in
    card.classList.remove("d-none");
    card.style.transition = "opacity 0.4s ease";
    card.style.opacity    = "0";
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { card.style.opacity = "1"; });
    });

    // Ocultar con fade out
    App._timerCard = setTimeout(() => {
        card.style.opacity = "0";
        setTimeout(() => card.classList.add("d-none"), 400);
    }, App.config.tiempoCard);
}


// ========================================
// ENVIAR QR AL SERVIDOR
// ========================================

async function enviarQr(qr, tipo) {

    if (App.btnCapturar) App.btnCapturar.disabled = true;

    try {

        const res = await fetch(App.config.urlControlador, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                qr_codigo: qr,
                tipoMovimiento: tipo
            })
        });

        const data = await res.json();

        mostrarMensaje(data.success, data.message);

        if (data.success) {
            if (App.table) App.table.ajax.reload(null, false);
            if (data.data)  mostrarFuncionario(data.data);
        }

    } catch (e) {

        console.error("Error enviarQr:", e);
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

        const droidcam = devices.find(d =>
            d.label.toLowerCase().includes("droid")
        );

        const cameraId = droidcam ? droidcam.id : devices[0].id;

        await App.qrReader.start(
            cameraId,
            {
                fps: App.config.fps,
                qrbox: {
                    width:  App.config.qrboxSize,
                    height: App.config.qrboxSize
                }
            },
            onScanQR,
            () => {}
        );

    } catch (error) {
        console.error("Error iniciando cámara:", error);
        mostrarMensaje(false, "No se pudo iniciar la cámara.");
    }
}


// ========================================
// DESCARGAR PDF
// ========================================

async function descargarPDF() {

    try {

        const res = await fetch('/SEGTRACK/App/Controller/IngresoPDFController.php?accion=pdf');
        if (!res.ok) throw new Error();

        const blob = await res.blob();
        const url  = window.URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = `Ingresos_${new Date().toISOString().slice(0, 10)}.pdf`;
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

    App.table = $("#tablaIngresosDT").DataTable({

        ajax: {
            url: App.config.urlControlador,
            dataSrc: json => json.data || []
        },

        columns: [
            { data: "NombreFuncionario" },
            { data: "CargoFuncionario"  },
            { data: "TipoMovimiento"    },
            {
                data: "FechaIngreso",
                render: d => new Date(d).toLocaleString('es-ES')
            }
        ],

        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },

        order: [[3, 'desc']]
    });

    if (App.btnCapturar)
        App.btnCapturar.addEventListener("click", iniciarCamara);

    if (App.btnDescargarPDF)
        App.btnDescargarPDF.addEventListener("click", descargarPDF);

});