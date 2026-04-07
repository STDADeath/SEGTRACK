const AppParqueadero = {
    table: null,
    qrReader: null,
    ultimaLectura: null,
    escaneando: false,
    tipoMovimiento: null,
    btnCapturar: null,
    mensajeExito: null,
    mensajeError: null,
    _timerCard: null,
    _timerCamara: null,
    _timerCountdown: null,

    config: {
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngresoParqueadero.php",
        rutaFotos: "/SEGTRACK/Public/",
        avatarDefault: "/SEGTRACK/Public/img/avatar_default.png",
        fps: 10,
        qrboxSize: 250,
        bloqueoQRms: 1500,
        tiempoCard: 7000,
        tiempoCamara: 5000
    }
};

// ========================================
// MENSAJES EN PANTALLA
// ========================================

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

// ========================================
// ALERTAS ESPECÍFICAS DE PARQUEADERO
// ========================================

function mostrarAlertaInactivo() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Acceso denegado',
            text: 'Este vehículo está inactivo y no tiene permiso de acceso.',
            confirmButtonColor: '#e74a3b',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('⛔ Vehículo inactivo. No tiene permiso de acceso.');
    }
    mostrarMensaje(false, '⛔ Vehículo inactivo. No tiene permiso de acceso.');
}

function mostrarAlertaSinEntrada() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Salida no permitida',
            text: 'Este vehículo no tiene una entrada activa. Debe registrar entrada primero.',
            confirmButtonColor: '#f6c23e',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('⚠️ El vehículo debe registrar una Entrada antes de salir.');
    }
    mostrarMensaje(false, '⚠️ Debe registrar una Entrada antes de poder registrar una Salida.');
}

function mostrarAlertaEntradaDuplicada() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Entrada duplicada',
            text: 'Este vehículo ya tiene una entrada activa. Debe registrar una Salida primero.',
            confirmButtonColor: '#36b9cc',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('ℹ️ El vehículo ya tiene una Entrada activa. Registre una Salida primero.');
    }
    mostrarMensaje(false, 'ℹ️ El vehículo ya tiene una Entrada activa. Registre una Salida primero.');
}

function mostrarAlertaSinEspacio() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Parqueadero lleno',
            text: 'No hay espacios de parqueadero disponibles en este momento.',
            confirmButtonColor: '#e74a3b',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('🅿️ No hay espacios de parqueadero disponibles.');
    }
    mostrarMensaje(false, '🅿️ No hay espacios de parqueadero disponibles.');
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

    foto.onerror = () => { foto.src = AppParqueadero.config.avatarDefault; };
    foto.src = (data.foto && data.foto.trim() !== "" && data.foto !== "NULL")
        ? AppParqueadero.config.rutaFotos + data.foto.trim()
        : AppParqueadero.config.avatarDefault;

    nombre.textContent  = data.dueno ?? "Sin asignar";
    tipo.textContent    = data.tipo ?? "—";
    placa.textContent   = data.placa ?? "—";
    desc.textContent    = data.descripcion ?? "—";
    espacio.textContent = data.numeroEspacio ?? "Sin asignar";
    fecha.textContent   = data.fecha ? new Date(data.fecha).toLocaleString('es-ES') : "";

    badge.textContent = data.movimiento ?? "—";
    badge.className   = "badge fs-5 px-4 py-2 mb-2 " +
        (data.movimiento === "Entrada" ? "bg-success" : "bg-danger");

    if (AppParqueadero._timerCard) clearTimeout(AppParqueadero._timerCard);

    card.classList.remove("d-none");
    card.style.transition = "opacity 0.4s ease";
    card.style.opacity = "0";
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { card.style.opacity = "1"; });
    });

    AppParqueadero._timerCard = setTimeout(() => {
        card.style.opacity = "0";
        setTimeout(() => card.classList.add("d-none"), 400);
    }, AppParqueadero.config.tiempoCard);
}

// ========================================
// ENVIAR QR AL SERVIDOR (CON CÓDIGOS DE ERROR)
// ========================================

async function enviarQr(qr, tipo) {
    if (AppParqueadero.btnCapturar) AppParqueadero.btnCapturar.disabled = true;
    
    try {
        const res = await fetch(AppParqueadero.config.urlControlador, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ qr_codigo: qr, tipoMovimiento: tipo })
        });
        
        const data = await res.json();
        
        // Manejar códigos de error específicos
        if (!data.success) {
            switch(data.codigo) {
                case 'inactivo':
                    mostrarAlertaInactivo();
                    return;
                case 'sin_entrada_previa':
                    mostrarAlertaSinEntrada();
                    return;
                case 'entrada_duplicada':
                    mostrarAlertaEntradaDuplicada();
                    return;
                case 'sin_espacio':
                    mostrarAlertaSinEspacio();
                    return;
                default:
                    mostrarMensaje(false, data.message);
            }
        } else {
            mostrarMensaje(true, data.message);
            if (AppParqueadero.table) AppParqueadero.table.ajax.reload(null, false);
            if (data.data) mostrarVehiculo(data.data);
        }
        
    } catch (e) {
        console.error("Error enviarQr:", e);
        mostrarMensaje(false, "Error de conexión.");
    } finally {
        if (AppParqueadero.btnCapturar) AppParqueadero.btnCapturar.disabled = false;
    }
}

