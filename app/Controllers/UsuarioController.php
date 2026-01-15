<?php
class UsuarioController {
    private $modeloUsuario;

    public function __construct() {
        $this->modeloUsuario = new Usuario();
    }

    public function iniciarSesion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $pass = $_POST['password'];

            $usuario = $this->modeloUsuario->autenticar($email, $pass);

            if ($usuario) {
                session_start();
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['rol_id'] = $usuario['rol_id'];
                $_SESSION['email'] = $usuario['email'];

                if ($usuario['rol_id'] == 1) {
                    header('Location: dashboard_artista.php');
                } else {
                    header('Location: explorar.php');
                }
                exit();
            } else {
                echo "Credenciales incorrectas. Por favor, inténtelo de nuevo.";
            }
        }
    }
}