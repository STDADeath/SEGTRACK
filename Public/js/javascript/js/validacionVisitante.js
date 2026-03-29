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

        // Control de estado de duplicados
        const duplicados = { identificacion: false, correo: false };

        // ── Helpers visuales ──────────────────────────
        function marcarError(selector, mensaje) {
            const $input = $(selector);
            $input.removeClass("is-valid").addClass("is-invalid");
            $input.siblings(".invalid-feedback").remove();
            $input.after(`<div class="invalid-feedback">${mensaje}</div>`);
        }

        function marcarOk(selector) {
            const $input = $(selector);
            $input.removeClass("is-invalid").addClass("is-valid");
            $input.siblings(".invalid-feedback").remove();
        }

        function limpiarMarca(selector) {
            $(selector).removeClass("is-valid is-invalid").siblings(".invalid-feedback").remove();
        }

        // ── Solo números, máximo 11 dígitos ──────────
        $("#IdentificacionVisitante").on("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 11);
            limpiarMarca(this);
            duplicados.identificacion = false;
        });

        // ── Verificar identificación duplicada al salir del campo ─────────
        $("#IdentificacionVisitante").on("blur", function () {
            const val = $(this).val().trim();
            if (!val) return;

            if (!/^\d{6,11}$/.test(val)) {
                marcarError(this, "Ingrese solo números (6 a 11 dígitos).");
                duplicados.identificacion = true;
                return;
            }

            $.ajax({
                url:      "../../Controller/ControladorVisitante.php",
                type:     "POST",
                data:     { accion: "verificar", IdentificacionVisitante: val },
                dataType: "json",
                success: (res) => {
                    if (res.duplicado && res.campo === "identificacion") {
                        marcarError("#IdentificacionVisitante", "⚠ Esta identificación ya está registrada.");
                        duplicados.identificacion = true;
                    } else {
                        marcarOk("#IdentificacionVisitante");
                        duplicados.identificacion = false;
                    }
                }
            });
        });

        // ── Solo letras y espacios en nombre ─────────
        $("#NombreVisitante").on("input", function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, "");
            limpiarMarca(this);
        });

        $("#NombreVisitante").on("blur", function () {
            const val = $(this).val().trim();
            if (!val) return;
            if (!/^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(val)) {
                marcarError(this, "Solo letras, mínimo 3 caracteres.");
            } else {
                marcarOk(this);
            }
        });

        // ── Verificar correo duplicado al salir del campo ─────────────────
        $("#CorreoVisitante").on("blur", function () {
            const val = $(this).val().trim();
            limpiarMarca(this);
            duplicados.correo = false;

            if (!val) return; // es opcional

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val)) {
                marcarError(this, "Ingrese un correo válido. Ej: correo@dominio.com");
                duplicados.correo = true;
                return;
            }

            $.ajax({
                url:      "../../Controller/ControladorVisitante.php",
                type:     "POST",
                data:     { accion: "verificar", IdentificacionVisitante: "", CorreoVisitante: val },
                dataType: "json",
                success: (res) => {
                    if (res.duplicado && res.campo === "correo") {
                        marcarError("#CorreoVisitante", "⚠ Este correo ya está registrado.");
                        duplicados.correo = true;
                    } else {
                        marcarOk("#CorreoVisitante");
                        duplicados.correo = false;
                    }
                }
            });
        });

        // ── Submit ────────────────────────────────────
        $("#formRegistrarVisitante").submit(function (e) {
            e.preventDefault();

            const id     = $("#IdentificacionVisitante").val().trim();
            const nombre = $("#NombreVisitante").val().trim();
            const correo = $("#CorreoVisitante").val().trim();

            // Bloquear si hay duplicado detectado en tiempo real
            if (duplicados.identificacion) {
                Swal.fire({ icon: 'error', title: 'Identificación duplicada', text: 'Ya existe un visitante con esa identificación.', confirmButtonColor: '#e74a3b' });
                $("#IdentificacionVisitante").focus();
                return;
            }
            if (duplicados.correo) {
                Swal.fire({ icon: 'error', title: 'Correo duplicado', text: 'Ya existe un visitante con ese correo electrónico.', confirmButtonColor: '#e74a3b' });
                $("#CorreoVisitante").focus();
                return;
            }

            // Validaciones de formato finales
            if (!/^\d{6,11}$/.test(id)) {
                Swal.fire({ icon: 'error', title: 'Identificación inválida', text: 'Ingrese solo números (mínimo 6, máximo 11 dígitos).', confirmButtonColor: '#e74a3b' });
                $("#IdentificacionVisitante").focus();
                return;
            }
            if (!/^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(nombre)) {
                Swal.fire({ icon: 'error', title: 'Nombre inválido', text: 'El nombre solo debe contener letras (mínimo 3 caracteres).', confirmButtonColor: '#e74a3b' });
                $("#NombreVisitante").focus();
                return;
            }
            if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(correo)) {
                Swal.fire({ icon: 'error', title: 'Correo inválido', text: 'Ingrese un correo electrónico válido.', confirmButtonColor: '#e74a3b' });
                $("#CorreoVisitante").focus();
                return;
            }

            const btn      = $(this).find('button[type="submit"]');
            const original = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...').prop("disabled", true);

            $.ajax({
                url:      "../../Controller/ControladorVisitante.php",
                type:     "POST",
                data:     $(this).serialize() + "&accion=registrar&TipoDetectado=CC",
                dataType: "json",
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success', title: '¡Visitante registrado!',
                            text: res.message, timer: 3000, timerProgressBar: true,
                            showConfirmButton: true, confirmButtonColor: '#1cc88a',
                            confirmButtonText: 'Entendido'
                        }).then(() => {
                            $("#formRegistrarVisitante")[0].reset();
                            // Limpiar marcas visuales al resetear
                            $(".is-valid, .is-invalid").removeClass("is-valid is-invalid");
                            $(".invalid-feedback").remove();
                            duplicados.identificacion = false;
                            duplicados.correo         = false;
                        });
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