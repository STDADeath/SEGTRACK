// File: ../js/registro-funcionario.js
// Controla el envío AJAX del formulario existente (id="formFuncionario")
// Muestra el resultado dentro de un modal SweetAlert2 pequeño.
// Incluye validación visual en tiempo real con clases CSS 'valido' e 'invalido'

document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formFuncionario');
  if (!form) return; // si no existe el formulario, salir

  const swalWidth = '420px'; // ancho del modal (ajústalo si quieres más chico/grande)

  // ========== FUNCIONES DE VALIDACIÓN ==========
  
  // Validar nombre (solo letras y espacios, mínimo 3 caracteres)
  function validarNombre(valor) {
    const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/;
    return regex.test(valor.trim());
  }

  // Validar documento (solo números, entre 7 y 12 dígitos)
  function validarDocumento(valor) {
    const regex = /^\d{7,12}$/;
    return regex.test(valor.trim());
  }

  // Validar correo electrónico
  function validarCorreo(valor) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(valor.trim());
  }

  // Validar teléfono (10 dígitos)
  function validarTelefono(valor) {
    const regex = /^\d{10}$/;
    return regex.test(valor.trim());
  }

  // Validar select (que tenga un valor seleccionado)
  function validarSelect(valor) {
    return valor !== "" && valor !== null;
  }

  // ========== APLICAR CLASES DE VALIDACIÓN ==========
  
  function aplicarValidacion(elemento, esValido) {
    elemento.classList.remove('valido', 'invalido');
    if (elemento.value.trim() !== '') { // Solo aplicar si hay contenido
      elemento.classList.add(esValido ? 'valido' : 'invalido');
    }
  }

  // ========== VALIDACIÓN EN TIEMPO REAL ==========
  
  // Obtener todos los campos del formulario
  const nombreInput = document.getElementById('NombreFuncionario');
  const documentoInput = document.getElementById('DocumentoFuncionario');
  const correoInput = document.getElementById('CorreoFuncionario');
  const telefonoInput = document.getElementById('TelefonoFuncionario');
  const cargoSelect = document.getElementById('CargoFuncionario');
  const sedeSelect = document.getElementById('IdSede');

  // Validación para el nombre
  if (nombreInput) {
    nombreInput.addEventListener('input', function() {
      const esValido = validarNombre(this.value);
      aplicarValidacion(this, esValido);
    });

    nombreInput.addEventListener('blur', function() {
      const esValido = validarNombre(this.value);
      aplicarValidacion(this, esValido);
    });
  }

  // Validación para el documento
  if (documentoInput) {
    documentoInput.addEventListener('input', function() {
      const esValido = validarDocumento(this.value);
      aplicarValidacion(this, esValido);
    });

    documentoInput.addEventListener('blur', function() {
      const esValido = validarDocumento(this.value);
      aplicarValidacion(this, esValido);
    });
  }

  // Validación para el correo
  if (correoInput) {
    correoInput.addEventListener('input', function() {
      const esValido = validarCorreo(this.value);
      aplicarValidacion(this, esValido);
    });

    correoInput.addEventListener('blur', function() {
      const esValido = validarCorreo(this.value);
      aplicarValidacion(this, esValido);
    });
  }

  // Validación para el teléfono
  if (telefonoInput) {
    telefonoInput.addEventListener('input', function() {
      const esValido = validarTelefono(this.value);
      aplicarValidacion(this, esValido);
    });

    telefonoInput.addEventListener('blur', function() {
      const esValido = validarTelefono(this.value);
      aplicarValidacion(this, esValido);
    });
  }

  // Validación para el cargo
  if (cargoSelect) {
    cargoSelect.addEventListener('change', function() {
      const esValido = validarSelect(this.value);
      aplicarValidacion(this, esValido);
    });
  }

  // Validación para la sede
  if (sedeSelect) {
    sedeSelect.addEventListener('change', function() {
      const esValido = validarSelect(this.value);
      aplicarValidacion(this, esValido);
    });
  }

  // ========== VALIDACIÓN COMPLETA DEL FORMULARIO ==========
  
  function validarFormularioCompleto() {
    let formularioValido = true;
    
    // Validar cada campo individualmente
    if (nombreInput) {
      const nombreValido = validarNombre(nombreInput.value);
      aplicarValidacion(nombreInput, nombreValido);
      if (!nombreValido) formularioValido = false;
    }

    if (documentoInput) {
      const documentoValido = validarDocumento(documentoInput.value);
      aplicarValidacion(documentoInput, documentoValido);
      if (!documentoValido) formularioValido = false;
    }

    if (correoInput) {
      const correoValido = validarCorreo(correoInput.value);
      aplicarValidacion(correoInput, correoValido);
      if (!correoValido) formularioValido = false;
    }

    if (telefonoInput) {
      const telefonoValido = validarTelefono(telefonoInput.value);
      aplicarValidacion(telefonoInput, telefonoValido);
      if (!telefonoValido) formularioValido = false;
    }

    if (cargoSelect) {
      const cargoValido = validarSelect(cargoSelect.value);
      aplicarValidacion(cargoSelect, cargoValido);
      if (!cargoValido) formularioValido = false;
    }

    if (sedeSelect) {
      const sedeValido = validarSelect(sedeSelect.value);
      aplicarValidacion(sedeSelect, sedeValido);
      if (!sedeValido) formularioValido = false;
    }

    return formularioValido;
  }

  // ========== ENVÍO DEL FORMULARIO ==========
  
  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    // Validar formulario completo antes de enviar
    const formularioValido = validarFormularioCompleto();

    // Si la validación personalizada falla, mostrar aviso
    if (!formularioValido) {
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Campos incompletos o incorrectos',
          text: 'Por favor corrige los campos marcados en rojo antes de enviar.',
          toast: true,
          position: 'top-end',
          timer: 3000,
          showConfirmButton: false
        });
      } else {
        alert('Por favor corrige los campos marcados en rojo antes de enviar.');
      }
      return;
    }

    // Validación nativa adicional
    if (!form.checkValidity()) {
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Campos incompletos',
          text: 'Por favor corrige los campos requeridos antes de enviar.',
          toast: true,
          position: 'top-end',
          timer: 2200,
          showConfirmButton: false
        });
      } else {
        alert('Por favor corrige los campos requeridos antes de enviar.');
      }
      return;
    }

    // Desactivar botón para evitar envíos dobles
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.dataset.orig = submitBtn.innerHTML;
      submitBtn.innerHTML = 'Registrando...';
    }

    try {
      const formData = new FormData(form);
      const response = await fetch(form.action, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const text = await response.text();

      // Detectar si la respuesta es un exito según tu PHP
      const isSuccess = /✅|Funcionario registrado correctamente|registrado correctamente/i.test(text);

      // Mostrar modal usando SweetAlert2
      if (window.Swal) {
        Swal.fire({
          title: isSuccess ? '✅ Registro exitoso' : 'Resultado',
          html: text,
          icon: isSuccess ? 'success' : 'info',
          width: swalWidth,
          padding: '1rem',
          confirmButtonText: 'Aceptar',
          backdrop: 'rgba(0,0,0,0.5)'
        });
      } else {
        alert((isSuccess ? 'Registro exitoso\n\n' : 'Respuesta:\n\n') + text.replace(/<[^>]*>/g, '').trim());
      }

      // Si fue éxito, limpiar formulario y clases de validación
      if (isSuccess) {
        form.reset();
        // Limpiar clases de validación
        const campos = [nombreInput, documentoInput, correoInput, telefonoInput, cargoSelect, sedeSelect];
        campos.forEach(campo => {
          if (campo) {
            campo.classList.remove('valido', 'invalido');
          }
        });
      }

    } catch (err) {
      if (window.Swal) {
        Swal.fire({
          title: '❌ Error',
          text: 'Error en la conexión: ' + (err.message || err),
          icon: 'error',
          width: swalWidth
        });
      } else {
        alert('Error en la conexión: ' + (err.message || err));
      }
    } finally {
      // reactivar botón
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = submitBtn.dataset.orig || 'Registrar';
        delete submitBtn.dataset.orig;
      }
    }
  });
});