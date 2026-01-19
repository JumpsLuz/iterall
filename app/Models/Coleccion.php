<?php
/**
 * Modelo Coleccion
 * Maneja las colecciones privadas de los clientes/reclutadores
 * donde pueden guardar posts de artistas para referencia
 * 
 * Tabla: colecciones (id, usuario_id, titulo, es_privada)
 * Tabla pivot: items_coleccion (id, coleccion_id, post_id, fecha_agregado)
 */
class Coleccion {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crear una nueva colección
     */
    public function crear($usuario_id, $titulo) {
        try {
            $sql = "INSERT INTO colecciones (usuario_id, titulo, es_privada) VALUES (?, ?, TRUE)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$usuario_id, $titulo]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear colección: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las colecciones de un usuario
     */
    public function obtenerPorUsuario($usuario_id) {
        try {
            $sql = "SELECT c.id, c.titulo as nombre, c.es_privada,
                    (SELECT COUNT(*) FROM items_coleccion WHERE coleccion_id = c.id) as total_posts
                    FROM colecciones c
                    WHERE c.usuario_id = ?
                    ORDER BY c.id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener colecciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una colección por ID (verificando propiedad)
     */
    public function obtenerPorId($coleccion_id, $usuario_id) {
        try {
            $sql = "SELECT id, titulo as nombre, es_privada FROM colecciones WHERE id = ? AND usuario_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$coleccion_id, $usuario_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener colección: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener posts de una colección
     */
    public function obtenerPosts($coleccion_id, $usuario_id) {
        try {
            // Verificar propiedad
            $coleccion = $this->obtenerPorId($coleccion_id, $usuario_id);
            if (!$coleccion) return [];

            $sql = "SELECT p.*, 
                    pf.nombre_artistico, pf.avatar_url as artista_avatar,
                    cat.nombre_categoria,
                    ic.fecha_agregado,
                    (SELECT url_archivo FROM imagenes_iteracion ii
                     JOIN iteraciones i ON ii.iteracion_id = i.id
                     WHERE i.post_id = p.id AND ii.es_principal = 1
                     ORDER BY i.numero_version DESC LIMIT 1) as portada
                    FROM items_coleccion ic
                    JOIN posts p ON ic.post_id = p.id
                    JOIN usuarios u ON p.creador_id = u.id
                    JOIN perfiles pf ON pf.usuario_id = u.id
                    LEFT JOIN categorias cat ON p.categoria_id = cat.id
                    WHERE ic.coleccion_id = ?
                    ORDER BY ic.fecha_agregado DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$coleccion_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener posts de colección: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Agregar un post a una colección
     */
    public function agregarPost($coleccion_id, $post_id, $usuario_id) {
        try {
            // Verificar propiedad de la colección
            $coleccion = $this->obtenerPorId($coleccion_id, $usuario_id);
            if (!$coleccion) return false;

            $sql = "INSERT IGNORE INTO items_coleccion (coleccion_id, post_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$coleccion_id, $post_id]);
        } catch (PDOException $e) {
            error_log("Error al agregar post a colección: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Quitar un post de una colección
     */
    public function quitarPost($coleccion_id, $post_id, $usuario_id) {
        try {
            // Verificar propiedad de la colección
            $coleccion = $this->obtenerPorId($coleccion_id, $usuario_id);
            if (!$coleccion) return false;

            $sql = "DELETE FROM items_coleccion WHERE coleccion_id = ? AND post_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$coleccion_id, $post_id]);
        } catch (PDOException $e) {
            error_log("Error al quitar post de colección: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una colección
     */
    public function actualizar($coleccion_id, $usuario_id, $titulo) {
        try {
            $sql = "UPDATE colecciones SET titulo = ? WHERE id = ? AND usuario_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$titulo, $coleccion_id, $usuario_id]);
        } catch (PDOException $e) {
            error_log("Error al actualizar colección: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una colección
     */
    public function eliminar($coleccion_id, $usuario_id) {
        try {
            $sql = "DELETE FROM colecciones WHERE id = ? AND usuario_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$coleccion_id, $usuario_id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar colección: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un post está en alguna colección del usuario
     */
    public function postEstaGuardado($post_id, $usuario_id) {
        try {
            $sql = "SELECT c.id, c.titulo as nombre FROM colecciones c
                    JOIN items_coleccion ic ON c.id = ic.coleccion_id
                    WHERE ic.post_id = ? AND c.usuario_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$post_id, $usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al verificar post guardado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener preview de posts para mostrar en la tarjeta de colección
     */
    public function obtenerPreviewPosts($coleccion_id, $limite = 4) {
        try {
            $sql = "SELECT p.id,
                    COALESCE(
                        (SELECT ii.url_archivo
                         FROM iteraciones i
                         JOIN imagenes_iteracion ii ON ii.iteracion_id = i.id
                         WHERE i.post_id = p.id AND ii.es_principal = 1
                         ORDER BY i.numero_version DESC, ii.orden_visual ASC
                         LIMIT 1),
                        (SELECT ii2.url_archivo
                         FROM iteraciones i2
                         JOIN imagenes_iteracion ii2 ON ii2.iteracion_id = i2.id
                         WHERE i2.post_id = p.id
                         ORDER BY i2.numero_version DESC, ii2.orden_visual ASC
                         LIMIT 1)
                    ) as portada
                    FROM items_coleccion ic
                    JOIN posts p ON ic.post_id = p.id
                    WHERE ic.coleccion_id = ?
                    ORDER BY ic.fecha_agregado DESC
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, (int)$coleccion_id, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener preview de colección: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Alternar (agregar/quitar) un post en una colección
     * Retorna un array con el estado y la acción realizada
     */
    public function togglePost($coleccion_id, $post_id, $usuario_id) {
        try {
            // Verificar propiedad de la colección
            $coleccion = $this->obtenerPorId($coleccion_id, $usuario_id);
            if (!$coleccion) {
                return ['success' => false, 'error' => 'Colección no encontrada'];
            }

            // Verificar si ya está guardado
            $sql = "SELECT 1 FROM items_coleccion WHERE coleccion_id = ? AND post_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$coleccion_id, $post_id]);
            
            if ($stmt->fetch()) {
                // Ya existe, quitar
                $this->quitarPost($coleccion_id, $post_id, $usuario_id);
                return ['success' => true, 'action' => 'removed'];
            } else {
                // No existe, agregar
                $this->agregarPost($coleccion_id, $post_id, $usuario_id);
                return ['success' => true, 'action' => 'added'];
            }
        } catch (PDOException $e) {
            error_log("Error en toggle post: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error de base de datos'];
        }
    }
}
