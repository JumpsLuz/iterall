<?php
class Miniproyecto {
    private $db;
    
    const ETIQUETA_POST_INDIVIDUAL = '#@#_no_mini_proyecto_#@#';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    
    public function esPostIndividual($miniproyecto_id) {
        try {
            $sql = "SELECT COUNT(*) FROM post_etiquetas pe
                    INNER JOIN posts p ON pe.post_id = p.id
                    INNER JOIN etiquetas e ON pe.etiqueta_id = e.id
                    WHERE p.miniproyecto_id = ? AND e.nombre_etiqueta = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$miniproyecto_id, self::ETIQUETA_POST_INDIVIDUAL]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar si es post individual: " . $e->getMessage());
            return false;
        }
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
                     WHERE p.miniproyecto_id = mp.id
                     ORDER BY p.fecha_creacion DESC, i.numero_version DESC, ii.orden_visual ASC LIMIT 1) as miniatura,
                    (SELECT c.nombre_categoria FROM posts p 
                     JOIN categorias c ON p.categoria_id = c.id
                     WHERE p.miniproyecto_id = mp.id 
                     ORDER BY p.fecha_creacion ASC LIMIT 1) as categoria_heredada,
                    (SELECT titulo FROM posts p WHERE p.miniproyecto_id = mp.id ORDER BY p.fecha_creacion ASC LIMIT 1) as titulo_primer_post,
                    (SELECT id FROM posts p WHERE p.miniproyecto_id = mp.id ORDER BY p.fecha_creacion ASC LIMIT 1) as id_primer_post,
                    (SELECT COUNT(*) FROM post_etiquetas pe
                     INNER JOIN posts p ON pe.post_id = p.id
                     INNER JOIN etiquetas e ON pe.etiqueta_id = e.id
                     WHERE p.miniproyecto_id = mp.id AND e.nombre_etiqueta = ?) as es_post_individual
                    FROM miniproyectos mp 
                    WHERE mp.creador_id = ? AND mp.proyecto_id IS NULL
                    ORDER BY mp.fecha_creacion DESC";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([self::ETIQUETA_POST_INDIVIDUAL, $usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener miniproyectos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId($id, $usuario_id) {
        try {
            $sql = "SELECT mp.*, 
                    (SELECT url_archivo FROM imagenes_iteracion ii
                     JOIN iteraciones i ON ii.iteracion_id = i.id 
                     JOIN posts p ON i.post_id = p.id
                     WHERE p.miniproyecto_id = mp.id
                     ORDER BY p.fecha_creacion DESC, i.numero_version DESC, ii.orden_visual ASC LIMIT 1) as miniatura
                    FROM miniproyectos mp WHERE mp.id = ? AND mp.creador_id = ?";
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
                (SELECT COUNT(*) FROM posts p WHERE p.miniproyecto_id = mp.id) as cantidad_posts,
                (SELECT url_archivo FROM imagenes_iteracion ii
                 JOIN iteraciones i ON ii.iteracion_id = i.id 
                 JOIN posts p ON i.post_id = p.id
                 WHERE p.miniproyecto_id = mp.id
                 ORDER BY p.fecha_creacion DESC, i.numero_version DESC, ii.orden_visual ASC LIMIT 1) as miniatura,
                (SELECT COUNT(*) FROM post_etiquetas pe
                INNER JOIN posts p ON pe.post_id = p.id
                INNER JOIN etiquetas e ON pe.etiqueta_id = e.id
                WHERE p.miniproyecto_id = mp.id AND e.nombre_etiqueta = ?) as es_post_individual
                FROM miniproyectos mp 
                WHERE mp.proyecto_id = ? 
                ORDER BY mp.fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([self::ETIQUETA_POST_INDIVIDUAL, $proyecto_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener miniproyectos por proyecto: " . $e->getMessage());
        return [];
    }}

    /**
     * Obtener miniproyecto por ID sin verificar propietario (para vista pública)
     */
    public function obtenerPublicoPorId($id) {
        try {
            $sql = "SELECT mp.*, 
                    p.nombre_artistico as creador_nombre,
                    p.avatar_url as creador_foto,
                    (SELECT url_archivo FROM imagenes_iteracion ii
                     JOIN iteraciones i ON ii.iteracion_id = i.id 
                     JOIN posts po ON i.post_id = po.id
                     WHERE po.miniproyecto_id = mp.id
                     ORDER BY po.fecha_creacion DESC, i.numero_version DESC, ii.orden_visual ASC LIMIT 1) as miniatura
                    FROM miniproyectos mp 
                    JOIN usuarios u ON mp.creador_id = u.id
                    LEFT JOIN perfiles p ON u.id = p.usuario_id
                    WHERE mp.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener miniproyecto público: " . $e->getMessage());
            return false;
        }
    }
}