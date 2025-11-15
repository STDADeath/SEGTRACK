document.addEventListener("DOMContentLoaded", function () {
    const formulario = document.getElementById("loginForm");
    const correoInput = document.getElementById("correo");
    const contrasenaInput = document.getElementById("contrasena");
    const errorCorreo = document.getElementById("errorCorreo");
    const errorContrasena = document.getElementById("errorContrasena");

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

    // ✅ Envío controlado con Fetch (sin recargar localhost)
    formulario.addEventListener("submit", async function (e) {
        e.preventDefault(); // Evitar recarga

        const correo = correoInput.value.trim();
        const contrasena = contrasenaInput.value.trim();

        // Validaciones previas
        if (!correoRegex.test(correo) || contrasena === "") {
            Swal.fire({
                icon: "error",
                title: "Campos inválidos",
                text: "Verifica que el correo y la contraseña sean correctos.",
                confirmButtonColor: "#d33",
            });
            return;
        }

        try {
            // ✅ Enviar los datos al servidor por AJAX (sin refrescar)
            const respuesta = await fetch("avalidar.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `correo=${encodeURIComponent(correo)}&contrasena=${encodeURIComponent(contrasena)}`
            });

            const data = await respuesta.json();

            if (data.success) {
                // ✅ Mostrar alerta personalizada con el nombre
                Swal.fire({
                    icon: "success",
                    title: `¡Bienvenido ${data.nombre}! 🎉`,
                    text: "Inicio de sesión exitoso.",
                    showConfirmButton: false,
                    timer: 2500
                }).then(() => {
                    // Redirigir según su rol
                    window.location.href = data.redirect;
                });
            } else {
                // ❌ Error en login
                Swal.fire({
                    icon: "error",
                    title: "Credenciales incorrectas",
                    text: "Correo o contraseña no válidos.",
                    confirmButtonColor: "#d33"
                });
            }
        } catch (error) {
            console.error("Error en el login:", error);
            Swal.fire({
                icon: "error",
                title: "Error del servidor",
                text: "Intenta nuevamente más tarde.",
            });
        }
    });
});
