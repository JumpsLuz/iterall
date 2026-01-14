<?php 
session_start();
require_once '../vendor/autoload.php';
require_once '../app/Models/Usuario.php';
require_once '../app/Models/Database.php';
require_once '../app/Controllers/UsuarioController.php';

$controller = new UsuarioController();
$action = $_GET['action'] ?? '';

if ($action === 'registrar') {
    $modelo = new Usuario();
    if ($_POST['email'] && $_POST['password'] && $_POST['rol_id']) {
        echo "Registro recibido";
    } else {
        echo "Error en el registro";
    }
}

if ($action === 'login') {
    $controller->iniciarSesion();
}