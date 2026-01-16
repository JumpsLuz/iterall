<?php
class Proyecto {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear($datos) {
        try {
            $sql = "INSERT INTO proyectos (creador_id, categoria_id, estado_id, titulo, descripcion, es_publico) VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $datos['creador_id'],
                $datos['categoria_id'],
                $datos['estado_id'],
                $datos['titulo'],
                $datos['descripcion'],
                $datos['es_publico']
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear proyecto: " . $e->getMessage());
            return false;
        }
    }
    public function obtenerPorUsuario($usuario_id) {
        try {
            $sql = "SELECT p.*, c.nombre_categoria, e.nombre_estado FROM proyectos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    LEFT JOIN estados_proyecto e ON p.estado_id = e.id
                    WHERE p.creador_id = ? ORDER BY p.fecha_actualizacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener proyectos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId($proyecto_id, $usuario_id = null) {
        try {
            $sql = "SELECT p.*, c.nombre_categoria, e.nombre_estado FROM proyectos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    LEFT JOIN estados_proyecto e ON p.estado_id = e.id
                    WHERE p.id = ?";
            
            if ($usuario_id) {
                $sql .= " AND p.creador_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$proyecto_id, $usuario_id]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$proyecto_id]);
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener proyecto: " . $e->getMessage());
            return false;
        }
    }
    public function actualizar($proyecto_id, $datos, $usuario_id) {
        try {
            $sql = "UPDATE proyectos 
                    SET titulo = ?, descripcion = ?, categoria_id = ?, estado_id = ?, es_publico = ?
                    WHERE id = ? AND creador_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $datos['titulo'],
                $datos['descripcion'],
                $datos['categoria_id'],
                $datos['estado_id'],
                $datos['es_publico'] ?? 0,
                $proyecto_id,
                $usuario_id
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar proyecto: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($proyecto_id, $usuario_id) {
        try {
            $sql = "DELETE FROM proyectos WHERE id = ? AND creador_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$proyecto_id, $usuario_id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar proyecto: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerCategorias() {
        try {
            $sql = "SELECT * FROM categorias ORDER BY nombre_categoria";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerEstados() {
        try {
            $sql = "SELECT * FROM estados_proyecto ORDER BY id";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estados: " . $e->getMessage());
            return [];
        }
    }
}