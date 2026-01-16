<?php
require_once '../app/Models/Post.php';
require_once '../app/Models/Miniproyecto.php';

class PostController {
    private $modeloPost;
    private $modeloMini;
    private $db;

    public function __construct() {
        $this->modeloPost = new Post();
        $this->modeloMini = new Miniproyecto();
        $this->db = Database::getInstance();
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (empty($_POST['miniproyecto_id']) && empty($_POST['proyecto_id'])) {
                die("Error: El post debe pertenecer a algo.");
            }

            $datos = [
                'creador_id' => $_SESSION['usuario_id'],
                'titulo' => $_POST['titulo'],
                'categoria_id' => $_POST['categoria_id'],
                'descripcion' => $_POST['descripcion'], // Agregamos descripción aquí
                'miniproyecto_id' => !empty($_POST['miniproyecto_id']) ? $_POST['miniproyecto_id'] : null,
                'proyecto_id' => !empty($_POST['proyecto_id']) ? $_POST['proyecto_id'] : null
            ];

            $exito = $this->modeloPost->crear($datos);

            if ($exito) {
                if ($datos['miniproyecto_id']) {
                    header('Location: ver_miniproyecto.php?id=' . $datos['miniproyecto_id']);
                } else {
                    header('Location: ver_proyecto.php?id=' . $datos['proyecto_id']);
                }
                exit();
            } else {
                header('Location: crear_post.php?error=db_error');
                exit();
            }
        }
    }

    public function crearRapido() {
        if (empty($_POST['titulo']) || empty($_POST['categoria_id'])) {
            header('Location: crear_post_rapido.php?error=campos_vacios');
            exit();
        }

        try {

            $this->db->beginTransaction();

            $datosMini = [
                'creador_id' => $_SESSION['usuario_id'],
                'proyecto_id' => null,
                'titulo' => $_POST['titulo'],
                'descripcion' => ''
            ];

            $miniproyecto_id = $this->modeloMini->crear($datosMini);

                if (!$miniproyecto_id) {
                    throw new Exception("Error al generar el contenedor del post.");
                }
            
            $datosPost = [
                    'creador_id' => $_SESSION['usuario_id'],
                    'titulo' => $_POST['titulo'],
                    'categoria_id' => $_POST['categoria_id'],
                    'miniproyecto_id' => $miniproyecto_id,
                    'proyecto_id' => null // NULL obligatorio por el CONSTRAINT de tu BD
                ];
            
            $exitoPost = $this->modeloPost->crear($datosPost);

                if (!$exitoPost) {
                    throw new Exception("Error al guardar el post.");
                }

            $this->db->commit();
                
                header('Location: dashboard_artista.php?mensaje=post_creado');
                exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en creación rápida de post: " . $e->getMessage());
            header('Location: crear_post_rapido.php?error=db_error');
            exit();
        }
    }

    public function alternarDestacado() {
        if (!isset($_GET['id'])) {
            header('Location: dashboard_artista.php');
            exit();
        }

        $post_id = $_GET['id'];
        $resultado = $this->modeloPost->toggleDestacado($post_id, $_SESSION['usuario_id']);

        if ($resultado === 'limite_alcanzado') {
            header('Location: dashboard_artista.php?error=limite_destacados');
        } else {
            $volverA = $_SERVER['HTTP_REFERER'] ?? 'dashboard_artista.php';
            header('Location: ' . $volverA);
        }
        exit();
    }
}