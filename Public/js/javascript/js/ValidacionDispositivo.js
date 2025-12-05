document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formDispositivo');
    
    if (!form) {
        console.error('Formulario no encontrado');
        return;
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Obtener valores
        const tipo = document.getElementById('TipoDispositivo').value.trim();
        const otroTipo = document.querySelector('input[name="OtroTipoDispositivo"]')?.value.trim() || '';
        const marca = document.getElementById('MarcaDispositivo').value.trim();
        const idFuncionario = document.getElementById('IdFuncionario').value.trim();
        const idVisitante = document.getElementById('IdVisitante').value.trim();
        const tieneVisitante = document.getElementById('TieneVisitante').value;

        // Expresiones regulares
        const regexTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,-]+$/;

        // 1. Validar Tipo de Dispositivo
        if (!tipo) {
            Swal.fire('Error', 'Debe seleccionar un tipo de dispositivo', 'error');
            return;
        }

        // 2. Si selecciona "Otro", debe especificar el tipo
        if (tipo === 'Otro' && !otroTipo) {
            Swal.fire('Error', 'Debe especificar el tipo de dispositivo en el campo "Otro"', 'error');
            return;
        }

        if (tipo === 'Otro' && !regexTexto.test(otroTipo)) {
            Swal.fire('Error', 'El tipo de dispositivo contiene caracteres inválidos (solo letras, números y .-,)', 'error');
            return;
        }

        // 3. Validar Marca
        if (!marca) {
            Swal.fire('Error', 'Debe ingresar la marca del dispositivo', 'error');
            return;
        }

        if (!regexTexto.test(marca)) {
            Swal.fire('Error', 'La marca contiene caracteres inválidos. Solo se permiten letras, números y .-,', 'error');
            return;
        }

        // 4. Validar que sea funcionario O visitante, pero no ambos ni ninguno
        const tieneIdFuncionario = idFuncionario !== '';
        const tieneIdVisitante = idVisitante !== '';

        // Si dice "no" a visitante, entonces DEBE tener funcionario
        if (tieneVisitante === 'no') {
            if (!tieneIdFuncionario) {
                Swal.fire('Error', 'Si el dispositivo pertenece a un funcionario, debe seleccionar uno de la lista', 'error');
                return;
            }
            
            if (tieneIdVisitante) {
                Swal.fire('Error', 'No puede seleccionar un visitante si indica que pertenece a un funcionario', 'error');
                return;
            }
        }

        // Si dice "sí" a visitante, entonces DEBE tener visitante
        if (tieneVisitante === 'si') {
            if (!tieneIdVisitante) {
                Swal.fire('Error', 'Si el dispositivo pertenece a un visitante, debe seleccionar uno de la lista', 'error');
                return;
            }

            if (tieneIdFuncionario) {
                Swal.fire('Error', 'No puede seleccionar un funcionario si indica que pertenece a un visitante', 'error');
                return;
            }
        }

        // Si llegamos aquí, todas las validaciones pasaron
        const formData = new FormData();
        const tipoFinal = tipo === 'Otro' ? otroTipo : tipo;
        formData.append('TipoDispositivo', tipoFinal);
        formData.append('MarcaDispositivo', marca);
        formData.append('IdFuncionario', tieneIdFuncionario ? idFuncionario : '');
        formData.append('IdVisitante', tieneIdVisitante ? idVisitante : '');
        formData.append('accion', 'registrar');

        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch('../../Controller/ControladorDispositivo.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            Swal.close();

            if (data.success) {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    form.reset();
                    document.getElementById('campoOtro').style.display = 'none';
                    document.getElementById('FuncionarioContainer').style.display = 'block';
                    document.getElementById('VisitanteContainer').style.display = 'none';
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

    // Mostrar/Ocultar campos de Funcionario o Visitante
    const tieneVisitante = document.getElementById('TieneVisitante');
    const funcionarioContainer = document.getElementById('FuncionarioContainer');
    const visitanteContainer = document.getElementById('VisitanteContainer');
    const selectFuncionario = document.getElementById('IdFuncionario');
    const selectVisitante = document.getElementById('IdVisitante');
    
    if (tieneVisitante && funcionarioContainer && visitanteContainer) {
        tieneVisitante.addEventListener('change', function() {
            if (this.value === 'si') {
                // Mostrar visitante, ocultar funcionario
                funcionarioContainer.style.display = 'none';
                visitanteContainer.style.display = 'block';
                
                // Limpiar selección de funcionario
                selectFuncionario.value = '';
            } else {
                // Mostrar funcionario, ocultar visitante
                funcionarioContainer.style.display = 'block';
                visitanteContainer.style.display = 'none';
                
                // Limpiar selección de visitante
                selectVisitante.value = '';
            }
        });
    }
});