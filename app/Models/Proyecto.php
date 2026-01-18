<?php
require_once __DIR__ . '/../Config/Cloudinary.php';

class Proyecto {
    private $db;
    private $cloudinary;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->cloudinary = CloudinaryConfig::getInstance();
    }
    /**
     * @param array $datos
     * @param array|null $avatar_file
     * @param array|null $banner_file
     * @return int|false
     */
    public function crear($datos, $avatar_file = null, $banner_file = null) {
        try {
            $this->db->beginTransaction();

            if (empty($datos['creador_id']) || empty($datos['categoria_id']) || 
                empty($datos['estado_id']) || empty($datos['titulo'])) {
                error_log("Error: Faltan campos requeridos para crear proyecto");
                return false;
            }

            $avatarUrl = null;
            $bannerUrl = null;

            if ($avatar_file && !empty($avatar_file['tmp_name'])) {
                $validacion = CloudinaryConfig::validateImage($avatar_file);
                if ($validacion['valid']) {
                    $resultado = $this->cloudinary->uploadImage(
                        $avatar_file['tmp_name'],
                        [
                            'folder' => "iterall/proyectos/portadas",
                            'public_id' => "avatar_" . uniqid(),
                            'transformation' => [
                                'width' => 400,
                                'height' => 400,
                                'crop' => 'fill'
                            ]
                        ]
                    );

                    if ($resultado['success']) {
                        $avatarUrl = $resultado['url'];
                    } else {
                        error_log("Error subiendo avatar de proyecto: " . $resultado['error']);
                    }
                }
            }

            if ($banner_file && !empty($banner_file['tmp_name'])) {
                $validacion = CloudinaryConfig::validateImage($banner_file);
                if ($validacion['valid']) {
                    $resultado = $this->cloudinary->uploadImage(
                        $banner_file['tmp_name'],
                        [
                            'folder' => "iterall/proyectos/banners",
                            'public_id' => "banner_" . uniqid(),
                            'transformation' => [
                                'width' => 1500,
                                'height' => 500,
                                'crop' => 'fill'
                            ]
                        ]
                    );

                    if ($resultado['success']) {
                        $bannerUrl = $resultado['url'];
                    } else {
                        error_log("Error subiendo banner de proyecto: " . $resultado['error']);
                    }
                }
            }

            $sql = "INSERT INTO proyectos (creador_id, titulo, descripcion, categoria_id, estado_id, es_publico, avatar_url, banner_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                $datos['creador_id'],
                $datos['titulo'],
                $datos['descripcion'] ?? '',
                $datos['categoria_id'],
                $datos['estado_id'],
                $datos['es_publico'] ?? 0,
                $avatarUrl,
                $bannerUrl
            ]);

            if (!$resultado) {
                throw new Exception("Error al insertar proyecto en BD");
            }

            $proyectoId = $this->db->lastInsertId();
            $this->db->commit();
            
            return $proyectoId;

        } catch (Exception $e) {
            $this->db->rollBack();
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

    /**
     * @param int $proyecto_id
     * @param array $datos
     * @param int $usuario_id
     * @param array|null $avatar_file
     * @param array|null $banner_file
     * @return bool
     */
    
    public function actualizar($proyecto_id, $datos, $usuario_id, $avatar_file = null, $banner_file = null) {
        try {

            $this->db->beginTransaction();

            if (empty($datos['titulo']) || empty($datos['categoria_id']) || empty($datos['estado_id'])) {
                error_log("Error: Faltan campos requeridos para actualizar proyecto");
                return false;
            }

            $proyectoActual = $this->obtenerPorId($proyecto_id, $usuario_id);
            if (!$proyectoActual) {
                throw new Exception("Proyecto no encontrado");
            }

            $avatarUrl = $proyectoActual['avatar_url'];
            $bannerUrl = $proyectoActual['banner_url'];

            if ($avatar_file && !empty($avatar_file['tmp_name'])) {
                $validacion = CloudinaryConfig::validateImage($avatar_file);
                if ($validacion['valid']) {
                    if (!empty($avatarUrl)) {
                        $this->eliminarImagenProyecto($avatarUrl);
                    }

                    $resultado = $this->cloudinary->uploadImage(
                        $avatar_file['tmp_name'],
                        [
                            'folder' => "iterall/proyectos/portadas",
                            'public_id' => "avatar_{$proyecto_id}_" . uniqid(),
                            'transformation' => [
                                'width' => 400,
                                'height' => 400,
                                'crop' => 'fill'
                            ]
                        ]
                    );

                    if ($resultado['success']) {
                        $avatarUrl = $resultado['url'];
                    }
                }
            }

            if ($banner_file && !empty($banner_file['tmp_name'])) {
                $validacion = CloudinaryConfig::validateImage($banner_file);
                if ($validacion['valid']) {
                    if (!empty($bannerUrl)) {
                        $this->eliminarImagenProyecto($bannerUrl);
                    }

                    $resultado = $this->cloudinary->uploadImage(
                        $banner_file['tmp_name'],
                        [
                            'folder' => "iterall/proyectos/banners",
                            'public_id' => "banner_{$proyecto_id}_" . uniqid(),
                            'transformation' => [
                                'width' => 1500,
                                'height' => 500,
                                'crop' => 'fill'
                            ]
                        ]
                    );

                    if ($resultado['success']) {
                        $bannerUrl = $resultado['url'];
                    }
                }
            }

            $sql = "UPDATE proyectos 
                    SET titulo = ?, descripcion = ?, categoria_id = ?, estado_id = ?, es_publico = ?, avatar_url = ?, banner_url = ?
                    WHERE id = ? AND creador_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $datos['titulo'],
                $datos['descripcion'],
                $datos['categoria_id'],
                $datos['estado_id'],
                $datos['es_publico'] ?? 0,
                $avatarUrl,
                $bannerUrl,
                $proyecto_id,
                $usuario_id
            ]);

            if ($result) {
                $this->db->commit();
                return true;
            } else {
                throw new Exception("Error al actualizar en BD");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al actualizar proyecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param string $url
     * @return bool
     */

    public function eliminarImagenProyecto($url) {
        if (empty($url)) return false;

        try {
            preg_match('/\/iterall\/[^.]+/', $url, $matches);
            
            if (!empty($matches[0])) {
                $publicId = ltrim($matches[0], '/');
                return $this->cloudinary->deleteImage($publicId);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error al eliminar imagen de proyecto: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($proyecto_id, $usuario_id) {
        try {
            $this->db->beginTransaction();

            $proyecto = $this->obtenerPorId($proyecto_id, $usuario_id);
            
            if (!$proyecto) {
                throw new Exception("No autorizado para eliminar este proyecto.");
            }

            if (!empty($proyecto['avatar_url'])) {
                $this->eliminarImagenProyecto($proyecto['avatar_url']);
            }
            if (!empty($proyecto['banner_url'])) {
                $this->eliminarImagenProyecto($proyecto['banner_url']);
            }

            $stmt = $this->db->prepare("DELETE FROM proyectos WHERE id = ?");
            $stmt->execute([$proyecto_id]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
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