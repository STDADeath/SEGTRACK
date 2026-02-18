/**
 * ========================================
 * VALIDACIONES USUARIO - SEGTRACK
 * ========================================
 */

console.log("✅ ValidacionesUsuario.js cargado");

// ==============================
// TOGGLE CONTRASEÑA
// ==============================
function togglePassword() {
    const input   = document.getElementById('contrasena');
    const eyeIcon = document.getElementById('eye-icon');

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// ==============================
// VALIDAR CAMPO VISUAL
// ==============================
function validarCampo($campo, $label, minLength = 1) {
    const valor = $campo.val().trim();

    if (valor.length >= minLength) {
        $campo.removeClass('input-invalid').addClass('input-valid');
        $label.removeClass('label-invalid').addClass('label-valid');
        return true;
    } else {
        $campo.removeClass('input-valid').addClass('input-invalid');
        $label.removeClass('label-valid').addClass('label-invalid');
        return false;
    }
}

// ==============================
// DOCUMENTO LISTO
// ==============================
$(document).ready(function () {

    const $funcionario = $('#id_funcionario');
    const $tipoRol     = $('#tipo_rol');
    const $contrasena  = $('#contrasena');

    const $labelFuncionario = $('#label_funcionario');
    const $labelTipoRol     = $('#label_tipo_rol');
    const $labelContrasena  = $('#label_contrasena');

    // Validación en tiempo real
    $funcionario.on('change', () => validarCampo($funcionario, $labelFuncionario));
    $tipoRol.on('change',     () => validarCampo($tipoRol,     $labelTipoRol));
    $contrasena.on('input',   () => validarCampo($contrasena,  $labelContrasena, 7));

    // ==============================
    // SUBMIT REGISTRAR USUARIO
    // ==============================
    $('#formUsuario').on('submit', function (e) {
        e.preventDefault();

        const v1 = validarCampo($funcionario, $labelFuncionario);
        const v2 = validarCampo($tipoRol,     $labelTipoRol);
        const v3 = validarCampo($contrasena,  $labelContrasena, 7);

        if (!(v1 && v2 && v3)) {
            Swal.fire('Campos incompletos', 'Por favor completa todos los campos correctamente', 'warning');
            return;
        }

        const btn = $('#formUsuario button[type=submit]');
        const textoOriginal = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Registrando...');

        $.ajax({
            url: '../../Controller/ControladorusuarioADM.php',
            type: 'POST',
            dataType: 'json',
            data: {
                accion:         'registrar',
                id_funcionario:  $funcionario.val(),
                tipo_rol:        $tipoRol.val(),
                contrasena:      $contrasena.val()
            },
            success: function (response) {

                btn.prop('disabled', false).html(textoOriginal);
                console.log("✓ Respuesta registrar:", response);

                if (response.success === true) {
                    Swal.fire({
                        title: '¡Registrado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#1cc88a'
                    }).then(() => {
                        window.location.href = 'UsuariosLista.php';
                    });
                } else {
                    Swal.fire('Error', response.message || 'No se pudo registrar el usuario', 'error');
                }
            },
            error: function (xhr, status, error) {
                btn.prop('disabled', false).html(textoOriginal);
                console.error('❌ Error AJAX registrar:', status, error);
                console.error('Respuesta cruda:', xhr.responseText);
                Swal.fire('Error de conexión', 'No se pudo comunicar con el servidor', 'error');
            }
        });
    });

});