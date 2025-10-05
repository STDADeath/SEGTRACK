// ✅ Esperar a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function () {

    // Obtener los elementos del formulario
    const formulario = document.getElementById("loginForm");
    const correoInput = document.getElementById("correo");
    const contrasenaInput = document.getElementById("contrasena");
    const errorCorreo = document.getElementById("errorCorreo");
    const errorContrasena = document.getElementById("errorContrasena");

    // Expresión regular para validar el formato del correo
    const correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // ✅ Validar en tiempo real el correo electrónico
    correoInput.addEventListener("input", () => {
        const valor = correoInput.value.trim();

        if (valor === "") {
            correoInput.classList.remove("valido", "invalido");
            errorCorreo.textContent = "";
            return;
        }

        if (correoRegex.test(valor)) {
            correoInput.classList.add("valido");
            correoInput.classList.remove("invalido");
            errorCorreo.textContent = "Correo válido ✔️";
            errorCorreo.style.color = "green";
        } else {
            correoInput.classList.add("invalido");
            correoInput.classList.remove("valido");
            errorCorreo.textContent = "El correo debe contener '@' y '.'";
            errorCorreo.style.color = "red";
        }
    });

    // ✅ Validación al enviar el formulario
    formulario.addEventListener("submit", function (e) {
        const correoValor = correoInput.value.trim();
        const contrasenaValor = contrasenaInput.value.trim();

        // Evitar envío si hay errores
        if (!correoRegex.test(correoValor) || contrasenaValor === "") {
            e.preventDefault(); // Detener el envío del formulario

            // Mostrar alerta con SweetAlert2
            Swal.fire({
                icon: "error",
                title: "Campos inválidos",
                text: "Verifica que el correo y la contraseña sean correctos.",
                confirmButtonColor: "#d33",
            });

            // Marcar campos incorrectos visualmente
            if (!correoRegex.test(correoValor)) {
                correoInput.classList.add("invalido");
                errorCorreo.textContent = "Formato de correo incorrecto";
                errorCorreo.style.color = "red";
            }

            if (contrasenaValor === "") {
                contrasenaInput.classList.add("invalido");
                errorContrasena.textContent = "Ingrese su contraseña";
                errorContrasena.style.color = "red";
            }
        }
    });
});
