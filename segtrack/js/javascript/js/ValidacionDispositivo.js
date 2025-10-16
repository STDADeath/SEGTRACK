document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formDispositivo');
    
    if (!form) {
        console.error('Formulario no encontrado');
        return;
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const tipo = document.getElementById('TipoDispositivo').value.trim();
        const marca = document.getElementById('MarcaDispositivo').value.trim();
        const idFuncionario = document.getElementById('IdFuncionario').value.trim();
        const idVisitante = document.getElementById('IdVisitante').value.trim();

        // Validaciones básicas
        if (!tipo) {
            alert('Debe seleccionar un tipo de dispositivo');
            return;
        }

        if (!marca) {
            alert('Debe ingresar la marca del dispositivo');
            return;
        }

        const formData = new FormData();
        formData.append('TipoDispositivo', tipo);
        formData.append('MarcaDispositivo', marca);
        formData.append('IdFuncionario', idFuncionario);
        formData.append('IdVisitante', idVisitante);
        formData.append('accion', 'registrar');

        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch('../Controller/parqueadero_dispositivo/ControladorDispositivo.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            Swal.close();

            if (data.success) {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    form.reset();
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message || 'Error al registrar', 'error');
            }

        } catch (error) {
            Swal.close();
            console.error('Error:', error);
            Swal.fire('Error de conexión', 'No se pudo conectar al servidor', 'error');
        }
    });

    // Mostrar/Ocultar campo "Otro"
    const tipoSelect = document.getElementById('TipoDispositivo');
    const campoOtro = document.getElementById('campoOtro');
    
    if (tipoSelect && campoOtro) {
        tipoSelect.addEventListener('change', function() {
            campoOtro.style.display = this.value === 'Otro' ? 'block' : 'none';
        });
    }

    // Mostrar/Ocultar campo "ID Visitante"
    const tieneVisitante = document.getElementById('TieneVisitante');
    const visitanteContainer = document.getElementById('VisitanteContainer');
    
    if (tieneVisitante && visitanteContainer) {
        tieneVisitante.addEventListener('change', function() {
            visitanteContainer.style.display = this.value === 'si' ? 'block' : 'none';
        });
    }
});