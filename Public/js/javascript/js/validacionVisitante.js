$(document).ready(function () {

    // ── DataTable solo si existe la tabla ──────────
    if ($("#tablaVisitantesDT").length) {
        $('#tablaVisitantesDT').DataTable({
            language: { url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json" },
            pageLength: 10,
            responsive: true,
            order: [[0, "desc"]]
        });
    }

    // ── Formulario solo si existe ──────────────────
    if ($("#formRegistrarVisitante").length) {

        // Solo letras en nombre
        $("#NombreVisitante").on("input", function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, "");
        });

        // Validación dinámica de identificación
        $("#IdentificacionVisitante").on("input", function () {
            let valor = $(this).val();
            if (/^\d+$/.test(valor)) {
                this.value = valor.replace(/\D/g, "");
            } else {
                this.value = valor.replace(/[^a-zA-Z0-9\-]/g, "");
            }
        });

        // Submit
        $("#formRegistrarVisitante").submit(function (e) {
            e.preventDefault();

            const id     = $("#IdentificacionVisitante").val().trim();
            const nombre = $("#NombreVisitante").val().trim();
            const correo = $("#CorreoVisitante").val().trim();

            // Detectar tipo CC o CE
            let tipo = "";
            if (/^\d{6,10}$/.test(id)) {
                tipo = "CC";
            } else if (/^[A-Za-z0-9\-]{4,20}$/.test(id)) {
                tipo = "CE";
            } else {
                Swal.fire({ icon: 'error', title: 'Documento inválido', text: 'Ingrese una CC (6-10 números) o CE (letras/números/guiones).', confirmButtonColor: '#e74a3b' });
                return;
            }

            if (!/^[a-zA-ZÀ-ÿ\s]{3,50}$/.test(nombre)) {
                Swal.fire({ icon: 'error', title: 'Nombre inválido', text: 'El nombre solo debe contener letras (mínimo 3 caracteres).', confirmButtonColor: '#e74a3b' });
                return;
            }

            if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
                Swal.fire({ icon: 'error', title: 'Correo inválido', text: 'El correo electrónico no es válido.', confirmButtonColor: '#e74a3b' });
                return;
            }

            const btn      = $(this).find('button[type="submit"]');
            const original = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...').prop("disabled", true);

            $.ajax({
                url:      "../../Controller/ControladorVisitante.php",
                type:     "POST",
                data:     $(this).serialize() + "&accion=registrar&TipoDetectado=" + tipo,
                dataType: "json",
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success', title: '¡Visitante registrado!',
                            text: res.message, timer: 3000, timerProgressBar: true,
                            showConfirmButton: true, confirmButtonColor: '#1cc88a',
                            confirmButtonText: 'Entendido'
                        }).then(() => { $("#formRegistrarVisitante")[0].reset(); });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'No se pudo registrar', text: res.message, confirmButtonColor: '#f6c23e', confirmButtonText: 'Entendido' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#e74a3b' });
                },
                complete: function () {
                    btn.html(original).prop("disabled", false);
                }
            });
        });
    }

});