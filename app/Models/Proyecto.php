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
}