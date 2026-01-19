<?php
require_once '../app/Models/Proyecto.php';
require_once '../app/Helpers/CategoryTagHelper.php';

class ProyectoController {
    private $modeloProyecto;
    private $db;

    public function __construct() {
        $this->modeloProyecto = new Proyecto();
        $this->db = Database::getInstance();
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (empty($_POST['titulo']) || empty($_POST['categorias']) || empty($_POST['estado_id'])) {
                header('Location: crear_proyecto.php?error=campos_requeridos');
                exit();
            }

            try {
                // Use first category as main category for backwards compatibility
                $categorias = $_POST['categorias'];
                $categoria_principal = $categorias[0];
                
                $datos = [
                    'creador_id' => $_SESSION['usuario_id'],
                    'categoria_id' => $categoria_principal,
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
                    // Save all categories
                    CategoryTagHelper::saveProjectCategories($proyectoId, $categorias);
                    
                    // Save tags
                    if (!empty($_POST['etiquetas'])) {
                        $tags = json_decode($_POST['etiquetas'], true);
                        if ($tags) {
                            CategoryTagHelper::saveProjectTags($proyectoId, $tags);
                        }
                    }
                    
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
            
            // Use first category as main category for backwards compatibility
            $categorias = $_POST['categorias'] ?? [];
            $categoria_principal = !empty($categorias) ? $categorias[0] : $_POST['categoria_id'];
            
            $datos = [
                'categoria_id' => $categoria_principal,
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
                // Save multiple categories
                if (!empty($categorias)) {
                    CategoryTagHelper::saveProjectCategories($proyecto_id, $categorias);
                }
                
                // Save tags from JSON field
                if (!empty($_POST['etiquetas'])) {
                    $tags = json_decode($_POST['etiquetas'], true);
                    if (is_array($tags)) {
                        CategoryTagHelper::saveProjectTags($proyecto_id, $tags);
                    }
                }
                
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
                
                $stmt = $this->db->prepare("SELECT id, avatar_url, banner_url FROM proyectos WHERE id = ? AND creador_id = ?");
                $stmt->execute([$proyecto_id, $usuario_id]);
                $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$proyecto) {
                    throw new Exception("No tienes permiso para eliminar este proyecto.");
                }
                
                require_once '../app/Config/Cloudinary.php';
                $cloudinary = CloudinaryConfig::getInstance();
                if (!empty($proyecto['avatar_url'])) {
                    $this->modeloProyecto->eliminarImagenProyecto($proyecto['avatar_url']);
                }
                if (!empty($proyecto['banner_url'])) {
                    $this->modeloProyecto->eliminarImagenProyecto($proyecto['banner_url']);
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