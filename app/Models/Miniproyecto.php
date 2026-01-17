<?php
class Miniproyecto {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear($datos) {
        try {
            $sql = "INSERT INTO miniproyectos (creador_id, proyecto_id, titulo, descripcion) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                $datos['creador_id'],
                $datos['proyecto_id'] ?? null,
                $datos['titulo'],
                $datos['descripcion'] ?? ''
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear miniproyecto: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorUsuario($usuario_id) {
        try {
            $sql = "SELECT mp.*, 
                    (SELECT COUNT(*) FROM posts p WHERE p.miniproyecto_id = mp.id) as cantidad_posts,
                    (SELECT url_archivo FROM imagenes_iteracion ii
                    JOIN iteraciones i ON ii.iteracion_id = i.id 
                    JOIN posts p ON i.post_id = p.id
                    WHERE p.miniproyecto_id = mp.id AND ii.es_principal = 1
                    ORDER BY p.fecha_creacion DESC, i.numero_version DESC LIMIT 1) as miniatura,
                    (SELECT c.nombre_categoria FROM posts p 
                    JOIN categorias c ON p.categoria_id = c.id
                    WHERE p.miniproyecto_id = mp.id 
                    ORDER BY p.fecha_creacion ASC LIMIT 1) as categoria_heredada
                    FROM miniproyectos mp 
                    WHERE mp.creador_id = ? 
                    HAVING cantidad_posts > 0
                    ORDER BY mp.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener miniproyectos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId($id, $usuario_id) {
        try {
            $sql = "SELECT * FROM miniproyectos WHERE id = ? AND creador_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id, $usuario_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerPrimerPostId($miniproyecto_id) {
        try {
            $sql = "SELECT id FROM posts WHERE miniproyecto_id = ? ORDER BY fecha_creacion ASC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$miniproyecto_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['id'] : null;
        } catch (PDOException $e) {
            error_log("Error al obtener primer post: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerPorProyectoPadre($proyecto_id) {
    try {
        $sql = "SELECT mp.*, 
                (SELECT COUNT(*) FROM posts p WHERE p.miniproyecto_id = mp.id) as cantidad_posts 
                FROM miniproyectos mp 
                WHERE mp.proyecto_id = ? 
                ORDER BY mp.fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$proyecto_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
}