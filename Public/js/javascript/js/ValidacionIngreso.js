    // ========================================
    // CONTROL DE INGRESOS - SISTEMA SEGTRACK
    // Lectura continua de QR, más simple
    // ========================================

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
        config: {
        urlControlador: "/SEGTRACK/App/Controller/ControladorIngreso.php",


            fps: 10,
            qrboxSize: 250,
            bloqueoQRms: 1500 // tiempo para permitir siguiente lectura
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
                body: JSON.stringify({ qr_codigo: qr, tipoMovimiento: tipo })
            });

            const data = await res.json();
            mostrarMensaje(data.success, data.message);

            if (data.success && App.table) {
                App.table.ajax.reload(null, false);
            }
        } catch (e) {
            mostrarMensaje(false, "Error de conexión.");
        } finally {
            if (App.btnCapturar) App.btnCapturar.disabled = false;
        }
    }

    // Lectura de QR continua
    function onScanQR(qr) {
        qr = qr.trim();
        if (!qr || App.escaneando || qr === App.ultimaLectura) return;

        App.escaneando = true;
        App.ultimaLectura = qr;

        enviarQr(qr, App.tipoMovimiento.value).finally(() => {
            // desbloquea después de un tiempo
            setTimeout(() => {
                App.escaneando = false;
                App.ultimaLectura = null;
            }, App.config.bloqueoQRms);
        });
    }

    // Iniciar cámara para escanear QR
    async function iniciarCamara() {
        if (App.qrReader) return; // ya está activa

        App.qrReader = new Html5Qrcode("qr-reader");

        try {
            await App.qrReader.start(
                { facingMode: "environment" },
                { fps: App.config.fps, qrbox: App.config.qrboxSize },
                onScanQR
            );
        } catch (e) {
            console.error("Error al iniciar cámara:", e);
            mostrarMensaje(false, "No se pudo iniciar la cámara.");
        }
    }

    // Descargar PDF de ingresos
  async function descargarPDF() {
    try {
        const res = await fetch('/SEGTRACK/App/Controller/IngresoPDFController.php?accion=pdf');
        if (!res.ok) throw new Error('Error al generar PDF');

        const blob = await res.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Ingresos_${new Date().toISOString().slice(0, 10)}.pdf`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        mostrarMensaje(true, "PDF descargado correctamente.");
    } catch (e) {
        console.error('Error:', e);
        mostrarMensaje(false, "No se pudo descargar el PDF.");
    }
}
    // Inicialización al cargar la página
    document.addEventListener("DOMContentLoaded", () => {
        App.tipoMovimiento = document.getElementById("tipoMovimiento");
        App.btnCapturar = document.getElementById("btnCapturar");
        App.btnDescargarPDF = document.getElementById("btnDescargarPDF");
        App.mensajeExito = document.getElementById("mensajeExito");
        App.mensajeError = document.getElementById("mensajeError");

        // Inicializa tabla
        App.table = $("#tablaIngresosDT").DataTable({
           ajax: {
    url: App.config.urlControlador,
    dataSrc: function (json) {
        return json.data || []; 
    }
},

            columns: [
                { data: "NombreFuncionario" },
                { data: "CargoFuncionario" },
                { data: "TipoMovimiento" },
                { data: "FechaIngreso", render: d => new Date(d).toLocaleString('es-ES') }
            ],
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
            order: [[3, 'desc']]
        });

        if (App.btnCapturar) App.btnCapturar.addEventListener("click", iniciarCamara);
        if (App.btnDescargarPDF) App.btnDescargarPDF.addEventListener("click", descargarPDF);
    });