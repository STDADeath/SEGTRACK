<?php
session_start();
$_SESSION = [];
session_destroy();

// Evitar que vuelva con el botón atrás
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: ../../View/Login/Login.php");
exit;
