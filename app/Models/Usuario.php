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
}
