<?php
require_once '../app/Models/Proyecto.php';

class ProyectoController {
    private $modeloProyecto;
    private $db;

    public function __construct() {
        $this->modeloProyecto = new Proyecto();
        $this->db = Database::getInstance();
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (empty($_POST['titulo']) || empty($_POST['categoria_id']) || empty($_POST['estado_id'])) {
                header('Location: crear_proyecto.php?error=campos_requeridos');
                exit();
            }

            try {
                $datos = [
                    'creador_id' => $_SESSION['usuario_id'],
                    'categoria_id' => $_POST['categoria_id'],
                    'estado_id' => $_POST['estado_id'],
                    'titulo' => $_POST['titulo'],
                    'descripcion' => $_POST['descripcion'] ?? '',
                    'es_publico' => isset($_POST['es_publico']) ? 1 : 0
                ];

                $avatarFile = null;
                $bannerFile = null;

                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $avatarFile = $_FILES['avatar'];
                }

                if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                    $bannerFile = $_FILES['banner'];
                }

                $proyectoId = $this->modeloProyecto->crear($datos, $avatarFile, $bannerFile);

                if ($proyectoId) {
                    header('Location: mis_proyectos.php?mensaje=proyecto_creado');
                    exit();
                } else {
                    header('Location: crear_proyecto.php?error=no_se_pudo_crear');
                    exit();
                }

            } catch (Exception $e) {
                error_log("Error al crear proyecto: " . $e->getMessage());
                header('Location: crear_proyecto.php?error=error_inesperado');
                exit();
            }
        }
    }

    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto_id = $_POST['proyecto_id'];
            
            $datos = [
                'categoria_id' => $_POST['categoria_id'],
                'estado_id' => $_POST['estado_id'],
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'] ?? '',
                'es_publico' => isset($_POST['es_publico']) ? 1 : 0
            ];

            $avatarFile = null;
            $bannerFile = null;

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatarFile = $_FILES['avatar'];
            }

            if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                $bannerFile = $_FILES['banner'];
            }

            $exito = $this->modeloProyecto->actualizar($proyecto_id, $datos, $_SESSION['usuario_id'], $avatarFile, $bannerFile);

            if ($exito) {
                header('Location: ver_proyecto.php?id=' . $proyecto_id . '&mensaje=actualizado');
                exit();
            } else {
                header('Location: editar_proyecto.php?id=' . $proyecto_id . '&error=1');
                exit();
            }
        }
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (!isset($_POST['proyecto_id'])) {
                header('Location: mis_proyectos.php?error=proyecto_no_especificado');
                exit();
            }
            
            $proyecto_id = $_POST['proyecto_id'];
            $usuario_id = $_SESSION['usuario_id'];
            
            try {
                $this->db->beginTransaction();
                
                $stmt = $this->db->prepare("SELECT id FROM proyectos WHERE id = ? AND creador_id = ?");
                $stmt->execute([$proyecto_id, $usuario_id]);
                
                if (!$stmt->fetch()) {
                    throw new Exception("No tienes permiso para eliminar este proyecto.");
                }
                
                $stmtImgs = $this->db->prepare("
                    SELECT ii.cloud_id 
                    FROM imagenes_iteracion ii
                    INNER JOIN iteraciones i ON ii.iteracion_id = i.id
                    INNER JOIN posts p ON i.post_id = p.id
                    INNER JOIN miniproyectos mp ON p.miniproyecto_id = mp.id
                    WHERE mp.proyecto_id = ? AND ii.cloud_id IS NOT NULL
                ");
                $stmtImgs->execute([$proyecto_id]);
                $imagenesCloud = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);

                require_once '../app/Config/Cloudinary.php';
                $cloudinary = CloudinaryConfig::getInstance();
                foreach ($imagenesCloud as $cloudId) {
                    $cloudinary->deleteImage($cloudId);
                }
                
                $stmtProyecto = $this->db->prepare("DELETE FROM proyectos WHERE id = ? AND creador_id = ?");
                $stmtProyecto->execute([$proyecto_id, $usuario_id]);
                
                $this->db->commit();
                
                header('Location: mis_proyectos.php?mensaje=proyecto_eliminado');
                exit();
                
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Error al eliminar proyecto: " . $e->getMessage());
                header('Location: mis_proyectos.php?error=no_se_pudo_eliminar&detalle=' . urlencode($e->getMessage()));
                exit();
            }
        }
    }
}