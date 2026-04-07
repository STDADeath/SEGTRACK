const AppDispositivo = {
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
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngresoDispositivo.php",
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
// ALERTAS ESPECÍFICAS (igual que funcionarios)
// ========================================

function mostrarAlertaInactivo() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Acceso denegado',
            text: 'Este dispositivo está inactivo y no tiene permiso de acceso.',
            confirmButtonColor: '#e74a3b',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('⛔ Dispositivo inactivo. No tiene permiso de acceso.');
    }
    mostrarMensaje(false, '⛔ Dispositivo inactivo. No tiene permiso de acceso.');
}

function mostrarAlertaSinEntrada() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Salida no permitida',
            text: 'Este dispositivo no tiene una entrada activa. Debe registrar entrada primero.',
            confirmButtonColor: '#f6c23e',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('⚠️ El dispositivo debe registrar una Entrada antes de salir.');
    }
    mostrarMensaje(false, '⚠️ Debe registrar una Entrada antes de poder registrar una Salida.');
}

function mostrarAlertaEntradaDuplicada() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Entrada duplicada',
            text: 'Este dispositivo ya tiene una entrada activa. Debe registrar una Salida primero.',
            confirmButtonColor: '#36b9cc',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('ℹ️ El dispositivo ya tiene una Entrada activa. Registre una Salida primero.');
    }
    mostrarMensaje(false, 'ℹ️ El dispositivo ya tiene una Entrada activa. Registre una Salida primero.');
}

// ========================================
// MOSTRAR CARD CON DATOS DEL DISPOSITIVO
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

    foto.onerror = () => { foto.src = AppDispositivo.config.avatarDefault; };
    foto.src = (data.foto && data.foto.trim() !== "" && data.foto !== "NULL")
        ? AppDispositivo.config.rutaFotos + data.foto.trim()
        : AppDispositivo.config.avatarDefault;

    nombre.textContent = data.funcionario ?? "Sin asignar";
    cargo.textContent  = data.cargo ?? "—";
    tipo.textContent   = data.tipo ?? "—";
    marca.textContent  = data.marca ?? "—";
    serial.textContent = data.serial ?? "—";
    fecha.textContent  = data.fecha ? new Date(data.fecha).toLocaleString('es-ES') : "";
    tipomov.textContent = data.tipo_mov ?? "—";
    tipomov.className = "badge fs-5 px-4 py-2 mb-2 " + (data.tipo_mov === "Entrada" ? "bg-success" : "bg-danger");

    if (AppDispositivo._timerCard) clearTimeout(AppDispositivo._timerCard);

    card.classList.remove("d-none");
    card.style.transition = "opacity 0.4s ease";
    card.style.opacity = "0";
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { card.style.opacity = "1"; });
    });

    AppDispositivo._timerCard = setTimeout(() => {
        card.style.opacity = "0";
        setTimeout(() => card.classList.add("d-none"), 400);
    }, AppDispositivo.config.tiempoCard);
}

// ========================================
// ENVIAR QR AL SERVIDOR (CON CÓDIGOS DE ERROR)
// ========================================

async function enviarQr(qr, tipo) {
    if (AppDispositivo.btnCapturar) AppDispositivo.btnCapturar.disabled = true;
    
    try {
        const res = await fetch(AppDispositivo.config.urlControlador, {
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
                default:
                    mostrarMensaje(false, data.message);
            }
        } else {
            mostrarMensaje(true, data.message);
            if (AppDispositivo.table) AppDispositivo.table.ajax.reload(null, false);
            if (data.data) mostrarDispositivo(data.data);
        }
        
    } catch (e) {
        console.error("Error enviarQr:", e);
        mostrarMensaje(false, "Error de conexión.");
    } finally {
        if (AppDispositivo.btnCapturar) AppDispositivo.btnCapturar.disabled = false;
    }
}

// ========================================
// DETECCIÓN DE QR
// ========================================

function onScanQR(qr) {
    qr = qr.trim();
    if (!qr || AppDispositivo.escaneando || qr === AppDispositivo.ultimaLectura) return;
    
    AppDispositivo.escaneando = true;
    AppDispositivo.ultimaLectura = qr;
    
    enviarQr(qr, AppDispositivo.tipoMovimiento.value).finally(() => {
        setTimeout(() => {
            AppDispositivo.escaneando = false;
            AppDispositivo.ultimaLectura = null;
        }, AppDispositivo.config.bloqueoQRms);
    });
}

// ========================================
// CONTROL DE CÁMARA CON COUNTDOWN
// ========================================

