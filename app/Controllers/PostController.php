<?php
require_once '../app/Models/Post.php';
require_once '../app/Models/Miniproyecto.php';
require_once '../app/Helpers/CategoryTagHelper.php';

class PostController {
    private $modeloPost;
    private $modeloMini;
    private $db;
    
    const ETIQUETA_POST_INDIVIDUAL = '#@#_no_mini_proyecto_#@#';

    public function __construct() {
        $this->modeloPost = new Post();
        $this->modeloMini = new Miniproyecto();
        $this->db = Database::getInstance();
    }

    private function marcarComoPostIndividual($post_id) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM etiquetas WHERE nombre_etiqueta = ?");
            $stmt->execute([self::ETIQUETA_POST_INDIVIDUAL]);
            $etiqueta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$etiqueta) {
                $stmt = $this->db->prepare("INSERT INTO etiquetas (nombre_etiqueta) VALUES (?)");
                $stmt->execute([self::ETIQUETA_POST_INDIVIDUAL]);
                $etiqueta_id = $this->db->lastInsertId();
            } else {
                $etiqueta_id = $etiqueta['id'];
            }
            
            $stmt = $this->db->prepare("INSERT IGNORE INTO post_etiquetas (post_id, etiqueta_id) VALUES (?, ?)");
            $stmt->execute([$post_id, $etiqueta_id]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error al marcar post como individual: " . $e->getMessage());
            return false;
        }
    }

    private function convertirAMiniproyecto($miniproyecto_id) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM etiquetas WHERE nombre_etiqueta = ?");
            $stmt->execute([self::ETIQUETA_POST_INDIVIDUAL]);
            $etiqueta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($etiqueta) {
                $stmt = $this->db->prepare("
                    DELETE pe FROM post_etiquetas pe
                    INNER JOIN posts p ON pe.post_id = p.id
                    WHERE p.miniproyecto_id = ? AND pe.etiqueta_id = ?
                ");
                $stmt->execute([$miniproyecto_id, $etiqueta['id']]);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error al convertir a miniproyecto: " . $e->getMessage());
            return false;
        }
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Build redirect URL with original parameters
            $redirectParams = [];
            if (!empty($_POST['miniproyecto_id'])) {
                $redirectParams[] = 'miniproyecto_id=' . $_POST['miniproyecto_id'];
            }
            if (!empty($_POST['proyecto_id'])) {
                $redirectParams[] = 'proyecto_id=' . $_POST['proyecto_id'];
            }
            $redirectBase = 'crear_post.php' . (!empty($redirectParams) ? '?' . implode('&', $redirectParams) : '');
            
            // Check for either categorias array or categoria_id
            $categorias = $_POST['categorias'] ?? [];
            if (empty($categorias) && empty($_POST['categoria_id'])) {
                header('Location: ' . $redirectBase . (strpos($redirectBase, '?') !== false ? '&' : '?') . 'error=campos_vacios');
                exit();
            }
            
            if (empty($_POST['titulo'])) {
                header('Location: ' . $redirectBase . (strpos($redirectBase, '?') !== false ? '&' : '?') . 'error=campos_vacios');
                exit();
            }

            // Get main category for backward compatibility
            $categoria_principal = !empty($categorias) ? $categorias[0] : $_POST['categoria_id'];

            $miniproyecto_id = !empty($_POST['miniproyecto_id']) ? $_POST['miniproyecto_id'] : null;
            $proyecto_id = !empty($_POST['proyecto_id']) ? $_POST['proyecto_id'] : null;

            if (!$miniproyecto_id && $proyecto_id) {
                try {
                    $datosMini = [
                        'creador_id' => $_SESSION['usuario_id'],
                        'proyecto_id' => $proyecto_id,
                        'titulo' => $_POST['titulo'],
                        'descripcion' => $_POST['descripcion'] ?? ''
                    ];
                    
                    $miniproyecto_id = $this->modeloMini->crear($datosMini);
                    
                    if (!$miniproyecto_id) {
                        throw new Exception("Error al crear miniproyecto contenedor");
                    }
                } catch (Exception $e) {
                    error_log("Error creando miniproyecto automático: " . $e->getMessage());
                    header('Location: crear_post.php?error=db_error');
                    exit();
                }
            }

            $datos = [
                'creador_id' => $_SESSION['usuario_id'],
                'titulo' => $_POST['titulo'],
                'categoria_id' => $categoria_principal,
                'descripcion' => $_POST['descripcion'] ?? '',
                'miniproyecto_id' => $miniproyecto_id,
                'proyecto_id' => null 
            ];

            if (!empty($_POST['descripcion']) && $miniproyecto_id) {
                try {
                    $sqlUpdate = "UPDATE miniproyectos SET descripcion = ? WHERE id = ? AND creador_id = ?";
                    $stmtUpdate = $this->db->prepare($sqlUpdate);
                    $stmtUpdate->execute([
                        $_POST['descripcion'], 
                        $miniproyecto_id, 
                        $_SESSION['usuario_id']
                    ]);
                } catch (PDOException $e) {
                    error_log("Aviso: No se pudo sincronizar descripción del mini proyecto: " . $e->getMessage());
                }
            }

            if ($miniproyecto_id && (!empty($_POST['titulo_miniproyecto']) || !empty($_POST['descripcion_miniproyecto']))) {
                try {
                    $sqlUpd = "UPDATE miniproyectos SET titulo = ?, descripcion = ? WHERE id = ? AND creador_id = ?";
                    $stmtUpd = $this->db->prepare($sqlUpd);
                    
                    $nuevoTitulo = $_POST['titulo_miniproyecto']; 
                    $nuevaDesc = $_POST['descripcion_miniproyecto'];

                    $stmtUpd->execute([$nuevoTitulo, $nuevaDesc, $miniproyecto_id, $_SESSION['usuario_id']]);
                } catch (Exception $e) {
                    error_log("Error actualizando mini proyecto: " . $e->getMessage());
                }
            }

            $post_id = $this->modeloPost->crear($datos);

            if ($post_id) {
                // Save multiple categories
                if (!empty($categorias)) {
                    CategoryTagHelper::savePostCategories($post_id, $categorias);
                }
                
                // Save tags from JSON field
                if (!empty($_POST['etiquetas'])) {
                    $tags = json_decode($_POST['etiquetas'], true);
                    if (is_array($tags)) {
                        CategoryTagHelper::savePostTags($post_id, $tags);
                    }
                }
                
                if ($miniproyecto_id) {
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM posts WHERE miniproyecto_id = ?");
                    $stmt->execute([$miniproyecto_id]);
                    $cantidad = $stmt->fetchColumn();
                    
                    if ($cantidad >= 2) {
                        $this->convertirAMiniproyecto($miniproyecto_id);
                    }
                }
                
                if ($datos['miniproyecto_id']) {
                    $stmt = $this->db->prepare("SELECT proyecto_id FROM miniproyectos WHERE id = ?");
                    $stmt->execute([$datos['miniproyecto_id']]);
                    $miniData = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($miniData && $miniData['proyecto_id']) {
                        header('Location: ver_proyecto.php?id=' . $miniData['proyecto_id'] . '&mensaje=post_creado');
                    } else {
                        header('Location: ver_miniproyecto.php?id=' . $datos['miniproyecto_id']);
                    }
                } else {
                    header('Location: dashboard_artista.php?mensaje=post_creado');
                }
                exit();
            } else {
                header('Location: crear_post.php?error=db_error');
                exit();
            }
        }
    }

    public function crearRapido() {
        // Check for either categorias array or categoria_id
        $categorias = $_POST['categorias'] ?? [];
        if (empty($categorias) && empty($_POST['categoria_id'])) {
            header('Location: crear_post_rapido.php?error=campos_vacios');
            exit();
        }
        
        if (empty($_POST['titulo'])) {
            header('Location: crear_post_rapido.php?error=campos_vacios');
            exit();
        }

        // Get main category for backward compatibility
        $categoria_principal = !empty($categorias) ? $categorias[0] : $_POST['categoria_id'];

        try {
            $this->db->beginTransaction();

            $proyecto_id_padre = !empty($_POST['proyecto_id']) ? $_POST['proyecto_id'] : null;

            $datosMini = [
                'creador_id' => $_SESSION['usuario_id'],
                'proyecto_id' => $proyecto_id_padre,
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'] ?? ''
            ];
            
            $miniproyecto_id = $this->modeloMini->crear($datosMini);

            if (!$miniproyecto_id) {
                throw new Exception("Error crítico al crear el mini proyecto contenedor.");
            }
            
            $datosPost = [
                'creador_id' => $_SESSION['usuario_id'],
                'titulo' => $_POST['titulo'],
                'categoria_id' => $categoria_principal,
                'miniproyecto_id' => $miniproyecto_id,
                'proyecto_id' => null
            ];

            $post_id = $this->modeloPost->crear($datosPost);

            if (!$post_id) {
                throw new Exception("Error al guardar el post en la base de datos.");
            }

            // Save multiple categories
            if (!empty($categorias)) {
                CategoryTagHelper::savePostCategories($post_id, $categorias);
            }
            
            // Save tags from JSON field
            if (!empty($_POST['etiquetas'])) {
                $tags = json_decode($_POST['etiquetas'], true);
                if (is_array($tags)) {
                    CategoryTagHelper::savePostTags($post_id, $tags);
                }
            }

            $this->marcarComoPostIndividual($post_id);

            $this->db->commit();

            if ($proyecto_id_padre) {
                header('Location: ver_proyecto.php?id=' . $proyecto_id_padre . '&mensaje=post_creado');
            } else {
                header('Location: dashboard_artista.php?mensaje=post_creado');
            }
            exit();

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error en creación rápida: " . $e->getMessage());
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
            $volverA = $_SERVER['HTTP_REFERER'] ?? 'dashboard_artista.php';
            if (strpos($volverA, '?') !== false) {
                header('Location: ' . $volverA . '&error=limite_destacados');
            } else {
                header('Location: ' . $volverA . '?error=limite_destacados');
            }
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

            // Delete images from Cloudinary
            $stmtImgs = $this->db->prepare("
                SELECT ii.cloud_id 
                FROM imagenes_iteracion ii
                INNER JOIN iteraciones i ON ii.iteracion_id = i.id
                WHERE i.post_id = ? AND ii.cloud_id IS NOT NULL
            ");
            $stmtImgs->execute([$post_id]);
            $imagenesCloud = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);

            require_once '../app/Config/Cloudinary.php';
            $cloudinary = CloudinaryConfig::getInstance();
            foreach ($imagenesCloud as $cloudId) {
                $cloudinary->deleteImage($cloudId);
            }

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
            
            if ($post['proyecto_id']) {
                 header('Location: ver_proyecto.php?id=' . $post['proyecto_id'] . '&mensaje=post_eliminado');
            } elseif ($miniproyecto_id) {
                 header('Location: dashboard_artista.php?mensaje=post_eliminado');
            } else {
                 header('Location: dashboard_artista.php?mensaje=post_eliminado');
            }
            exit();

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al eliminar post: " . $e->getMessage());
            header('Location: dashboard_artista.php?error=error_eliminar');
            exit();
        }
    }
}