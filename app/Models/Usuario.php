<?php
require_once __DIR__ . '/../Config/Cloudinary.php';

class Usuario {
    private $db;
    private $cloudinary;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->cloudinary = CloudinaryConfig::getInstance();
    }

    public function registrar($email, $password, $rol_id) {
        try {
            $this->db->beginTransaction();

            $passHashed = password_hash($password, PASSWORD_BCRYPT);

            $sqlUser = "INSERT INTO usuarios (email, password, rol_id) VALUES (?, ?, ?)";

            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->execute([$email, $passHashed, $rol_id]);

            $usuarioId = $this->db->lastInsertId();

            $sqlPerfil = "INSERT INTO perfiles (usuario_id) VALUES (?)";
            $stmtPerfil = $this->db->prepare($sqlPerfil);
            $stmtPerfil->execute([$usuarioId]);

            $this->db->commit();
            return true;
            } catch (Exception $e) {
            $this->db->rollBack();
            return false; 
            }
        }

        public function autenticar($email, $password) {
            $sql = "SELECT * FROM usuarios WHERE email = ?";
            $stmtAuth = $this->db->prepare($sql);
            $stmtAuth->execute([$email]);
            $usuario = $stmtAuth->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                return $usuario;
            }
            return false;
        }

        /**
         * @param int $usuario_id
         * @param string $nombre_artistico
         * @param string $biografia
         * @param array $redes_sociales
         * @param array|null $avatarFile
         * @param array|null $bannerFile
         * @return bool
         */

        public function actualizarPerfil($usuario_id, $nombre_artistico, $biografia, $redes_sociales, $avatarFile = null, $bannerFile = null) {
            try {
                $this->db->beginTransaction();

                $stmtActual = $this->db->prepare("SELECT avatar_url, banner_url FROM perfiles WHERE usuario_id = ?");
                $stmtActual->execute([$usuario_id]);
                $perfilActual = $stmtActual->fetch(PDO::FETCH_ASSOC);

                $avatarUrl = $perfilActual['avatar_url'];
                $bannerUrl = $perfilActual['banner_url'];

                if ($avatarFile && !empty($avatarFile['tmp_name'])) {
                    $validacion = CloudinaryConfig::validateImage($avatarFile);
                    if ($validacion['valid']) {
                        if (!empty($avatarUrl)) {
                            $this->eliminarImagenPerfil($avatarUrl);
                        }

                        $resultado = $this->cloudinary->uploadImage(
                            $avatarFile['tmp_name'],
                            [
                                'folder' => "iterall/usuarios/avatares",
                                'public_id' => "usuario_{$usuario_id}_avatar_" . uniqid()
                            ]
                        );

                        if ($resultado['success']) {
                            $avatarUrl = $resultado['url'];
                        } else {
                            error_log("Error subiendo avatar: " . $resultado['error']);
                        }
                    } else {
                        error_log("Validación avatar falló: " . $validacion['error']);
                    }
                }

                if ($bannerFile && !empty($bannerFile['tmp_name'])) {
                    $validacion = CloudinaryConfig::validateImage($bannerFile);
                    if ($validacion['valid']) {
                        if (!empty($bannerUrl)) {
                            $this->eliminarImagenPerfil($bannerUrl);
                        }

                        $resultado = $this->cloudinary->uploadImage(
                            $bannerFile['tmp_name'],
                            [
                                'folder' => "iterall/usuarios/banners",
                                'public_id' => "usuario_{$usuario_id}_banner_" . uniqid()
                            ]
                        );

                        if ($resultado['success']) {
                            $bannerUrl = $resultado['url'];
                        } else {
                            error_log("Error subiendo banner: " . $resultado['error']);
                        }
                    } else {
                        error_log("Validación banner falló: " . $validacion['error']);
                    }
                }

                $sql = "UPDATE perfiles 
                        SET nombre_artistico = ?, 
                            biografia = ?, 
                            redes_sociales_json = ?, 
                            avatar_url = ?, 
                            banner_url = ? 
                        WHERE usuario_id = ?";
                
                $stmtActu = $this->db->prepare($sql);
                $redesJson = json_encode($redes_sociales);

                $resultado = $stmtActu->execute([
                    $nombre_artistico, 
                    $biografia, 
                    $redesJson,
                    $avatarUrl,
                    $bannerUrl,
                    $usuario_id
                ]);

                $this->db->commit();
                return $resultado;

            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Error al actualizar perfil: " . $e->getMessage());
                return false;
            }
        }

        private function eliminarImagenPerfil($url) {
            if (empty($url)) return false;

            preg_match('/\/iterall\/[^.]+/', $url, $matches);
            if (!empty($matches[0])) {
                return $this->cloudinary->deleteImage(ltrim($matches[0], '/'));
            }
            return false;
        }

        public function eliminarCuenta($usuario_id) {
            try {
                $this->db->beginTransaction();

                // Get all projects for this user
                $stmtProyectos = $this->db->prepare("SELECT id, avatar_url, banner_url FROM proyectos WHERE creador_id = ?");
                $stmtProyectos->execute([$usuario_id]);
                $proyectos = $stmtProyectos->fetchAll(PDO::FETCH_ASSOC);

                // Delete project images from Cloudinary
                foreach ($proyectos as $proyecto) {
                    if (!empty($proyecto['avatar_url'])) {
                        $this->eliminarImagenProyecto($proyecto['avatar_url']);
                    }
                    if (!empty($proyecto['banner_url'])) {
                        $this->eliminarImagenProyecto($proyecto['banner_url']);
                    }
                }

                // Get all images from posts/iterations for this user
                $stmtImgs = $this->db->prepare("
                    SELECT ii.cloud_id 
                    FROM imagenes_iteracion ii
                    INNER JOIN iteraciones i ON ii.iteracion_id = i.id
                    INNER JOIN posts p ON i.post_id = p.id
                    WHERE p.creador_id = ? AND ii.cloud_id IS NOT NULL
                ");
                $stmtImgs->execute([$usuario_id]);
                $imagenesCloud = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);

                // Delete images from Cloudinary
                foreach ($imagenesCloud as $cloudId) {
                    $this->cloudinary->deleteImage($cloudId);
                }

                // Delete profile images
                $stmtPerfil = $this->db->prepare("SELECT avatar_url, banner_url FROM perfiles WHERE usuario_id = ?");
                $stmtPerfil->execute([$usuario_id]);
                $perfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);

                if (!empty($perfil['avatar_url'])) {
                    $this->eliminarImagenPerfil($perfil['avatar_url']);
                }
                if (!empty($perfil['banner_url'])) {
                    $this->eliminarImagenPerfil($perfil['banner_url']);
                }

                // Delete all data in correct order (to handle potential foreign keys)
                // Delete imagenes_iteracion
                $this->db->prepare("DELETE ii FROM imagenes_iteracion ii INNER JOIN iteraciones i ON ii.iteracion_id = i.id INNER JOIN posts p ON i.post_id = p.id WHERE p.creador_id = ?")->execute([$usuario_id]);

                // Delete iteraciones
                $this->db->prepare("DELETE i FROM iteraciones i INNER JOIN posts p ON i.post_id = p.id WHERE p.creador_id = ?")->execute([$usuario_id]);

                // Delete posts
                $this->db->prepare("DELETE FROM posts WHERE creador_id = ?")->execute([$usuario_id]);

                // Delete miniproyectos
                $this->db->prepare("DELETE FROM miniproyectos WHERE creador_id = ?")->execute([$usuario_id]);

                // Delete proyectos
                $this->db->prepare("DELETE FROM proyectos WHERE creador_id = ?")->execute([$usuario_id]);

                // Delete perfil
                $stmtDeletePerfil = $this->db->prepare("DELETE FROM perfiles WHERE usuario_id = ?");
                $stmtDeletePerfil->execute([$usuario_id]);

                // Delete usuario
                $stmtDeleteUsuario = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmtDeleteUsuario->execute([$usuario_id]);

                $this->db->commit();
                return true;

            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Error al eliminar cuenta: " . $e->getMessage());
                return false;
            }
        }

        private function eliminarImagenProyecto($url) {
            if (empty($url)) return false;

            preg_match('/\/iterall\/[^.]+/', $url, $matches);
            if (!empty($matches[0])) {
                return $this->cloudinary->deleteImage(ltrim($matches[0], '/'));
            }
            return false;
        }
}
