<?php
require_once __DIR__ . '/../Config/Cloudinary.php';

class Iteracion {
    private $db;
    private $cloudinary;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->cloudinary = CloudinaryConfig::getInstance();
    }

    /**
     * @param array $datos 
     * @param array $imagenes 
     * @return int|false 
     */
    public function crear($datos, $imagenes = []) {
        try {
            $this->db->beginTransaction();

            $stmtVersion = $this->db->prepare(
                "SELECT COALESCE(MAX(numero_version), 0) + 1 as siguiente_version 
                 FROM iteraciones 
                 WHERE post_id = ?"
            );
            $stmtVersion->execute([$datos['post_id']]);
            $numeroVersion = $stmtVersion->fetchColumn();

            $sqlIteracion = "INSERT INTO iteraciones 
                            (post_id, numero_version, notas_cambio, tiempo_dedicado_min) 
                            VALUES (?, ?, ?, ?)";
            
            $stmtIteracion = $this->db->prepare($sqlIteracion);
            $stmtIteracion->execute([
                $datos['post_id'],
                $numeroVersion,
                $datos['notas_cambio'] ?? '',
                $datos['tiempo_dedicado_min'] ?? null
            ]);

            $iteracionId = $this->db->lastInsertId();

            if (!empty($imagenes)) {
                $orden = 0;
                foreach ($imagenes as $index => $imagen) {
                    $validacion = CloudinaryConfig::validateImage($imagen);
                    if (!$validacion['valid']) {
                        throw new Exception($validacion['error']);
                    }

                    $uploadResult = $this->cloudinary->uploadImage(
                        $imagen['tmp_name'],
                        [
                            'folder' => "iterall/post_{$datos['post_id']}/v{$numeroVersion}",
                            'public_id' => "img_{$index}_" . uniqid()
                        ]
                    );

                    if (!$uploadResult['success']) {
                        throw new Exception("Error al subir imagen: " . $uploadResult['error']);
                    }

                    $sqlImagen = "INSERT INTO imagenes_iteracion 
                                 (iteracion_id, url_archivo, cloud_id, es_principal, orden_visual) 
                                 VALUES (?, ?, ?, ?, ?)";
                    
                    $stmtImagen = $this->db->prepare($sqlImagen);
                    $stmtImagen->execute([
                        $iteracionId,
                        $uploadResult['url'],
                        $uploadResult['cloud_id'],
                        ($index === 0) ? 1 : 0, 
                        $orden++
                    ]);
                }
            }

            $this->db->commit();
            return $iteracionId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al crear iteración: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param int $postId
     * @return array
     */
    public function obtenerPorPost($postId) {
        try {
            $sql = "SELECT i.*, 
                    (SELECT COUNT(*) FROM imagenes_iteracion WHERE iteracion_id = i.id) as total_imagenes
                    FROM iteraciones i
                    WHERE i.post_id = ?
                    ORDER BY i.numero_version DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$postId]);
            $iteraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($iteraciones as &$iteracion) {
                $iteracion['imagenes'] = $this->obtenerImagenes($iteracion['id']);
            }

            return $iteraciones;

        } catch (PDOException $e) {
            error_log("Error al obtener iteraciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param int $iteracionId
     * @return array
     */
    public function obtenerImagenes($iteracionId) {
        try {
            $sql = "SELECT * FROM imagenes_iteracion 
                    WHERE iteracion_id = ? 
                    ORDER BY orden_visual ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$iteracionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error al obtener imágenes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param int $iteracionId
     * @param int $usuarioId 
     * @return array|false
     */
    public function obtenerPorId($iteracionId, $usuarioId) {
        try {
            $sql = "SELECT i.*, p.creador_id
                    FROM iteraciones i
                    INNER JOIN posts p ON i.post_id = p.id
                    WHERE i.id = ? AND p.creador_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$iteracionId, $usuarioId]);
            $iteracion = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($iteracion) {
                $iteracion['imagenes'] = $this->obtenerImagenes($iteracionId);
            }

            return $iteracion;

        } catch (PDOException $e) {
            error_log("Error al obtener iteración: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param int $iteracionId
     * @param int $usuarioId 
     * @return bool
     */
    public function eliminar($iteracionId, $usuarioId) {
        try {
            $this->db->beginTransaction();

            $iteracion = $this->obtenerPorId($iteracionId, $usuarioId);
            if (!$iteracion) {
                throw new Exception("Iteración no encontrada o sin permisos");
            }

            foreach ($iteracion['imagenes'] as $imagen) {
                if (!empty($imagen['cloud_id'])) {
                    $this->cloudinary->deleteImage($imagen['cloud_id']);
                }
            }

            $stmt = $this->db->prepare("DELETE FROM iteraciones WHERE id = ?");
            $stmt->execute([$iteracionId]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al eliminar iteración: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param int $iteracionId
     * @param array $datos
     * @param int $usuarioId
     * @return bool
     */
    public function actualizar($iteracionId, $datos, $usuarioId) {
        try {
            // Verificar permisos
            $iteracion = $this->obtenerPorId($iteracionId, $usuarioId);
            if (!$iteracion) {
                return false;
            }

            $sql = "UPDATE iteraciones 
                    SET notas_cambio = ?, tiempo_dedicado_min = ? 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $datos['notas_cambio'] ?? $iteracion['notas_cambio'],
                $datos['tiempo_dedicado_min'] ?? $iteracion['tiempo_dedicado_min'],
                $iteracionId
            ]);

        } catch (PDOException $e) {
            error_log("Error al actualizar iteración: " . $e->getMessage());
            return false;
        }
    }
}