$(document).ready(function () {

    // ------------------------------------------------
    // ACTIVAR DATATABLE SOLO SI LA TABLA EXISTE
    // ------------------------------------------------
    if ($("#tablaVisitantesDT").length) {
        $('#tablaVisitantesDT').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
            },
            pageLength: 10,
            responsive: true,
            order: [[0, "desc"]]
        });
    }

    // ------------------------------------------------
    // VALIDACIONES SOLO SI EXISTE EL FORMULARIO
    // ------------------------------------------------
    if ($("#formRegistrarVisitante").length) {

        // Solo letras en nombre
        $("#NombreVisitante").on("input", function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, "");
        });

        // Validación dinámica según tipo detectado automáticamente
        $("#IdentificacionVisitante").on("input", function () {
            let valor = $(this).val();

            // Si es solo números → CC
            if (/^\d+$/.test(valor)) {
                this.value = valor.replace(/\D/g, "");
            }
            // Si mezcla letras o guiones → CE
            else {
                this.value = valor.replace(/[^a-zA-Z0-9\-]/g, "");
            }
        });

        // Validación completa antes de enviar
        $("#formRegistrarVisitante").submit(function (e) {
            e.preventDefault();

            const id = $("#IdentificacionVisitante").val().trim();
            const nombre = $("#NombreVisitante").val().trim();

            // --------- DETECCIÓN AUTOMÁTICA ---------

            let tipo = "";

            // CC → 6 a 10 números
            if (/^\d{6,10}$/.test(id)) {
                tipo = "CC";
            }
            // CE → letras, números, guiones, 4 a 20 caracteres
            else if (/^[A-Za-z0-9\-]{4,20}$/.test(id)) {
                tipo = "CE";
            }
            else {
                alert("❌ Documento inválido. Ingrese una CC (solo números) o CE (letras/números/guiones).");
                return;
            }

            // Validación nombre
            if (!/^[a-zA-ZÀ-ÿ\s]{3,50}$/.test(nombre)) {
                alert("❌ El nombre solo debe contener letras (mínimo 3 caracteres).");
                return;
            }

            // Botón loading
            const btn = $(this).find('button[type="submit"]');
            const original = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...').prop("disabled", true);

            $.ajax({
                url: "../../Controller/ControladorVisitante.php",
                type: "POST",
                data: $(this).serialize() + "&accion=registrar" + "&TipoDetectado=" + tipo,
                dataType: "json",

                success: function (res) {
                    alert(res.success ? "✅ " + res.message : "❌ " + res.message);
                    if (res.success) $("#formRegistrarVisitante")[0].reset();
                },

                error: function () {
                    alert("❌ Error de conexión con el servidor");
                },

                complete: function () {
                    btn.html(original).prop("disabled", false);
                }
            });
        });
    }

});
