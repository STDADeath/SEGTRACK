        </div>
        <!-- Fin container -->

    </div>
    <!-- Fin contenido -->

    <!-- ==============================
    📌 FOOTER
    ============================== -->
    <footer class="sticky-footer bg-primary">
        <div class="container my-auto">
            <div class="text-center text-white">
                © 2025 SEGTRACK | Innovación y Seguridad Inteligente.
            </div>
        </div>
    </footer>

</div>
</div>

<!-- ==============================
🔝 BOTÓN SUBIR
============================== -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- ==============================
🔐 MODAL LOGOUT (FUNCIONAL)
============================== -->
<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Cerrar sesión</h5>
                <button class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                ¿Seguro que deseas cerrar sesión?
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>

                <!-- 🔥 ESTE ES EL QUE FUNCIONA -->
                <a class="btn btn-primary" href="../Login/logout.php">
                    Cerrar sesión
                </a>
            </div>

        </div>
    </div>
</div>

<!-- ==============================
📦 SCRIPTS (SOLO UNA VEZ)
============================== -->
<script src="../../../Public/vendor/jquery/jquery.min.js"></script>
<script src="../../../Public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../../Public/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../../../Public/js/sb-admin-2.min.js"></script>

</body>
</html>