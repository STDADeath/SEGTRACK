<?php

require_once __DIR__ . '/../Core/conexion.php';

require_once __DIR__ . '/../Plantilla/parte_superior.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-book me-2"></i>Bitácoras Registradas</h1>
        <a href="../view/Bitacora_Registrar.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Nueva Bitácora
        </a>
    </div>

    <!-- Card de Búsqueda y Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Búsqueda y Filtros</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="buscarBitacora" class="form-label">Buscar en novedades</label>
                    <input type="text" id="buscarBitacora" class="form-control" placeholder="Buscar por novedades...">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="filtroTurno" class="form-label">Filtrar por turno</label>
                    <select id="filtroTurno" class="form-select">
                        <option value="">Todos los turnos</option>
                        <option value="Jornada mañana">Jornada mañana</option>
                        <option value="Jornada tarde">Jornada tarde</option>
                        <option value="Jornada noche">Jornada noche</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="filtroFecha" class="form-label">Filtrar por fecha</label>
                    <input type="date" id="filtroFecha" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button id="btnAplicarFiltros" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                    </button>
                    <button id="btnLimpiarFiltros" class="btn btn-secondary">
                        <i class="fas fa-broom me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card de Resultados -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Bitácoras</h6>
            <span id="contadorResultados" class="badge bg-primary">Cargando...</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Turno</th>
                            <th>Novedades</th>
                            <th>Fecha y Hora</th>
                            <th>ID Funcionario</th>
                            <th>ID Ingreso</th>
                            <th>ID Visitante</th>
                            <th>ID Dispositivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBitacoras">
                        <!-- Los datos se cargarán via AJAX -->
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando bitácoras...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <nav aria-label="Paginación de bitácoras">
                <ul class="pagination justify-content-center" id="paginacion">
                    <!-- La paginación se generará via JavaScript -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalles -->
