<?php 
session_start();
require_once '../vendor/autoload.php';
require_once '../app/Models/Usuario.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';
require_once '../app/Controllers/ProyectoController.php';
require_once '../app/Controllers/UsuarioController.php';
require_once '../app/Controllers/PostController.php';

$action = $_GET['action'] ?? '';

$accionesPublicas = ['login', 'registrar'];

if (!in_array($action, $accionesPublicas) && !isset($_SESSION['usuario_id'])) {
    header('Location: login.php?error=sesion_expirada');
    exit();
}

if ($action === 'registrar') {
    $controller = new UsuarioController();
    $controller->registrar();
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
        header('Location: dashboard_artista.php?mensaje=perfil_actualizado');
        exit();
    } else {
        header('Location: completar_perfil.php?error=actualizar_perfil');
        exit();
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

if ($action === 'crear_post') {
    $controller = new PostController();
    $controller->crear();
}

if ($action === 'crear_post_rapido') {
    $controller = new PostController();
    $controller->crearRapido();
}

if ($action === 'toggle_destacado') {
    $controller = new PostController();
    $controller->alternarDestacado();
}

if ($action === 'eliminar_post') {
    $controller = new PostController();
    $controller->eliminar();
}
