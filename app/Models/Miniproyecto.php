<?php
class Miniproyecto {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear($datos) {
        try {
            $sql = "INSERT INTO miniproyectos (creador_id, titulo, descripcion, categoria_id, estado_id, es_publico) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                $datos['creador_id'],
                $datos['proyecyo_id'] ?? null,
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
                    JOIN iteraciones i ON ii.iteracion_id = i.id JOIN posts p ON i.post_id = p.id
                    WHERE p.miniproyecto_id = mp.id
                    ORDER BY p.fecha_creacion DESC, i.numero_version DESC LIMIT 1) as miniatura
                    FROM miniproyectos mp WHERE mp.creador_id = ? ORDER BY mp.fecha_creacion DESC";
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
}