<div class="modal fade" id="modalVerDetalles" tabindex="-1" role="dialog" aria-labelledby="modalVerDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalVerDetallesLabel">Detalles de Bitácora</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detallesBitacora">
                <!-- Los detalles se cargarán aquí -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog" aria-labelledby="confirmarEliminarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar esta bitácora? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    let bitacoraIdAEliminar = null;
    let paginaActual = 1;
    const resultadosPorPagina = 10;

    // Cargar bitácoras al iniciar
    cargarBitacoras();

    // Función para cargar bitácoras con filtros
    function cargarBitacoras(pagina = 1) {
        paginaActual = pagina;
        
        const filtros = {
            buscar: $('#buscarBitacora').val(),
            turno: $('#filtroTurno').val(),
            fecha: $('#filtroFecha').val(),
            pagina: pagina,
            porPagina: resultadosPorPagina
        };

        $.ajax({
            url: "../controller/bitacora_dotacion/controladorBitacora.php",
            type: "POST",
            data: { ...filtros, accion: "mostrar" },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    mostrarBitacoras(response.data);
                    actualizarPaginacion(response.total, response.paginas);
                    $('#contadorResultados').text(`${response.total} bitácoras encontradas`);
                } else {
                    $('#tablaBitacoras').html(`
                        <tr>
                            <td colspan="9" class="text-center py-4 text-danger">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <p>Error al cargar las bitácoras: ${response.message}</p>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function (xhr, status, error) {
                $('#tablaBitacoras').html(`
                    <tr>
                        <td colspan="9" class="text-center py-4 text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <p>Error de conexión: ${error}</p>
                        </td>
                    </tr>
                `);
            }
        });
    }

    // Mostrar bitácoras en la tabla
    function mostrarBitacoras(bitacoras) {
        if (bitacoras.length === 0) {
            $('#tablaBitacoras').html(`
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="fas fa-search fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No se encontraron bitácoras con los filtros aplicados</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        bitacoras.forEach(bitacora => {
            html += `
                <tr id="fila-${bitacora.IdBitacora}">
                    <td>${bitacora.IdBitacora}</td>
                    <td><span class="badge bg-primary">${bitacora.TurnoBitacora}</span></td>
                    <td>
                        <div class="novedades-truncate" title="${bitacora.NovedadesBitacora}">
                            ${bitacora.NovedadesBitacora.length > 50 ? 
                              bitacora.NovedadesBitacora.substring(0, 50) + '...' : 
                              bitacora.NovedadesBitacora}
                        </div>
                    </td>
                    <td>${formatearFecha(bitacora.FechaBitacora)}</td>
                    <td>${bitacora.IdFuncionario || '-'}</td>
                    <td>${bitacora.IdIngreso}</td>
                    <td>${bitacora.IdVisitante || '-'}</td>
                    <td>${bitacora.IdDispositivo || '-'}</td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="verDetalles(${bitacora.IdBitacora})"
                                    title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                    onclick="editarBitacora(${bitacora.IdBitacora})"
                                    title="Editar bitácora">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="confirmarEliminacion(${bitacora.IdBitacora})"
                                    title="Eliminar bitácora">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        $('#tablaBitacoras').html(html);
    }

    // Actualizar paginación
    function actualizarPaginacion(total, totalPaginas) {
        if (totalPaginas <= 1) {
            $('#paginacion').html('');
            return;
        }

        let html = '';
        
        // Botón anterior
        if (paginaActual > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1})">Anterior</a></li>`;
        }

        // Números de página
        for (let i = 1; i <= totalPaginas; i++) {
            html += `
                <li class="page-item ${i === paginaActual ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>
                </li>
            `;
        }

        // Botón siguiente
        if (paginaActual < totalPaginas) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1})">Siguiente</a></li>`;
        }

        $('#paginacion').html(html);
    }

    // Formatear fecha
    function formatearFecha(fecha) {
        return new Date(fecha).toLocaleString('es-ES');
    }

    // Eventos de filtros
    $('#btnAplicarFiltros').click(function() {
        cargarBitacoras(1);
    });

    $('#btnLimpiarFiltros').click(function() {
        $('#buscarBitacora').val('');
        $('#filtroTurno').val('');
        $('#filtroFecha').val('');
        cargarBitacoras(1);
    });

    // Buscar al presionar Enter
    $('#buscarBitacora').keypress(function(e) {
        if (e.which === 13) {
            cargarBitacoras(1);
        }
    });

    // Funciones globales
    window.cambiarPagina = function(pagina) {
        cargarBitacoras(pagina);
    };

    window.verDetalles = function(id) {
        $.ajax({
            url: "../controller/bitacora_dotacion/controladorBitacora.php",
            type: "POST",
            data: { IdBitacora: id, accion: "obtener" },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    const bitacora = response.data;
                    $('#detallesBitacora').html(`
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID:</strong> ${bitacora.IdBitacora}</p>
                                <p><strong>Turno:</strong> ${bitacora.TurnoBitacora}</p>
                                <p><strong>Fecha:</strong> ${formatearFecha(bitacora.FechaBitacora)}</p>
                                <p><strong>ID Funcionario:</strong> ${bitacora.IdFuncionario || 'No asignado'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>ID Ingreso:</strong> ${bitacora.IdIngreso}</p>
                                <p><strong>ID Visitante:</strong> ${bitacora.IdVisitante || 'No aplica'}</p>
                                <p><strong>ID Dispositivo:</strong> ${bitacora.IdDispositivo || 'No aplica'}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Novedades:</strong></p>
                                <div class="border p-3 bg-light">
                                    ${bitacora.NovedadesBitacora}
                                </div>
                            </div>
                        </div>
                    `);
                    $('#modalVerDetalles').modal('show');
                } else {
                    alert('Error al cargar los detalles: ' + response.message);
                }
            }
        });
    };

    window.confirmarEliminacion = function(id) {
        bitacoraIdAEliminar = id;
        $('#confirmarEliminarModal').modal('show');
    };

    window.editarBitacora = function(id) {
        // Redirigir a página de edición o abrir modal de edición
        alert('Funcionalidad de edición en desarrollo. ID: ' + id);
        // window.location.href = `Bitacora_Editar.php?id=${id}`;
    };

    // Eliminar bitácora
    $('#btnConfirmarEliminar').click(function() {
        if (bitacoraIdAEliminar) {
            $.ajax({
                url: "../controller/bitacora_dotacion/controladorBitacora.php",
                type: "POST",
                data: { 
                    IdBitacora: bitacoraIdAEliminar, 
                    accion: "eliminar" 
                },
                dataType: "json",
                success: function (response) {
                    $('#confirmarEliminarModal').modal('hide');
                    if (response.success) {
                        alert('Bitácora eliminada correctamente');
                        $('#fila-' + bitacoraIdAEliminar).remove();
                        cargarBitacoras(paginaActual); // Recargar para actualizar contador
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    $('#confirmarEliminarModal').modal('hide');
                    alert('Error al intentar eliminar la bitácora');
                }
            });
        }
    });
});

// Estilos CSS adicionales
const style = document.createElement('style');
style.textContent = `
    .novedades-truncate {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: help;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.1);
    }
`;
document.head.appendChild(style);
</script>

<?php require_once __DIR__ . '/../Plantilla/parte_inferior.php'; ?>