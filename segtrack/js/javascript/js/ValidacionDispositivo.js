// Espera a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formDispositivo');

    form.addEventListener('submit', function (event) {
        event.preventDefault(); // ❌ Evita recargar la página

        // Obtenemos los valores
        const tipo = document.getElementById('TipoDispositivo').value;
        const otroTipo = document.querySelector('input[name="OtroTipoDispositivo"]')?.value.trim() || "";
        const marca = document.querySelector('input[name="MarcaDispositivo"]').value.trim();
        const idFuncionario = document.querySelector('input[name="IdFuncionario"]').value.trim();
        const idVisitante = document.querySelector('input[name="IdVisitante"]').value.trim();

        // Expresiones regulares
        const regexTexto = /^[a-zA-Z0-9\s.,-]*$/;
        const regexNumero = /^\d+$/;

        // ✅ Validación de tipo de dispositivo
        if (tipo === "") {
            alert('❌ Error: Debe seleccionar un tipo de dispositivo.');
            return;
        }

        if (tipo === "Otro" && (otroTipo === "" || !regexTexto.test(otroTipo))) {
            alert('❌ Error: Debe especificar un tipo válido en "Otro".');
            return;
        }

        // ✅ Validación de marca
        if (!regexTexto.test(marca) || marca === "") {
            alert('❌ Error: El campo Marca solo puede contener letras, números, espacios y algunos símbolos (.,-).');
            return;
        }

        // ✅ Validación de IDs (solo uno debe estar lleno)
        if ((idFuncionario === "" && idVisitante === "") || (idFuncionario !== "" && idVisitante !== "")) {
            alert('❌ Error: Debe ingresar solo un ID: Funcionario o Visitante.');
            return;
        }

        // ✅ Validar formato de ID Funcionario si se llenó
        if (idFuncionario !== "" && !regexNumero.test(idFuncionario)) {
            alert('❌ Error: El campo ID Funcionario solo puede contener números.');
            return;
        }

        // ✅ Validar formato de ID Visitante si se llenó
        if (idVisitante !== "" && !regexNumero.test(idVisitante)) {
            alert('❌ Error: El campo ID Visitante solo puede contener números.');
            return;
        }
            // ✅ Preparar los datos del formulario
            const formData = new FormData(form);

            // Si eligió "Otro", reemplazamos el tipo en el formData
            if (tipo === "Otro") {
                formData.set("TipoDispositivo", otroTipo);
            }

            // 👇 Muy importante: enviamos la acción al backend
            formData.append("accion", "registrar");

            // ✅ Enviar con fetch (AJAX)
            fetch("../Controller/parqueadero_dispositivo/ControladorDispositivo.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json()) // 👈 Parsear como JSON
            .then(data => {
                console.log(data); // Para depuración en consola

                if (data.success) {
                    alert(data.message); // ✅ Muestra solo el mensaje
                    form.reset();
                    document.getElementById("campoOtro").style.display = "none"; // Ocultar campo extra
                } else {
                    alert(data.message); // ❌ Muestra solo el mensaje de error
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Hubo un error al registrar el dispositivo');
            });

    });
    // Mostrar/Ocultar campo "Otro" según selección
    document.getElementById("TipoDispositivo").addEventListener("change", function() {
        document.getElementById("campoOtro").style.display = this.value === "Otro" ? "block" : "none";
    });
});
