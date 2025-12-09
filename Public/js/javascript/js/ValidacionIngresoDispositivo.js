// ========================================
// CONTROL DE DISPOSITIVOS - SISTEMA SEGTRACK
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

// Mostrar mensaje en pantalla
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

// Enviar QR al servidor
async function enviarQr(qr, tipo) {
    if (App.btnCapturar) App.btnCapturar.disabled = true;

    try {
        const res = await fetch(App.config.urlControlador, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ 
                qr_codigo: qr, 
                tipoMovimiento: tipo
                // NO enviar idSede, se obtiene automáticamente
            })
        });

        const data = await res.json();
        
        // Log para debug
        console.log('Respuesta del servidor:', data);
        
        mostrarMensaje(data.success, data.message);

        if (data.success && App.table) {
            App.table.ajax.reload(null, false);
        }
    } catch (e) {
        console.error('Error de conexión:', e);
        mostrarMensaje(false, "Error de conexión con el servidor.");
    } finally {
        if (App.btnCapturar) App.btnCapturar.disabled = false;
    }
}

// Lectura de QR continua
function onScanQR(qr) {
    // Limpieza agresiva del QR
    qr = qr.trim().replace(/\s+/g, '');
    
    // Log para debug
    console.log('QR escaneado:', qr, '| Length:', qr.length);
    
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

// Iniciar cámara para escanear QR
async function iniciarCamara() {
    if (App.qrReader) {
        console.log('Cámara ya está activa');
        return;
    }

    App.qrReader = new Html5Qrcode("qr-reader");

    try {
        await App.qrReader.start(
            { facingMode: "environment" },
            { fps: App.config.fps, qrbox: App.config.qrboxSize },
            onScanQR
        );
        console.log('Cámara iniciada correctamente');
    } catch (e) {
        console.error("Error al iniciar cámara:", e);
        mostrarMensaje(false, "No se pudo iniciar la cámara. Verifica los permisos.");
    }
}

// Inicialización al cargar la página
document.addEventListener("DOMContentLoaded", () => {
    console.log('Inicializando sistema de dispositivos...');
    
    App.tipoMovimiento = document.getElementById("tipoMovimiento");
    App.btnCapturar = document.getElementById("btnCapturar");
    App.mensajeExito = document.getElementById("mensajeExito");
    App.mensajeError = document.getElementById("mensajeError");

    // Inicializa tabla DataTables
    App.table = $("#tablaDispositivosDT").DataTable({
        ajax: {
            url: App.config.urlControlador,
            dataSrc: function (json) {
                console.log('Datos recibidos:', json);
                return json.data || [];
            }
        },
        columns: [
            { data: "QrDispositivo" },
            { data: "TipoDispositivo" },
            { data: "MarcaDispositivo" },
            { data: "TipoMovimiento" },
            { data: "FechaIngreso", render: d => new Date(d).toLocaleString('es-ES') }
        ],
        language: { 
            url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" 
        },
        order: [[4, 'desc']]
    });

    // Evento para botón de captura
    if (App.btnCapturar) {
        App.btnCapturar.addEventListener("click", iniciarCamara);
        console.log('Botón de captura configurado');
    }

    console.log('Sistema inicializado correctamente');
});