const AppDev = {
    table: null,
    qrReader: null,
    ultimaLectura: null,
    escaneando: false,
    tipoMovimiento: null,
    btnCapturar: null,
    mensajeExito: null,
    mensajeError: null,
    _timerCard: null,

    config: {
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngresoDispositivo.php",
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

    const exito = document.getElementById("mensajeExito");
    const error = document.getElementById("mensajeError");

    if (!exito || !error) return;

    exito.classList.toggle("d-none", !esExito);
    error.classList.toggle("d-none", esExito);
    (esExito ? exito : error).textContent = texto;

    setTimeout(() => {
        exito.classList.add("d-none");
        error.classList.add("d-none");
    }, 5000);
}


// ========================================
// MOSTRAR CARD CON FOTO Y DATOS DISPOSITIVO
// ========================================

function mostrarDispositivo(data) {

    const card    = document.getElementById("cardDispositivo");
    const foto    = document.getElementById("fotoFuncionario");
    const nombre  = document.getElementById("nombreFuncionario");
    const cargo   = document.getElementById("cargoFuncionario");
    const tipo    = document.getElementById("tipoDispositivo");
    const marca   = document.getElementById("marcaDispositivo");
    const serial  = document.getElementById("serialDispositivo");
    const tipomov = document.getElementById("tipoMovimiento2");
    const fecha   = document.getElementById("fechaDispositivo");

    if (!card) return;

    // Foto del funcionario
    foto.onerror = () => { foto.src = AppDev.config.avatarDefault; };
    foto.src = (data.foto && data.foto.trim() !== "" && data.foto !== "NULL")
        ? AppDev.config.rutaFotos + data.foto.trim()
        : AppDev.config.avatarDefault;

    nombre.textContent = data.funcionario ?? "Sin asignar";
    cargo.textContent  = data.cargo       ?? "—";
    tipo.textContent   = data.tipo        ?? "—";
    marca.textContent  = data.marca       ?? "—";
    serial.textContent = data.serial      ?? "—";
    fecha.textContent  = data.fecha
        ? new Date(data.fecha).toLocaleString('es-ES')
        : "";

    tipomov.textContent = data.tipo_mov ?? "—";
    tipomov.className   = "badge fs-5 px-4 py-2 mb-2 " +
        (data.tipo_mov === "Entrada" ? "bg-success" : "bg-danger");

    // Limpiar timer anterior
    if (AppDev._timerCard) clearTimeout(AppDev._timerCard);

    // Mostrar con fade in
    card.classList.remove("d-none");
    card.style.transition = "opacity 0.4s ease";
    card.style.opacity    = "0";
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { card.style.opacity = "1"; });
    });

    // Ocultar con fade out
    AppDev._timerCard = setTimeout(() => {
        card.style.opacity = "0";
        setTimeout(() => card.classList.add("d-none"), 400);
    }, AppDev.config.tiempoCard);
}


// ========================================
// ENVIAR QR AL SERVIDOR
// ========================================

async function enviarQr(qr, tipo) {

    if (AppDev.btnCapturar) AppDev.btnCapturar.disabled = true;

    try {

        const res = await fetch(AppDev.config.urlControlador, {
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
            if (AppDev.table) AppDev.table.ajax.reload(null, false);
            if (data.data)    mostrarDispositivo(data.data);
        }

    } catch (e) {

        console.error("Error enviarQr:", e);
        mostrarMensaje(false, "Error de conexión.");

    } finally {

        if (AppDev.btnCapturar) AppDev.btnCapturar.disabled = false;

    }
}


// ========================================
// CUANDO SE DETECTA UN QR
// ========================================

function onScanQR(qr) {

    qr = qr.trim();

    if (!qr || AppDev.escaneando || qr === AppDev.ultimaLectura) return;

    AppDev.escaneando    = true;
    AppDev.ultimaLectura = qr;

    enviarQr(qr, AppDev.tipoMovimiento.value).finally(() => {
        setTimeout(() => {
            AppDev.escaneando    = false;
            AppDev.ultimaLectura = null;
        }, AppDev.config.bloqueoQRms);
    });
}


// ========================================
// INICIAR CÁMARA
// ========================================

async function iniciarCamara() {

    if (AppDev.qrReader) return;

    AppDev.qrReader = new Html5Qrcode("qr-reader");

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

        await AppDev.qrReader.start(
            cameraId,
            {
                fps: AppDev.config.fps,
                qrbox: {
                    width:  AppDev.config.qrboxSize,
                    height: AppDev.config.qrboxSize
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
// INICIALIZACIÓN
// ========================================

document.addEventListener("DOMContentLoaded", () => {

    AppDev.tipoMovimiento = document.getElementById("tipoMovimiento");
    AppDev.btnCapturar    = document.getElementById("btnCapturar");
    AppDev.mensajeExito   = document.getElementById("mensajeExito");
    AppDev.mensajeError   = document.getElementById("mensajeError");

    AppDev.table = $("#tablaDispositivosDT").DataTable({

        ajax: {
            url: AppDev.config.urlControlador,
            dataSrc: json => json.data || []
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

    if (AppDev.btnCapturar)
        AppDev.btnCapturar.addEventListener("click", iniciarCamara);

});