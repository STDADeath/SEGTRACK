
// ===============================
// VALIDACIONES
// ===============================

function validarMin7(texto) {
    return texto.trim().length >= 7;
}

function marcarCampo(input, valido) {
    const label = input.closest(".mb-3").querySelector("label");

    input.classList.remove("input-valid", "input-invalid");
    label.classList.remove("label-valid", "label-invalid");

    if (valido) {
        input.classList.add("input-valid");
        label.classList.add("label-valid");
    } else {
        input.classList.add("input-invalid");
        label.classList.add("label-invalid");
    }
}

// ===============================
// VALIDACIÓN TIEMPO REAL
// ===============================

document.getElementById("tipo_rol").addEventListener("change", function () {
    marcarCampo(this, this.value !== "");
});

document.getElementById("contrasena").addEventListener("input", function () {
    marcarCampo(this, validarMin7(this.value));
});

document.getElementById("id_funcionario").addEventListener("change", function () {
    marcarCampo(this, this.value !== "");
});

// ===============================
// ENVÍO DEL FORMULARIO VIA AJAX
// ===============================

document.getElementById("formUsuario").addEventListener("submit", function(e) {
    e.preventDefault(); // NO RECARGA PÁGINA

    const rol = document.getElementById('tipo_rol');
    const contrasena = document.getElementById('contrasena');
    const funcionario = document.getElementById('id_funcionario');

    let valido = true;

    if (rol.value === "") { marcarCampo(rol, false); valido = false; }
    if (!validarMin7(contrasena.value)) { marcarCampo(contrasena, false); valido = false; }
    if (funcionario.value === "") { marcarCampo(funcionario, false); valido = false; }

    if (!valido) {
        Swal.fire({
            icon: "error",
            title: "Campos inválidos",
            text: "Revisa los campos marcados en rojo"
        });
        return;
    }

    // =================================
    //  ENVÍO AJAX F A L I N A L
    // =================================
    let datos = new FormData(this);

    fetch("../../Controller/ControladorusuarioADM.php", {
        method: "POST",
        body: datos
    })
    .then(resp => resp.json())
    .then(data => {

        if (data.ok) {
            Swal.fire({
                icon: "success",
                title: "Usuario Registrado",
                text: data.mensaje,
                confirmButtonText: "Aceptar"
            }).then(() => {
                document.getElementById("formUsuario").reset();

                [rol, contrasena, funcionario].forEach(i => {
                    i.classList.remove("input-valid", "input-invalid");
                });
            });

        } else {
            Swal.fire({
                icon: "error",
                title: "Error al registrar",
                text: data.mensaje
            });
        }

    })
    .catch(() => {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo contactar con el servidor"
        });
    });

});
