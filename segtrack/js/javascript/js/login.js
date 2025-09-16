  document.getElementById("loginForm").addEventListener("submit", function(e) {
            let valido = true;

            // Obtener valores
            const correo = document.getElementById("correo");
            const contrasena = document.getElementById("contrasena");
            const errorCorreo = document.getElementById("errorCorreo");
            const errorContrasena = document.getElementById("errorContrasena");

            // Expresiones regulares
            const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Resetear mensajes
            errorCorreo.textContent = "";
            errorContrasena.textContent = "";

            // Validar correo
            if (!regexCorreo.test(correo.value.trim())) {
                errorCorreo.textContent = "Ingrese un correo válido.";
                correo.style.border = "2px solid red";
                valido = false;
            } else {
                correo.style.border = "2px solid green";
            }

            // Validar contraseña
            if (contrasena.value.trim().length < 6) {
                errorContrasena.textContent = "La contraseña debe tener mínimo 6 caracteres.";
                contrasena.style.border = "2px solid red";
                valido = false;
            } else {
                contrasena.style.border = "2px solid green";
            }

            // Si hay errores, no enviar
            if (!valido) {
                e.preventDefault();
            }
        });