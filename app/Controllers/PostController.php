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
                header('Location: dashboard_artista.php?error=padre_necesario');
                exit();
            }

            $datos = [
                'creador_id' => $_SESSION['usuario_id'],
                'titulo' => $_POST['titulo'],
                'categoria_id' => $_POST['categoria_id'],
                'miniproyecto_id' => !empty($_POST['miniproyecto_id']) ? $_POST['miniproyecto_id'] : null,
                'proyecto_id' => !empty($_POST['proyecto_id']) ? $_POST['proyecto_id'] : null
            ];

            if (!empty($_POST['descripcion']) && !empty($_POST['miniproyecto_id'])) {
                try {
                    $sqlUpdate = "UPDATE miniproyectos SET descripcion = ? WHERE id = ? AND creador_id = ?";
                    $stmtUpdate = $this->db->prepare($sqlUpdate);
                    $stmtUpdate->execute([
                        $_POST['descripcion'],
                        $_POST['miniproyecto_id'],
                        $_SESSION['usuario_id']
                    ]);
                } catch (PDOException $e) {
                    error_log("Error al actualizar descripción de miniproyecto: " . $e->getMessage());
                }
            }

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

    public function eliminar() {
        if (!isset($_POST['post_id'])) {
            header('Location: dashboard_artista.php?error=post_no_especificado');
            exit();
        }

        try {
            $post_id = $_POST['post_id'];
            $usuario_id = $_SESSION['usuario_id'];

            $post = $this->modeloPost->obtenerPorId($post_id, $usuario_id);
            
            if (!$post) {
                header('Location: dashboard_artista.php?error=post_no_encontrado');
                exit();
            }

            $miniproyecto_id = $post['miniproyecto_id'];

            $this->db->beginTransaction();

            $sqlDelete = "DELETE FROM posts WHERE id = ? AND creador_id = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([$post_id, $usuario_id]);

            if ($miniproyecto_id) {
                $sqlCount = "SELECT COUNT(*) FROM posts WHERE miniproyecto_id = ?";
                $stmtCount = $this->db->prepare($sqlCount);
                $stmtCount->execute([$miniproyecto_id]);
                $cantidad = $stmtCount->fetchColumn();

                if ($cantidad == 0) {
                    $sqlDeleteMini = "DELETE FROM miniproyectos WHERE id = ? AND creador_id = ?";
                    $stmtDeleteMini = $this->db->prepare($sqlDeleteMini);
                    $stmtDeleteMini->execute([$miniproyecto_id, $usuario_id]);
                }
            }

            $this->db->commit();

            header('Location: dashboard_artista.php?mensaje=post_eliminado');
            exit();

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al eliminar post: " . $e->getMessage());
            header('Location: dashboard_artista.php?error=error_eliminar');
            exit();
        }
    }
}