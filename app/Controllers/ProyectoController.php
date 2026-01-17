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

            $datos = [
                'creador_id' => $_SESSION['usuario_id'],
                'categoria_id' => $_POST['categoria_id'],
                'estado_id' => $_POST['estado_id'],
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'] ?? '',
                'es_publico' => isset($_POST['es_publico']) ? 1 : 0
            ];

            $exito = $this->modeloProyecto->crear($datos);

            if ($exito) {
                header('Location: mis_proyectos.php?mensaje=proyecto_creado');
                exit();
            } else {
                header('Location: crear_proyecto.php?error=no_se_pudo_crear');
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

            $exito = $this->modeloProyecto->actualizar($proyecto_id, $datos, $_SESSION['usuario_id']);

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
                    DELETE ii FROM imagenes_iteracion ii
                    INNER JOIN iteraciones i ON ii.iteracion_id = i.id
                    INNER JOIN posts p ON i.post_id = p.id
                    INNER JOIN miniproyectos mp ON p.miniproyecto_id = mp.id
                    WHERE mp.proyecto_id = ?
                ");
                $stmtImgs->execute([$proyecto_id]);
                
                $stmtIter = $this->db->prepare("
                    DELETE i FROM iteraciones i
                    INNER JOIN posts p ON i.post_id = p.id
                    INNER JOIN miniproyectos mp ON p.miniproyecto_id = mp.id
                    WHERE mp.proyecto_id = ?
                ");
                $stmtIter->execute([$proyecto_id]);
                
                $stmtEtiq = $this->db->prepare("
                    DELETE pe FROM post_etiquetas pe
                    INNER JOIN posts p ON pe.post_id = p.id
                    INNER JOIN miniproyectos mp ON p.miniproyecto_id = mp.id
                    WHERE mp.proyecto_id = ?
                ");
                $stmtEtiq->execute([$proyecto_id]);
                
                $stmtPosts = $this->db->prepare("
                    DELETE p FROM posts p
                    INNER JOIN miniproyectos mp ON p.miniproyecto_id = mp.id
                    WHERE mp.proyecto_id = ?
                ");
                $stmtPosts->execute([$proyecto_id]);
                
                $stmtMini = $this->db->prepare("DELETE FROM miniproyectos WHERE proyecto_id = ?");
                $stmtMini->execute([$proyecto_id]);
                
                $stmtColab = $this->db->prepare("DELETE FROM colaboradores WHERE proyecto_id = ?");
                $stmtColab->execute([$proyecto_id]);
                
                $stmtProyEtiq = $this->db->prepare("DELETE FROM proyecto_etiquetas WHERE proyecto_id = ?");
                $stmtProyEtiq->execute([$proyecto_id]);
                
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
?>