// ========================================
// DETECCIÓN DE QR
// ========================================

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

// ========================================
// CONTROL DE CÁMARA CON COUNTDOWN
// ========================================

function actualizarBoton(camaraActiva) {
    const btn = AppParqueadero.btnCapturar;
    if (camaraActiva) {
        btn.classList.replace("btn-primary", "btn-danger");
        btn.innerHTML = '<i class="fas fa-times me-2"></i>Cancelar Cámara';
    } else {
        btn.classList.replace("btn-danger", "btn-primary");
        btn.innerHTML = '<i class="fas fa-camera me-2"></i>Capturar Código QR';
    }
}

function iniciarCountdown(segundos) {
    limpiarCountdown();
    let restantes = segundos;
    
    if (AppParqueadero.btnCapturar) {
        AppParqueadero.btnCapturar.innerHTML = `<i class="fas fa-times me-2"></i>Cancelar Cámara (${restantes}s)`;
    }
    
    AppParqueadero._timerCountdown = setInterval(() => {
        restantes--;
        if (restantes <= 0) {
            limpiarCountdown();
            detenerCamara();
            return;
        }
        if (AppParqueadero.btnCapturar) {
            AppParqueadero.btnCapturar.innerHTML = `<i class="fas fa-times me-2"></i>Cancelar Cámara (${restantes}s)`;
        }
    }, 1000);
}

function limpiarCountdown() {
    if (AppParqueadero._timerCountdown) {
        clearInterval(AppParqueadero._timerCountdown);
        AppParqueadero._timerCountdown = null;
    }
}

async function iniciarCamara() {
    if (AppParqueadero.qrReader) return;
    
    try {
        await navigator.mediaDevices.getUserMedia({ video: true });
    } catch {
        mostrarMensaje(false, "Permiso de cámara denegado.");
        return;
    }
    
    AppParqueadero.qrReader = new Html5Qrcode("qr-reader");
    
    try {
        const devices = await Html5Qrcode.getCameras();
        if (!devices || devices.length === 0) {
            mostrarMensaje(false, "No se detectaron cámaras.");
            AppParqueadero.qrReader.clear();
            AppParqueadero.qrReader = null;
            return;
        }
        
        const camara = devices.find(d => d.label.toLowerCase().includes("back")) ||
                      devices.find(d => d.label.toLowerCase().includes("rear")) ||
                      devices.find(d => d.label.toLowerCase().includes("trasera")) ||
                      devices[0];
        
        await AppParqueadero.qrReader.start(
            camara.id,
            { fps: AppParqueadero.config.fps, qrbox: { width: AppParqueadero.config.qrboxSize, height: AppParqueadero.config.qrboxSize } },
            onScanQR,
            () => {}
        );
        
        actualizarBoton(true);
        iniciarCountdown(20);
        
    } catch (error) {
        try {
            await AppParqueadero.qrReader.start(
                { facingMode: "environment" },
                { fps: AppParqueadero.config.fps, qrbox: { width: AppParqueadero.config.qrboxSize, height: AppParqueadero.config.qrboxSize } },
                onScanQR,
                () => {}
            );
            actualizarBoton(true);
            iniciarCountdown(20);
        } catch (error2) {
            mostrarMensaje(false, "No se pudo iniciar la cámara: " + error2.message);
            AppParqueadero.qrReader.clear();
            AppParqueadero.qrReader = null;
        }
    }
}

async function detenerCamara() {
    limpiarCountdown();
    if (!AppParqueadero.qrReader) return;
    
    try {
        await AppParqueadero.qrReader.stop();
        AppParqueadero.qrReader.clear();
    } catch (e) {
        console.error("Error deteniendo cámara:", e);
    } finally {
        AppParqueadero.qrReader = null;
        AppParqueadero.escaneando = false;
        AppParqueadero.ultimaLectura = null;
        actualizarBoton(false);
    }
}

async function toggleCamara() {
    if (AppParqueadero.qrReader) {
        await detenerCamara();
    } else {
        await iniciarCamara();
    }
}

// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener("DOMContentLoaded", () => {
    AppParqueadero.tipoMovimiento = document.getElementById("tipoMovimiento");
    AppParqueadero.btnCapturar = document.getElementById("btnCapturar");
    AppParqueadero.mensajeExito = document.getElementById("mensajeExito");
    AppParqueadero.mensajeError = document.getElementById("mensajeError");
    
    AppParqueadero.table = $("#tablaParqueaderoDT").DataTable({
        ajax: {
            url: AppParqueadero.config.urlControlador,
            dataSrc: json => json.data || []
        },
        columns: [
            { data: "PlacaVehiculo", defaultContent: "—" },
            { data: "TipoVehiculo", defaultContent: "—" },
            { data: "DuenoVehiculo", defaultContent: "No registrado" },
            { data: "NumeroEspacio", defaultContent: "Sin asignar" },
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
    
    if (AppParqueadero.btnCapturar)
        AppParqueadero.btnCapturar.addEventListener("click", toggleCamara);
});