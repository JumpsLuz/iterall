<?php
class Post {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear($datos) {
        try {
            $sql = "INSERT INTO posts (creador_id, titulo, categoria_id, miniproyecto_id, proyecto_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                $datos['creador_id'],
                $datos['titulo'],
                $datos['categoria_id'],
                $datos['miniproyecto_id'] ?? null,
                $datos['proyecto_id'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear post: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorId($id, $usuario_id) {
        $sql = "SELECT p.*, c.nombre_categoria 
                FROM posts p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = ? AND p.creador_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerIteraciones($post_id) {
        $sql = "SELECT * FROM iteraciones WHERE post_id = ? ORDER BY numero_version DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDestacados($usuario_id) {
        try {
            $sql = "SELECT p.*, mp.titulo AS nombre_miniproyecto FROM posts p 
                    JOIN post_etiquetas pe ON p.id = pe.post_id
                    JOIN etiquetas e ON pe.etiqueta_id = e.id
                    LEFT JOIN miniproyectos mp ON p.miniproyecto_id = mp.id
                    WHERE p.creador_id = ? AND e.nombre_etiqueta = 'Destacado'
                    ORDER BY p.fecha_creacion DESC LIMIT 5";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function contarDestacados($usuario_id) {
        $sql = "SELECT COUNT(*) FROM posts p
                JOIN post_etiquetas pe ON p.id = pe.post_id
                JOIN etiquetas e ON pe.etiqueta_id = e.id
                WHERE p.creador_id = ? AND e.nombre_etiqueta = 'Destacado'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchColumn();
    }

    public function toggleDestacado($post_id, $usuario_id) {
        try {
            $stmtEtiqueta = $this->db->prepare("SELECT id FROM etiquetas WHERE nombre_etiqueta = 'Destacado'");
            $stmtEtiqueta->execute();
            $etiqueta = $stmtEtiqueta->fetch(PDO::FETCH_ASSOC);

            if (!$etiqueta) {
                $this->db->exec("INSERT INTO etiquetas (nombre_etiqueta) VALUES ('Destacado')");
                $etiqueta_id = $this->db->lastInsertId();
            } else {
                $etiqueta_id = $etiqueta['id'];
            }

            $check = $this->db->prepare("SELECT * FROM post_etiquetas WHERE post_id = ? AND etiqueta_id = ?");
            $check->execute([$post_id, $etiqueta_id]);

            if ($check->fetch()) {
                $del = $this->db->prepare("DELETE FROM post_etiquetas WHERE post_id = ? AND etiqueta_id = ?");
                return $del->execute([$post_id, $etiqueta_id]);
            } else {
                if ($this->contarDestacados($usuario_id) >= 5) {
                    return 'limite_alcanzado';
                }
                $ins = $this->db->prepare("INSERT INTO post_etiquetas (post_id, etiqueta_id) VALUES (?, ?)");
                return $ins->execute([$post_id, $etiqueta_id]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public function esDestacado($post_id) {
        $sql = "SELECT COUNT(*) FROM post_etiquetas pe
                JOIN etiquetas e ON pe.etiqueta_id = e.id
                WHERE pe.post_id = ? AND e.nombre_etiqueta = 'Destacado'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$post_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function obtenerPorMiniproyecto($miniproyecto_id) {
        try {
            $sql = "SELECT * FROM posts 
                    WHERE miniproyecto_id = ? 
                    ORDER BY fecha_creacion ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$miniproyecto_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}