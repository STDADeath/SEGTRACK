// ============================================================
// listageneral.js — JS unificado para vistas de solo lectura
// Cubre: InstitutoLista.php y SedeLista.php
// ============================================================

console.log("✅ listageneral.js OK");

$(document).ready(function () {

    function idiomaES(entidad) {
        return {
            emptyTable:   'No hay ' + entidad + ' registradas con los filtros seleccionados',
            info:         'Mostrando _START_ a _END_ de _TOTAL_ ' + entidad,
            infoEmpty:    'Mostrando 0 a 0 de 0 ' + entidad,
            infoFiltered: '(filtrado de _MAX_ ' + entidad + ')',
            lengthMenu:   'Mostrar _MENU_ ' + entidad,
            search:       'Buscar:',
            zeroRecords:  'No se encontraron resultados',
            paginate: {
                first:    'Primera',
                last:     'Última',
                next:     'Siguiente',
                previous: 'Anterior'
            }
        };
    }

    var opcionesBase = {
        destroy:    true,
        ordering:   false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        responsive: true
    };

    // ── TABLA INSTITUCIONES ──────────────────────────────────
    if ($('#tablaInstitutos').length) {
        $('#tablaInstitutos').DataTable(
            $.extend({}, opcionesBase, { language: idiomaES('instituciones') })
        );
    }



    

    // ── TABLA SEDES + cascada ────────────────────────────────
    if ($('#tablaSedes').length) {

        $('#tablaSedes').DataTable(
            $.extend({}, opcionesBase, { language: idiomaES('sedes') })
        );

        // ── Cascada: cambio institución → repobla sedes ──────
        $('#filtroInstituto').on('change', function () {

            // FIX DEFINITIVO: las claves del JSON siempre son STRING
            // aunque en PHP sean enteros. Nunca usar parseInt aquí.
            const clave = String($(this).val()).trim();
            const $sede = $('#filtroSede');

            $sede.empty().append('<option value="">Todas</option>');

            if (!clave || clave === '0' || clave === '') {
                return;
            }

            const sedes = SEDES_POR_INSTITUCION[clave] || [];

            if (sedes.length === 0) {
                $sede.append('<option value="" disabled>Sin sedes asociadas</option>');
            } else {
                sedes.forEach(function (s) {
                    $sede.append(
                        '<option value="' + s.IdSede + '">' + s.NombreSede + '</option>'
                    );
                });
            }
        });

        // ── Restaurar al cargar si vienen filtros GET ────────
        const institutoActual = String($('#filtroInstituto').val()).trim();

        if (institutoActual && institutoActual !== '0' && institutoActual !== '') {

            $('#filtroInstituto').trigger('change');

            const sedeActual = $('#filtroSede').data('selected');
            if (sedeActual) {
                setTimeout(function () {
                    $('#filtroSede').val(String(sedeActual));
                }, 200);
            }
        }
    }

});