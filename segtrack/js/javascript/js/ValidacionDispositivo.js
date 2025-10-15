document.addEventListener('DOMContentLoaded', function () {
    console.log('✅ Script de validación cargado');
    
    const form = document.getElementById('formDispositivo');
    const tipoSelect = document.getElementById('TipoDispositivo');
    const campoOtro = document.getElementById('campoOtro');
    const tieneVisitanteSelect = document.getElementById('TieneVisitante');
    const visitanteContainer = document.getElementById('VisitanteContainer');

    // 🎛️ Mostrar/Ocultar campo "Otro"
    tipoSelect.addEventListener('change', function() {
        console.log('Tipo seleccionado:', this.value);
        if (this.value === 'Otro') {
            campoOtro.style.display = 'block';
        } else {
            campoOtro.style.display = 'none';
        }
    });

    // 🎛️ Mostrar/Ocultar campo "ID Visitante"
    tieneVisitanteSelect.addEventListener('change', function() {
        console.log('Visitante:', this.value);
        if (this.value === 'si') {
            visitanteContainer.style.display = 'block';
            document.getElementById('IdVisitante').required = true;
        } else {
            visitanteContainer.style.display = 'none';
            document.getElementById('IdVisitante').required = false;
            document.getElementById('IdVisitante').value = '';
        }
    });

    // 📋 MANEJO DEL SUBMIT
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        console.log('📤 Formulario enviado');

        // 🔍 Obtenemos los valores
        const tipo = tipoSelect.value.trim();
        const otroTipo = document.querySelector('input[name="OtroTipoDispositivo"]')?.value.trim() || '';
        const marca = document.getElementById('MarcaDispositivo').value.trim();
        const idFuncionario = document.getElementById('IdFuncionario').value.trim();
        const idVisitante = document.getElementById('IdVisitante').value.trim();
        const tieneVisitante = tieneVisitanteSelect.value;

        console.log('Datos capturados:', {
            tipo,
            otroTipo,
            marca,
            idFuncionario,
            idVisitante,
            tieneVisitante
        });

        // 🧩 Validaciones
        const regexTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,-]+$/;
        const regexNumero = /^\d+$/;

        // ✅ Validación de tipo
        if (tipo === '') {
            console.error('❌ Error: Tipo vacío');
            alert('❌ Debe seleccionar un tipo de dispositivo.');
            return;
        }

        // ✅ Validación de marca
        if (marca === '') {
            console.error('❌ Error: Marca vacía');
            alert('❌ El campo Marca es obligatorio.');
            return;
        }

        if (!regexTexto.test(marca)) {
            console.error('❌ Error: Marca inválida:', marca);
            alert('❌ El campo Marca contiene caracteres inválidos.');
            return;
        }

        // 🧠 Preparar FormData
        const formData = new FormData();
        formData.append('TipoDispositivo', tipo === 'Otro' ? otroTipo : tipo);
        formData.append('MarcaDispositivo', marca);
        formData.append('IdFuncionario', idFuncionario);
        formData.append('IdVisitante', idVisitante);
        formData.append('TieneVisitante', tieneVisitante);
        formData.append('accion', 'registrar');

        console.log('📦 FormData preparado:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}: ${value}`);
        }

        // Mostrar loading
        Swal.fire({
            title: 'Procesando...',
            text: 'Por favor espera.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            console.log('🚀 Iniciando fetch...');
            
            // Probar ambas rutas
            let urls = [
                '../Controller/parqueadero_dispositivo/ControladorDispositivo.php',
                '../../Controller/parqueadero_dispositivo/ControladorDispositivo.php'
            ];

            let response;
            let urlUsada = '';

            for (let url of urls) {
                try {
                    console.log(`Probando URL: ${url}`);
                    response = await fetch(url, {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        urlUsada = url;
                        console.log(`✅ URL funcionó: ${url}`);
                        break;
                    }
                } catch (e) {
                    console.log(`❌ URL falló: ${url} - ${e.message}`);
                }
            }

            if (!response) {
                throw new Error('No se pudo conectar con ninguna ruta');
            }

            const contentType = response.headers.get('content-type');
            console.log('📨 Content-Type:', contentType);
            console.log('📊 Status:', response.status);

            let datos;
            const textoRespuesta = await response.text();
            console.log('📄 Respuesta del servidor (texto):', textoRespuesta);

            try {
                datos = JSON.parse(textoRespuesta);
            } catch (e) {
                console.error('❌ Error al parsear JSON:', e);
                console.error('Texto recibido:', textoRespuesta.substring(0, 500));
                throw new Error('El servidor no devolvió JSON válido');
            }

            Swal.close();
            console.log('✅ Respuesta parseada:', datos);

            if (datos.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: datos.message || 'Dispositivo registrado correctamente',
                    showConfirmButton: true
                }).then(() => {
                    form.reset();
                    campoOtro.style.display = 'none';
                    visitanteContainer.style.display = 'none';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: datos.message || 'Error al registrar',
                    footer: datos.error ? `Detalle: ${datos.error}` : ''
                });
            }

        } catch (error) {
            Swal.close();
            console.error('❌ Error completo:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: error.message,
                footer: 'Revisa la consola (F12) para más detalles'
            });
        }
    });
});