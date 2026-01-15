<?php 
session_start();
require_once '../vendor/autoload.php';
require_once '../app/Models/Usuario.php';
require_once '../app/Config/Database.php';
require_once '../app/Controllers/UsuarioController.php';

$controller = new UsuarioController();
$action = $_GET['action'] ?? '';

if ($action === 'registrar') {
    $modelo = new Usuario();
    if ($_POST['email'] && $_POST['password'] && $_POST['rol_id']) {
        $resultado = $modelo->registrar($_POST['email'], $_POST['password'], $_POST['rol_id']);
        if ($resultado) {
            header('Location: login.php?registro=exitoso');
            exit();
        } else {
            header('Location: registro.php?error=fallo_registro');
            exit();
        }}
}

if ($action === 'login') {
    $controller = new UsuarioController();
    $controller->iniciarSesion();
}

if ($action === 'logout') {
    session_unset(); 
    session_destroy(); 
    header("Location: login.php?mensaje=sesion_cerrada");
    exit();
}

if ($action === 'actualizar_perfil') {
    $modelo = new Usuario();
    $exito = $modelo->actualizarPerfil(
        $_SESSION['usuario_id'],
        $_POST['nombre_artistico'],
        $_POST['biografia'],
        $_POST['redes']
    );

    if ($exito) {
        header('Location: dashboard_artista.php?mensaje=perfil_actualizado');
        exit();
    } else {
        echo "Error al actualizar el perfil.";
    }
}

