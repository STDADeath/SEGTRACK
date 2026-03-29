const AppPark = {
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
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngresoParqueadero.php",
        rutaFotos:      "/SEGTRACK/Public/",
        avatarDefault:  "/SEGTRACK/Public/img/avatar_default.png",
        fps:            10,
        qrboxSize:      250,
        bloqueoQRms:    1500,
        tiempoCard:     7000
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
// MOSTRAR CARD CON DATOS DEL VEHÍCULO
// ========================================

function mostrarVehiculo(data) {
    const card    = document.getElementById("cardVehiculo");
    const foto    = document.getElementById("fotoFuncionario");
    const nombre  = document.getElementById("nombreDueno");
    const tipo    = document.getElementById("tipoVehiculo");
    const placa   = document.getElementById("placaVehiculo");
    const desc    = document.getElementById("descripcionVehiculo");
    const espacio = document.getElementById("espacioVehiculo");
    const badge   = document.getElementById("badgeMovimiento");
    const fecha   = document.getElementById("fechaVehiculo");

    if (!card) return;

    foto.onerror = () => { foto.src = AppPark.config.avatarDefault; };
    foto.src = (data.foto && data.foto.trim() !== "" && data.foto !== "NULL")
        ? AppPark.config.rutaFotos + data.foto.trim()
        : AppPark.config.avatarDefault;

    console.log("FOTO recibida:", data.foto);
    console.log("RUTA final:", foto.src);

    nombre.textContent  = data.dueno         ?? "Sin asignar";
    tipo.textContent    = data.tipo          ?? "—";
    placa.textContent   = data.placa         ?? "—";
    desc.textContent    = data.descripcion   ?? "—";
    espacio.textContent = data.numeroEspacio ?? "Sin asignar";
    fecha.textContent   = data.fecha
        ? new Date(data.fecha).toLocaleString('es-ES')
        : "";

    badge.textContent = data.movimiento ?? "—";
    badge.className   = "badge fs-5 px-4 py-2 mb-2 " +
        (data.movimiento === "Entrada" ? "bg-success" : "bg-danger");

    if (AppPark._timerCard) clearTimeout(AppPark._timerCard);

    card.classList.remove("d-none");
    card.style.transition = "opacity 0.4s ease";
    card.style.opacity    = "0";
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { card.style.opacity = "1"; });
    });

    AppPark._timerCard = setTimeout(() => {
        card.style.opacity = "0";
        setTimeout(() => card.classList.add("d-none"), 400);
    }, AppPark.config.tiempoCard);
}

// ========================================
// ENVIAR QR AL SERVIDOR
// ========================================

async function enviarQr(qr, tipo) {
    if (AppPark.btnCapturar) AppPark.btnCapturar.disabled = true;
    try {
        const res  = await fetch(AppPark.config.urlControlador, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ qr_codigo: qr, tipoMovimiento: tipo })
        });
        const data = await res.json();

        console.log("RESPUESTA COMPLETA:", JSON.stringify(data));

        mostrarMensaje(data.success, data.message);
        if (data.success) {
            if (AppPark.table) AppPark.table.ajax.reload(null, false);
            if (data.data)     mostrarVehiculo(data.data);
        }
    } catch (e) {
        console.error("Error enviarQr:", e);
        mostrarMensaje(false, "Error de conexión.");
    } finally {
        if (AppPark.btnCapturar) AppPark.btnCapturar.disabled = false;
    }
}

// ========================================
// CUANDO SE DETECTA UN QR
// ========================================

function onScanQR(qr) {
    qr = qr.trim();
    if (!qr || AppPark.escaneando || qr === AppPark.ultimaLectura) return;
    AppPark.escaneando    = true;
    AppPark.ultimaLectura = qr;
    enviarQr(qr, AppPark.tipoMovimiento.value).finally(() => {
        setTimeout(() => {
            AppPark.escaneando    = false;
            AppPark.ultimaLectura = null;
        }, AppPark.config.bloqueoQRms);
    });
}

// ========================================
// ACTUALIZAR BOTÓN SEGÚN ESTADO CÁMARA
// ========================================

