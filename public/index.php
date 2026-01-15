<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] == 1) {
        header('Location: dashboard_artista.php');
    } else {
        header('Location: explorar.php');
    }
} else {
    header('Location: login.php');
}
exit();