function actualizarBoton(camaraActiva) {
    const btn = AppDispositivo.btnCapturar;
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
    
    if (AppDispositivo.btnCapturar) {
        AppDispositivo.btnCapturar.innerHTML = `<i class="fas fa-times me-2"></i>Cancelar Cámara (${restantes}s)`;
    }
    
    AppDispositivo._timerCountdown = setInterval(() => {
        restantes--;
        if (restantes <= 0) {
            limpiarCountdown();
            detenerCamara();
            return;
        }
        if (AppDispositivo.btnCapturar) {
            AppDispositivo.btnCapturar.innerHTML = `<i class="fas fa-times me-2"></i>Cancelar Cámara (${restantes}s)`;
        }
    }, 1000);
}

function limpiarCountdown() {
    if (AppDispositivo._timerCountdown) {
        clearInterval(AppDispositivo._timerCountdown);
        AppDispositivo._timerCountdown = null;
    }
}

async function iniciarCamara() {
    if (AppDispositivo.qrReader) return;
    
    try {
        await navigator.mediaDevices.getUserMedia({ video: true });
    } catch {
        mostrarMensaje(false, "Permiso de cámara denegado.");
        return;
    }
    
    AppDispositivo.qrReader = new Html5Qrcode("qr-reader");
    
    try {
        const devices = await Html5Qrcode.getCameras();
        if (!devices || devices.length === 0) {
            mostrarMensaje(false, "No se detectaron cámaras.");
            AppDispositivo.qrReader.clear();
            AppDispositivo.qrReader = null;
            return;
        }
        
        const camara = devices.find(d => d.label.toLowerCase().includes("back")) ||
                      devices.find(d => d.label.toLowerCase().includes("rear")) ||
                      devices.find(d => d.label.toLowerCase().includes("trasera")) ||
                      devices[0];
        
        await AppDispositivo.qrReader.start(
            camara.id,
            { fps: AppDispositivo.config.fps, qrbox: { width: AppDispositivo.config.qrboxSize, height: AppDispositivo.config.qrboxSize } },
            onScanQR,
            () => {}
        );
        
        actualizarBoton(true);
        iniciarCountdown(20);
        
    } catch (error) {
        try {
            await AppDispositivo.qrReader.start(
                { facingMode: "environment" },
                { fps: AppDispositivo.config.fps, qrbox: { width: AppDispositivo.config.qrboxSize, height: AppDispositivo.config.qrboxSize } },
                onScanQR,
                () => {}
            );
            actualizarBoton(true);
            iniciarCountdown(20);
        } catch (error2) {
            mostrarMensaje(false, "No se pudo iniciar la cámara: " + error2.message);
            AppDispositivo.qrReader.clear();
            AppDispositivo.qrReader = null;
        }
    }
}

async function detenerCamara() {
    limpiarCountdown();
    if (!AppDispositivo.qrReader) return;
    
    try {
        await AppDispositivo.qrReader.stop();
        AppDispositivo.qrReader.clear();
    } catch (e) {
        console.error("Error deteniendo cámara:", e);
    } finally {
        AppDispositivo.qrReader = null;
        AppDispositivo.escaneando = false;
        AppDispositivo.ultimaLectura = null;
        actualizarBoton(false);
    }
}

async function toggleCamara() {
    if (AppDispositivo.qrReader) {
        await detenerCamara();
    } else {
        await iniciarCamara();
    }
}

// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener("DOMContentLoaded", () => {
    AppDispositivo.tipoMovimiento = document.getElementById("tipoMovimiento");
    AppDispositivo.btnCapturar = document.getElementById("btnCapturar");
    AppDispositivo.mensajeExito = document.getElementById("mensajeExito");
    AppDispositivo.mensajeError = document.getElementById("mensajeError");
    
    AppDispositivo.table = $("#tablaDispositivosDT").DataTable({
        ajax: {
            url: AppDispositivo.config.urlControlador,
            dataSrc: json => json.data || []
        },
        columns: [
            { data: "TipoDispositivo" },
            { data: "MarcaDispositivo" },
            { data: "NumeroSerial" },
            { data: "NombreFuncionario" },
            { data: "TipoMovimiento" },
            { data: "FechaIngreso", render: d => new Date(d).toLocaleString('es-ES') }
        ],
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
        order: [[5, 'desc']]
    });
    
    if (AppDispositivo.btnCapturar)
        AppDispositivo.btnCapturar.addEventListener("click", toggleCamara);
});