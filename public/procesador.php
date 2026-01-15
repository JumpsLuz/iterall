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
    $exito = $modelo->registrar($_POST['email'], $_POST['password'], $_POST['rol_id']);

    IF ($exito) {
        $usuario = $modelo->autenticar($_POST['email'], $_POST['password']);
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['rol_id'] = $usuario['rol_id'];

        IF ($usuario['rol_id'] == 1) {
            header('Location: completar_perfil.php');
        } ELSE {
            header('Location: explorar.php');
        }
        exit();
    }
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
        ['instagram' => '', 'artstation' => ''] // Tengo que mejorarlo lvd
    );

    if ($exito) {
        header('Location: dashboard_artista.php');
        exit();
    } else {
        echo "Error al actualizar el perfil.";
    }
}

