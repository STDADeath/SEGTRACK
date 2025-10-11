document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formDispositivo');

    form.addEventListener('submit', function (event) {
        event.preventDefault(); // ❌ Evita recargar la página

        // 🔍 Obtenemos los valores del formulario
        const tipo = document.getElementById('TipoDispositivo').value;
        const otroTipo = document.querySelector('input[name="OtroTipoDispositivo"]')?.value.trim() || "";
        const marca = document.querySelector('input[name="MarcaDispositivo"]').value.trim();
        const idFuncionario = document.querySelector('input[name="IdFuncionario"]').value.trim();
        const idVisitante = document.querySelector('input[name="IdVisitante"]').value.trim();

        // 🧩 Expresiones regulares para validar texto y números
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

        if (idFuncionario !== "" && !regexNumero.test(idFuncionario)) {
            alert('❌ Error: El campo ID Funcionario solo puede contener números.');
            return;
        }

        if (idVisitante !== "" && !regexNumero.test(idVisitante)) {
            alert('❌ Error: El campo ID Visitante solo puede contener números.');
            return;
        }

        // 🧠 Preparamos los datos para enviar al controlador
        const formData = new FormData(form);

        // Si eligió "Otro", reemplazamos el tipo por el valor del campo
        if (tipo === "Otro") {
            formData.set("TipoDispositivo", otroTipo);
        }

        // Acción que el backend usará para decidir qué hacer
        formData.append("accion", "registrar");

        // 🚀 Enviamos con AJAX (fetch)
        fetch('../../Controller/parqueadero_dispositivo/ControladorDispositivo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("✅ Respuesta del servidor:", data);

            if (data.success) {
                alert(data.message); // ✅ Éxito
                form.reset(); // Limpia el formulario
                document.getElementById("campoOtro").style.display = "none";
            } else {
                alert(`⚠️ ${data.message}`);
            }
        })
        .catch(error => {
            console.error('❌ Error al conectar con el servidor:', error);
            alert('❌ Hubo un error al conectar con el servidor. Verifica la ruta o la conexión.');
        });
    });

    // 🎛️ Mostrar/Ocultar campo "Otro"
    document.getElementById("TipoDispositivo").addEventListener("change", function() {
        document.getElementById("campoOtro").style.display = this.value === "Otro" ? "block" : "none";
    });
});