function actualizarBoton(camaraActiva) {
    const btn = AppPark.btnCapturar;
    if (!btn) return;
    if (camaraActiva) {
        btn.classList.replace("btn-primary", "btn-danger");
        btn.innerHTML = '<i class="fas fa-times me-2"></i>Cancelar Cámara';
    } else {
        btn.classList.replace("btn-danger", "btn-primary");
        btn.innerHTML = '<i class="fas fa-camera me-2"></i>Capturar Código QR';
    }
}

// ========================================
// INICIAR CÁMARA
// ========================================

async function iniciarCamara() {
    if (AppPark.qrReader) return;

    // Paso 1: pedir permiso explícito primero
    try {
        await navigator.mediaDevices.getUserMedia({ video: true });
    } catch {
        mostrarMensaje(false, "Permiso de cámara denegado. Habilítalo en la barra del navegador.");
        return;
    }

    AppPark.qrReader = new Html5Qrcode("qr-reader");

    try {
        const devices = await Html5Qrcode.getCameras();
        if (!devices || devices.length === 0) {
            mostrarMensaje(false, "No se detectaron cámaras.");
            AppPark.qrReader.clear();
            AppPark.qrReader = null;
            return;
        }

        const camara =
            devices.find(d => d.label.toLowerCase().includes("back"))    ||
            devices.find(d => d.label.toLowerCase().includes("rear"))    ||
            devices.find(d => d.label.toLowerCase().includes("trasera")) ||
            devices.find(d => d.label.toLowerCase().includes("droid"))   ||
            devices[0];

        await AppPark.qrReader.start(
            camara.id,
            { fps: AppPark.config.fps, qrbox: { width: AppPark.config.qrboxSize, height: AppPark.config.qrboxSize } },
            onScanQR,
            () => {}
        );

        actualizarBoton(true);

    } catch (error) {
        console.error("Error iniciando por deviceId:", error);

        // Fallback con facingMode
        try {
            await AppPark.qrReader.start(
                { facingMode: "environment" },
                { fps: AppPark.config.fps, qrbox: { width: AppPark.config.qrboxSize, height: AppPark.config.qrboxSize } },
                onScanQR,
                () => {}
            );

            actualizarBoton(true);

        } catch (error2) {
            console.error("Fallback también falló:", error2);
            mostrarMensaje(false, "No se pudo iniciar la cámara: " + error2.message);
            AppPark.qrReader.clear();
            AppPark.qrReader = null;
        }
    }
}

// ========================================
// DETENER CÁMARA
// ========================================

async function detenerCamara() {
    if (!AppPark.qrReader) return;
    try {
        await AppPark.qrReader.stop();
        AppPark.qrReader.clear();
    } catch (e) {
        console.error("Error deteniendo cámara:", e);
    } finally {
        AppPark.qrReader      = null;
        AppPark.escaneando    = false;
        AppPark.ultimaLectura = null;
        actualizarBoton(false);
    }
}

// ========================================
// ALTERNAR CÁMARA (un solo botón)
// ========================================

async function toggleCamara() {
    if (AppPark.qrReader) {
        await detenerCamara();
    } else {
        await iniciarCamara();
    }
}

// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener("DOMContentLoaded", () => {
    AppPark.tipoMovimiento = document.getElementById("tipoMovimiento");
    AppPark.btnCapturar    = document.getElementById("btnCapturar");
    AppPark.mensajeExito   = document.getElementById("mensajeExito");
    AppPark.mensajeError   = document.getElementById("mensajeError");

    AppPark.table = $("#tablaParqueaderoDT").DataTable({
        ajax: {
            url:     AppPark.config.urlControlador,
            dataSrc: json => json.data || []
        },
        columns: [
            { data: "PlacaVehiculo",  defaultContent: "—" },
            { data: "TipoVehiculo",   defaultContent: "—" },
            { data: "DuenoVehiculo",  defaultContent: "No registrado" },
            { data: "NumeroEspacio",  defaultContent: "Sin asignar" },
            { data: "TipoMovimiento", defaultContent: "—" },
            {
                data: "FechaIngreso",
                defaultContent: "—",
                render: d => d ? new Date(d).toLocaleString('es-ES') : "—"
            }
        ],
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
        order: [[5, 'desc']]
    });

    if (AppPark.btnCapturar)
        AppPark.btnCapturar.addEventListener("click", toggleCamara); // ← CAMBIADO
});