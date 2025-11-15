                </div>
                <!-- Fin del contenido específico (aquí terminan las páginas internas) -->
            </div>
            <!-- End Main Content (fin del área principal donde se muestra la información) -->

            <!-- Footer: barra inferior fija -->
            <!-- Esta sección muestra un pie de página pegado abajo con el fondo azul -->
            <footer class="sticky-footer bg-primary">
                <div class="container my-auto">
                    <div class="copyright text-center text-white">
                        <span>© 2025 SEGTRACK | Innovación y Seguridad Inteligente.</span>
                    </div>
                </div>
            </footer>
            <!-- End Footer -->

        </div>
        <!-- End Content Wrapper (envoltorio del contenido y el footer) -->

    </div>
    <!-- End Page Wrapper (envoltorio de TODO el diseño, sidebar + contenido) -->

    <!-- Botón para volver arriba -->
    <!-- Es el botón flotante circular con la flecha que aparece al bajar scroll -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Modal para cerrar sesión -->
    <!-- Esta ventana emergente aparece cuando el usuario quiere cerrar sesión -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <!-- Encabezado del modal -->
                <div class="modal-header">
                    <h5 class="modal-title">Cerrar sesión</h5>
                    <button class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>

                <!-- Cuerpo del modal (mensaje principal) -->
                <div class="modal-body">¿Seguro que deseas cerrar sesión?</div>

                <!-- Footer del modal (botones Cancelar / Salir) -->
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a class="btn btn-primary" href="login.html">Salir</a>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts principales del proyecto -->
    <!-- jQuery: necesario para el funcionamiento de Bootstrap y SB Admin -->
    <script src="../../../Public/vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap (versión bundle para que funcionen los modales y menús desplegables) -->
    <script src="../../../Public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery Easing: animaciones suaves usadas por SB Admin -->
    <script src="../../../Public/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Chart.js: librería para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- JS principal del tema SB Admin 2 -->
    <!-- Controla el sidebar, las transiciones y componentes del template -->
    <script src="../../../Public/js/sb-admin-2.min.js"></script>

</body>
</html>
