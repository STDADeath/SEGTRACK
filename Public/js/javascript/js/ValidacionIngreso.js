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
    _timerCamara: null,
    _timerCountdown: null,

    config: {
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngreso.php",
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
// ALERTA FUNCIONARIO INACTIVO
// ========================================

function mostrarAlertaInactivo() {

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Acceso denegado',
            text: 'Este funcionario está inactivo y no tiene permiso de acceso.',
            confirmButtonColor: '#e74a3b',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('⛔ Funcionario inactivo. No tiene permiso de acceso.');
    }

    mostrarMensaje(false, '⛔ Funcionario inactivo. No tiene permiso de acceso.');
}


// ========================================
// ALERTA SIN ENTRADA PREVIA
// ========================================

function mostrarAlertaSinEntrada() {

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Salida no permitida',
            text: 'Este funcionario no tiene una entrada activa. Debe ingresar primero.',
            confirmButtonColor: '#f6c23e',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('⚠️ El funcionario debe registrar una Entrada antes de salir.');
    }

    mostrarMensaje(false, '⚠️ Debe registrar una Entrada antes de poder registrar una Salida.');
}


// ========================================
// ALERTA ENTRADA DUPLICADA
// ========================================

function mostrarAlertaEntradaDuplicada() {

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Entrada duplicada',
            text: 'Este funcionario ya tiene una entrada activa. Debe registrar una Salida primero.',
            confirmButtonColor: '#36b9cc',
            confirmButtonText: 'Entendido',
            timer: 5000,
            timerProgressBar: true
        });
    } else {
        alert('ℹ️ El funcionario ya tiene una Entrada activa. Registre una Salida primero.');
    }

    mostrarMensaje(false, 'ℹ️ El funcionario ya tiene una Entrada activa. Registre una Salida primero.');
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

    if (App._timerCard) clearTimeout(App._timerCard);

    card.classList.remove("d-none");
    card.style.transition = "opacity 0.4s ease";
    card.style.opacity    = "0";
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { card.style.opacity = "1"; });
    });

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

        if (!data.success && data.codigo === 'inactivo') {
            mostrarAlertaInactivo();
            return;
        }

        if (!data.success && data.codigo === 'sin_entrada_previa') {
            mostrarAlertaSinEntrada();
            return;
        }

        if (!data.success && data.codigo === 'entrada_duplicada') {
            mostrarAlertaEntradaDuplicada();
            return;
        }

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
// ACTUALIZAR BOTÓN SEGÚN ESTADO CÁMARA
// ========================================

function actualizarBoton(camaraActiva) {
    const btn = App.btnCapturar;
    if (camaraActiva) {
        btn.classList.replace("btn-primary", "btn-danger");
        btn.innerHTML = '<i class="fas fa-times me-2"></i>Cancelar Cámara';
    } else {
        btn.classList.replace("btn-danger", "btn-primary");
        btn.innerHTML = '<i class="fas fa-camera me-2"></i>Capturar Código QR';
    }
}


// ========================================
// COUNTDOWN VISUAL EN EL BOTÓN
// ========================================

function iniciarCountdown(segundos) {

    limpiarCountdown();

    let restantes = segundos;

    // Actualizar el botón inmediatamente con el primer valor
    if (App.btnCapturar) {
        App.btnCapturar.innerHTML =
            `<i class="fas fa-times me-2"></i>Cancelar Cámara (${restantes}s)`;
    }

    App._timerCountdown = setInterval(() => {
        restantes--;

        if (restantes <= 0) {
            limpiarCountdown();
            detenerCamara();
            return;
        }

        if (App.btnCapturar) {
            App.btnCapturar.innerHTML =
                `<i class="fas fa-times me-2"></i>Cancelar Cámara (${restantes}s)`;
        }

    }, 1000);
}

function limpiarCountdown() {
    if (App._timerCountdown) {
        clearInterval(App._timerCountdown);
        App._timerCountdown = null;
    }
}


// ========================================
// INICIAR CÁMARA
// ========================================

async function iniciarCamara() {

    if (App.qrReader) return;

    try {
        await navigator.mediaDevices.getUserMedia({ video: true });
    } catch (permiso) {
        mostrarMensaje(false, "Permiso de cámara denegado. Habilítalo en la barra del navegador.");
        return;
    }

    App.qrReader = new Html5Qrcode("qr-reader");

    try {

        const devices = await Html5Qrcode.getCameras();

        if (!devices || devices.length === 0) {
            mostrarMensaje(false, "No se detectaron cámaras.");
            App.qrReader.clear();
            App.qrReader = null;
            return;
        }

        const camara =
            devices.find(d => d.label.toLowerCase().includes("back"))    ||
            devices.find(d => d.label.toLowerCase().includes("rear"))    ||
            devices.find(d => d.label.toLowerCase().includes("trasera")) ||
            devices.find(d => d.label.toLowerCase().includes("droid"))   ||
            devices[0];

        await App.qrReader.start(
            camara.id,
            {
                fps: App.config.fps,
                qrbox: { width: App.config.qrboxSize, height: App.config.qrboxSize }
            },
            onScanQR,
            () => {}
        );

        actualizarBoton(true);
        iniciarCountdown(20);

    } catch (error) {

        try {

            await App.qrReader.start(
                { facingMode: "environment" },
                {
                    fps: App.config.fps,
                    qrbox: { width: App.config.qrboxSize, height: App.config.qrboxSize }
                },
                onScanQR,
                () => {}
            );

            actualizarBoton(true);
            iniciarCountdown(5);

        } catch (error2) {

            mostrarMensaje(false, "No se pudo iniciar la cámara: " + error2.message);
            App.qrReader.clear();
            App.qrReader = null;

        }
    }
}


// ========================================
// DETENER CÁMARA
// ========================================

async function detenerCamara() {

    limpiarCountdown();

    if (!App.qrReader) return;

    try {
        await App.qrReader.stop();
        App.qrReader.clear();
    } catch (e) {
        console.error("Error deteniendo cámara:", e);
    } finally {
        App.qrReader      = null;
        App.escaneando    = false;
        App.ultimaLectura = null;
        actualizarBoton(false);
    }
}


// ========================================
// ALTERNAR CÁMARA
// ========================================

async function toggleCamara() {
    if (App.qrReader) {
        await detenerCamara();
    } else {
        await iniciarCamara();
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
        App.btnCapturar.addEventListener("click", toggleCamara);

    if (App.btnDescargarPDF)
        App.btnDescargarPDF.addEventListener("click", descargarPDF);

});