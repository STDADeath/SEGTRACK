document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formDispositivo');
    
    if (!form) {
        console.error('Formulario no encontrado');
        return;
    }

    // 🆕 VALIDACIÓN EN TIEMPO REAL DEL NÚMERO SERIAL
    const inputSerial = document.getElementById('NumeroSerial');
    if (inputSerial) {
        inputSerial.addEventListener('input', function(e) {
            let valor = e.target.value;
            // Remover caracteres no permitidos automáticamente
            valor = valor.replace(/[^a-zA-Z0-9\-_]/g, '');
            e.target.value = valor;
            
            // Validar longitud
            if (valor.length > 50) {
                e.target.value = valor.substring(0, 50);
                e.target.classList.add('is-invalid');
            } else if (valor.length > 0) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else {
                e.target.classList.remove('is-invalid', 'is-valid');
            }
        });
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Obtener valores
        const tipo = document.getElementById('TipoDispositivo').value.trim();
        const otroTipo = document.querySelector('input[name="OtroTipoDispositivo"]')?.value.trim() || '';
        const marca = document.getElementById('MarcaDispositivo').value.trim();
        const numeroSerial = document.getElementById('NumeroSerial').value.trim();
        const idFuncionario = document.getElementById('IdFuncionario').value.trim();
        const idVisitante = document.getElementById('IdVisitante').value.trim();
        const tieneVisitante = document.getElementById('TieneVisitante').value;

        // Expresiones regulares
        const regexTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,-]+$/;
        const regexSerial = /^[a-zA-Z0-9\-_]+$/;

        // 1. Validar Tipo de Dispositivo
        if (!tipo) {
            Swal.fire({
                icon: 'error',
                title: 'Campo requerido',
                text: 'Debe seleccionar un tipo de dispositivo',
                confirmButtonColor: '#e74a3b'
            });
            return;
        }

        // 2. Si selecciona "Otro", debe especificar el tipo
        if (tipo === 'Otro' && !otroTipo) {
            Swal.fire({
                icon: 'error',
                title: 'Campo requerido',
                text: 'Debe especificar el tipo de dispositivo en el campo "Otro"',
                confirmButtonColor: '#e74a3b'
            });
            return;
        }

        if (tipo === 'Otro' && !regexTexto.test(otroTipo)) {
            Swal.fire({
                icon: 'error',
                title: 'Caracteres inválidos',
                text: 'El tipo de dispositivo contiene caracteres inválidos (solo letras, números y .-,)',
                confirmButtonColor: '#e74a3b'
            });
            return;
        }

        // 3. Validar Marca
        if (!marca) {
            Swal.fire({
                icon: 'error',
                title: 'Campo requerido',
                text: 'Debe ingresar la marca del dispositivo',
                confirmButtonColor: '#e74a3b'
            });
            return;
        }

        if (!regexTexto.test(marca)) {
            Swal.fire({
                icon: 'error',
                title: 'Caracteres inválidos',
                text: 'La marca contiene caracteres inválidos. Solo se permiten letras, números y .-,',
                confirmButtonColor: '#e74a3b'
            });
            return;
        }

        // 4. 🆕 VALIDAR NÚMERO SERIAL (MEJORADO)
        if (numeroSerial) {
            if (!regexSerial.test(numeroSerial)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Serial inválido',
                    html: 'El número serial solo puede contener:<br>' +
                          '• Letras (A-Z, a-z)<br>' +
                          '• Números (0-9)<br>' +
                          '• Guiones (-)<br>' +
                          '• Guiones bajos (_)',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            if (numeroSerial.length > 50) {
                Swal.fire({
                    icon: 'error',
                    title: 'Serial muy largo',
                    text: 'El número serial no puede exceder 50 caracteres',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            if (numeroSerial.length < 3) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Serial muy corto',
                    text: 'El número serial debe tener al menos 3 caracteres',
                    confirmButtonColor: '#f6c23e'
                });
                return;
            }
        }

        // 5. Validar que sea funcionario O visitante, pero no ambos ni ninguno
        const tieneIdFuncionario = idFuncionario !== '';
        const tieneIdVisitante = idVisitante !== '';

        // Si dice "no" a visitante, entonces DEBE tener funcionario
        if (tieneVisitante === 'no') {
            if (!tieneIdFuncionario) {
                Swal.fire({
                    icon: 'error',
                    title: 'Funcionario requerido',
                    text: 'Si el dispositivo pertenece a un funcionario, debe seleccionar uno de la lista',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }
            
            if (tieneIdVisitante) {
                Swal.fire({
                    icon: 'error',
                    title: 'Selección inválida',
                    text: 'No puede seleccionar un visitante si indica que pertenece a un funcionario',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }
        }

        // Si dice "sí" a visitante, entonces DEBE tener visitante
        if (tieneVisitante === 'si') {
            if (!tieneIdVisitante) {
                Swal.fire({
                    icon: 'error',
                    title: 'Visitante requerido',
                    text: 'Si el dispositivo pertenece a un visitante, debe seleccionar uno de la lista',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }

            if (tieneIdFuncionario) {
                Swal.fire({
                    icon: 'error',
                    title: 'Selección inválida',
                    text: 'No puede seleccionar un funcionario si indica que pertenece a un visitante',
                    confirmButtonColor: '#e74a3b'
                });
                return;
            }
        }

        // Si llegamos aquí, todas las validaciones pasaron
        const formData = new FormData();
        const tipoFinal = tipo === 'Otro' ? otroTipo : tipo;
        formData.append('TipoDispositivo', tipoFinal);
        formData.append('MarcaDispositivo', marca);
        formData.append('NumeroSerial', numeroSerial);
        formData.append('IdFuncionario', tieneIdFuncionario ? idFuncionario : '');
        formData.append('IdVisitante', tieneIdVisitante ? idVisitante : '');
        formData.append('accion', 'registrar');

        Swal.fire({
            title: 'Procesando...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br>Validando y registrando dispositivo',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        try {
            const response = await fetch('../../Controller/ControladorDispositivo.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            Swal.close();

            if (data.success) {
                // 🎉 ÉXITO CON ANIMACIÓN
                Swal.fire({
                    icon: 'success',
                    title: '¡Dispositivo registrado!',
                    html: data.message,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonColor: '#1cc88a',
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    form.reset();
                    document.getElementById('campoOtro').style.display = 'none';
                    document.getElementById('FuncionarioContainer').style.display = 'block';
                    document.getElementById('VisitanteContainer').style.display = 'none';
                    
                    // Limpiar clases de validación
                    if (inputSerial) {
                        inputSerial.classList.remove('is-valid', 'is-invalid');
                    }
                    
                    location.reload();
                });
            } else {
                // ⚠️ ERROR DEL SERVIDOR (duplicados, etc.)
                Swal.fire({
                    icon: 'warning',
                    title: 'No se pudo registrar',
                    html: data.message.replace(/\n/g, '<br>'),
                    confirmButtonColor: '#f6c23e',
                    confirmButtonText: 'Entendido',
                    footer: '<small class="text-muted">Revise la información e intente nuevamente</small>'
                });
            }

        } catch (error) {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                html: 'No se pudo conectar al servidor.<br>Por favor, intente nuevamente.',
                confirmButtonColor: '#e74a3b',
                footer: '<small>Si el problema persiste, contacte al administrador</small>'
            });
        }
    });

    // Mostrar/Ocultar campo "Otro"
    const tipoSelect = document.getElementById('TipoDispositivo');
    const campoOtro = document.getElementById('campoOtro');
    
    if (tipoSelect && campoOtro) {
        tipoSelect.addEventListener('change', function() {
            if (this.value === 'Otro') {
                campoOtro.style.display = 'block';
                campoOtro.querySelector('input').focus();
            } else {
                campoOtro.style.display = 'none';
            }
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
                selectVisitante.focus();
            } else {
                // Mostrar funcionario, ocultar visitante
                funcionarioContainer.style.display = 'block';
                visitanteContainer.style.display = 'none';
                
                // Limpiar selección de visitante
                selectVisitante.value = '';
                selectFuncionario.focus();
            }
        });
    }

    // 🆕 TOOLTIP PARA NÚMERO SERIAL
    if (inputSerial) {
        inputSerial.addEventListener('focus', function() {
            const hint = document.createElement('small');
            hint.id = 'serialHint';
            hint.className = 'text-muted d-block mt-1';
            hint.innerHTML = '<i class="fas fa-info-circle me-1"></i> Formato: letras, números, guiones (-) y guiones bajos (_)';
            
            if (!document.getElementById('serialHint')) {
                this.parentElement.parentElement.appendChild(hint);
            }
        });
    }
});