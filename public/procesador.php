<?php 
session_start();
require_once '../vendor/autoload.php';
require_once '../app/Models/Usuario.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';
require_once '../app/Controllers/ProyectoController.php';
require_once '../app/Controllers/UsuarioController.php';

$controller = new UsuarioController();
$action = $_GET['action'] ?? '';



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

 if ($action === 'go_home') {
    if ($_SESSION['rol_id'] == 1) {
        header('Location: dashboard_artista.php');
    } else {
        header('Location: explorar.php');
    }
    exit();
 }
 if ($action === 'crear_proyecto') {
    $controller = new ProyectoController();
    $controller->crear();
}

if ($action === 'editar_proyecto') {
    $controller = new ProyectoController();
    $controller->editar();
}

if ($action === 'eliminar_proyecto') {
    $controller = new ProyectoController();
    $controller->eliminar();
}
