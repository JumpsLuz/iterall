<?php
require_once '../app/Models/Iteracion.php';
require_once '../app/Models/Post.php';

class IteracionController {
    private $modeloIteracion;
    private $modeloPost;

    public function __construct() {
        $this->modeloIteracion = new Iteracion();
        $this->modeloPost = new Post();
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: dashboard_artista.php');
            exit();
        }

        if (empty($_POST['post_id'])) {
            header('Location: dashboard_artista.php?error=post_no_especificado');
            exit();
        }

        try {
            $postId = $_POST['post_id'];
            $usuarioId = $_SESSION['usuario_id'];

            $post = $this->modeloPost->obtenerPorId($postId, $usuarioId);
            if (!$post) {
                header('Location: dashboard_artista.php?error=post_no_encontrado');
                exit();
            }

            if (empty($_FILES['imagenes']['name'][0])) {
                header('Location: ver_post.php?id=' . $postId . '&error=sin_imagenes');
                exit();
            }

            $datos = [
                'post_id' => $postId,
                'notas_cambio' => $_POST['notas_cambio'] ?? '',
                'tiempo_dedicado_min' => !empty($_POST['tiempo_dedicado_min']) ? (int)$_POST['tiempo_dedicado_min'] : null
            ];

            // IMPORTANTE: Solo la primera imagen (índice 0) será la principal
            $imagenPrincipalIndex = 0;

            $imagenes = $this->procesarImagenesMultiples($_FILES['imagenes'], $imagenPrincipalIndex);

            $iteracionId = $this->modeloIteracion->crear($datos, $imagenes);

            if ($iteracionId) {
                header('Location: ver_post.php?id=' . $postId . '&mensaje=iteracion_creada');
            } else {
                header('Location: ver_post.php?id=' . $postId . '&error=error_crear_iteracion');
            }
            exit();

        } catch (Exception $e) {
            error_log("Error en creación de iteración: " . $e->getMessage());
            $postId = $_POST['post_id'] ?? null;
            $redirect = $postId ? "ver_post.php?id=$postId" : "dashboard_artista.php";
            header('Location: ' . $redirect . '&error=error_inesperado');
            exit();
        }
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: dashboard_artista.php');
            exit();
        }

        if (empty($_POST['iteracion_id'])) {
            header('Location: dashboard_artista.php?error=iteracion_no_especificada');
            exit();
        }

        try {
            $iteracionId = $_POST['iteracion_id'];
            $usuarioId = $_SESSION['usuario_id'];

            $iteracion = $this->modeloIteracion->obtenerPorId($iteracionId, $usuarioId);
            
            if (!$iteracion) {
                header('Location: dashboard_artista.php?error=iteracion_no_encontrada');
                exit();
            }

            $postId = $iteracion['post_id'];

            $exito = $this->modeloIteracion->eliminar($iteracionId, $usuarioId);

            if ($exito) {
                header('Location: ver_post.php?id=' . $postId . '&mensaje=iteracion_eliminada');
            } else {
                header('Location: ver_post.php?id=' . $postId . '&error=error_eliminar');
            }
            exit();

        } catch (Exception $e) {
            error_log("Error al eliminar iteración: " . $e->getMessage());
            header('Location: dashboard_artista.php?error=error_inesperado');
            exit();
        }
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: dashboard_artista.php');
            exit();
        }

        if (empty($_POST['iteracion_id'])) {
            header('Location: dashboard_artista.php?error=iteracion_no_especificada');
            exit();
        }

        try {
            $iteracionId = $_POST['iteracion_id'];
            $usuarioId = $_SESSION['usuario_id'];

            $datos = [
                'notas_cambio' => $_POST['notas_cambio'] ?? '',
                'tiempo_dedicado_min' => !empty($_POST['tiempo_dedicado_min']) ? (int)$_POST['tiempo_dedicado_min'] : null
            ];

            $exito = $this->modeloIteracion->actualizar($iteracionId, $datos, $usuarioId);

            $iteracion = $this->modeloIteracion->obtenerPorId($iteracionId, $usuarioId);
            $postId = $iteracion['post_id'] ?? null;

            if ($exito && $postId) {
                header('Location: ver_post.php?id=' . $postId . '&mensaje=iteracion_actualizada');
            } else {
                $redirect = $postId ? "ver_post.php?id=$postId" : "dashboard_artista.php";
                header('Location: ' . $redirect . '&error=error_actualizar');
            }
            exit();

        } catch (Exception $e) {
            error_log("Error al actualizar iteración: " . $e->getMessage());
            header('Location: dashboard_artista.php?error=error_inesperado');
            exit();
        }
    }

    /**
     * @param array $filesArray 
     * @param int $principalIndex 
     * @return array
     */
    private function procesarImagenesMultiples($filesArray, $principalIndex = 0) {
        $imagenes = [];
        $fileCount = count($filesArray['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($filesArray['error'][$i] === UPLOAD_ERR_OK) {
                $imagenes[] = [
                    'name' => $filesArray['name'][$i],
                    'type' => $filesArray['type'][$i],
                    'tmp_name' => $filesArray['tmp_name'][$i],
                    'error' => $filesArray['error'][$i],
                    'size' => $filesArray['size'][$i],
                    // CORRECCIÓN: Solo marcar como principal la imagen en el índice especificado
                    'es_principal' => ($i === $principalIndex) ? 1 : 0
                ];
            }
        }

        return $imagenes;
    }
}