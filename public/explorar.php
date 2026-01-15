<?php
require_once '../app/Config/auth_check.php';
if ($_SESSION['rol_id'] != 2) {
    header('Location: dashboard_artista.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<body>
    <h1>Panel de Exploración (Cliente)</h1>
    <p>Bienvenido, <?php echo $_SESSION['email']; ?></p>
    <a href="procesador.php?action=logout">Cerrar Sesión</a>
    <hr>
    <p>Aquí podrás buscar artistas y ver proyectos pronto.</p>
</body>
</html>