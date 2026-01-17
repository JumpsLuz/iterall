<?php
class UsuarioController {
    private $modeloUsuario;
    private $db;

    public function __construct() {
        $this->modeloUsuario = new Usuario();
        $this->db = Database::getInstance();
    }

    public function iniciarSesion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $pass = $_POST['password'];

            $usuario = $this->modeloUsuario->autenticar($email, $pass);

            if ($usuario) {
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
                header('Location: index.php?error=1');
            }
        }
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];

            $sqlCheck = "SELECT id FROM usuarios WHERE email = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$email]);
            
            if ($stmtCheck->fetch()) {
                header('Location: registro.php?error=email_exists');
                exit();
            }

            session_start();
            $exito = $this->modeloUsuario->registrar(
                $email, 
                $_POST['password'], 
                $_POST['rol_id']
            );

            if ($exito) {
                $usuario = $this->modeloUsuario->autenticar($email, $_POST['password']);
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['rol_id'] = $usuario['rol_id'];
                $_SESSION['email'] = $usuario['email'];

                header('Location: ' . ($usuario['rol_id'] == 1 ? 'completar_perfil.php' : 'explorar.php'));
                exit();
            } else {
                header('Location: registro.php?error=1');
                exit();
            }
        }
    }
}
