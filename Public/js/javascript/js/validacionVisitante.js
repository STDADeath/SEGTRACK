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

        const CONTROLLER = "../../Controller/ControladorVisitante.php";

        // Control de duplicados detectados en tiempo real
        const duplicados = { identificacion: false, correo: false };

        // ── Helpers visuales ──────────────────────────
        function marcarError(selector, mensaje) {
            const $input = $(selector);
            $input.removeClass("is-valid").addClass("is-invalid");
            $input.siblings(".invalid-feedback").remove();
            $input.after(`<div class="invalid-feedback">${mensaje}</div>`);
        }

        function marcarOk(selector) {
            $(selector).removeClass("is-invalid").addClass("is-valid")
                       .siblings(".invalid-feedback").remove();
        }

        function limpiarMarca(selector) {
            $(selector).removeClass("is-valid is-invalid")
                       .siblings(".invalid-feedback").remove();
        }

        // ══════════════════════════════════════════════
        // CARGAR INSTITUCIONES AL INICIAR
        // ══════════════════════════════════════════════
        $.ajax({
            url:      CONTROLLER,
            type:     "POST",
            data:     { accion: "obtener_instituciones" },
            dataType: "json",
            success: function (res) {
                if (Array.isArray(res) && res.length > 0) {
                    res.forEach(function (inst) {
                        $("#IdInstitucion").append(
                            `<option value="${inst.IdInstitucion}">${inst.NombreInstitucion}</option>`
                        );
                    });
                }
            }
        });

        // ══════════════════════════════════════════════
        // CASCADA: INSTITUCIÓN → SEDE
        // ══════════════════════════════════════════════
        $("#IdInstitucion").on("change", function () {
            const idInstitucion = $(this).val();
            const $selectSede   = $("#IdSede");
            const $spinner      = $("#spinnerSede");

            $selectSede.html('<option value="">Seleccione una sede...</option>')
                       .prop("disabled", true);
            limpiarMarca("#IdSede");

            if (!idInstitucion) {
                $selectSede.html('<option value="">Primero seleccione una institución...</option>');
                limpiarMarca("#IdInstitucion");
                return;
            }

            marcarOk("#IdInstitucion");
            $spinner.removeClass("d-none");

            $.ajax({
                url:      CONTROLLER,
                type:     "POST",
                data:     { accion: "obtener_sedes", IdInstitucion: idInstitucion },
                dataType: "json",
                success: function (res) {
                    $spinner.addClass("d-none");
                    if (res.success && res.sedes.length > 0) {
                        $selectSede.prop("disabled", false);
                        res.sedes.forEach(function (sede) {
                            $selectSede.append(
                                `<option value="${sede.IdSede}">${sede.TipoSede} – ${sede.Ciudad}</option>`
                            );
                        });
                    } else {
                        $selectSede.html('<option value="">No hay sedes disponibles</option>');
                        marcarError("#IdSede", "Esta institución no tiene sedes activas.");
                    }
                },
                error: function () {
                    $spinner.addClass("d-none");
                    marcarError("#IdSede", "No se pudieron cargar las sedes. Intente nuevamente.");
                }
            });
        });

        // Validar sede al cambiar
        $("#IdSede").on("change", function () {
            $(this).val() ? marcarOk(this) : limpiarMarca(this);
        });

        // ── Solo números, máximo 11 dígitos ──────────
        $("#IdentificacionVisitante").on("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 11);
            limpiarMarca(this);
            duplicados.identificacion = false;
        });

        $("#IdentificacionVisitante").on("blur", function () {
            const val = $(this).val().trim();
            if (!val) return;

            if (!/^\d{6,11}$/.test(val)) {
                marcarError(this, "Ingrese solo números (6 a 11 dígitos).");
                duplicados.identificacion = true;
                return;
            }

            $.ajax({
                url:      CONTROLLER,
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

        // ── Solo letras en nombre ─────────────────────
        $("#NombreVisitante").on("input", function () {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, "");
            limpiarMarca(this);
        });

        $("#NombreVisitante").on("blur", function () {
            const val = $(this).val().trim();
            if (!val) return;
            /^[a-zA-ZÀ-ÿ\s]{3,100}$/.test(val)
                ? marcarOk(this)
                : marcarError(this, "Solo letras, mínimo 3 caracteres.");
        });

        // ── Verificar correo duplicado al salir ───────
        $("#CorreoVisitante").on("blur", function () {
            const val = $(this).val().trim();
            limpiarMarca(this);
            duplicados.correo = false;
            if (!val) return;

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val)) {
                marcarError(this, "Ingrese un correo válido. Ej: correo@dominio.com");
                duplicados.correo = true;
                return;
            }

            $.ajax({
                url:      CONTROLLER,
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

        // ══════════════════════════════════════════════
        // SUBMIT
        // ══════════════════════════════════════════════
        $("#formRegistrarVisitante").submit(function (e) {
            e.preventDefault();

            const id     = $("#IdentificacionVisitante").val().trim();
            const nombre = $("#NombreVisitante").val().trim();
            const correo = $("#CorreoVisitante").val().trim();
            const idInst = $("#IdInstitucion").val();
            const idSede = $("#IdSede").val();

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
            if (!idInst) {
                Swal.fire({ icon: 'warning', title: 'Institución requerida', text: 'Debe seleccionar una institución.', confirmButtonColor: '#f6c23e' });
                $("#IdInstitucion").focus();
                return;
            }
            if (!idSede) {
                Swal.fire({ icon: 'warning', title: 'Sede requerida', text: 'Debe seleccionar una sede.', confirmButtonColor: '#f6c23e' });
                $("#IdSede").focus();
                return;
            }

            const $btn     = $("#btnRegistrar");
            const original = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...').prop("disabled", true);

            $.ajax({
                url:      CONTROLLER,
                type:     "POST",
                data:     $(this).serialize() + "&accion=registrar",
                dataType: "json",
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Visitante registrado!',
                            text:  res.message,
                            timer: 3000,
                            timerProgressBar:   true,
                            showConfirmButton:  true,
                            confirmButtonColor: '#1cc88a',
                            confirmButtonText:  'Entendido'
                        }).then(() => {
                            $("#formRegistrarVisitante")[0].reset();
                            $("#IdSede").html('<option value="">Primero seleccione una institución...</option>')
                                        .prop("disabled", true);
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
                    $btn.html(original).prop("disabled", false);
                }
            });
        });
    }

});