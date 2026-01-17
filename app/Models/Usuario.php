<?php
class Usuario {
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
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

    public function actualizarPerfil($usuario_id, $nombre_artistico, $biografia, $redes_sociales, $avatar = null, $banner = null) {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("SELECT avatar_url, banner_url FROM perfiles WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
            $perfilActual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $avatarUrl = $perfilActual['avatar_url'] ?? null;
            $bannerUrl = $perfilActual['banner_url'] ?? null;
            
            if ($avatar && $avatar['error'] === UPLOAD_ERR_OK) {
                if ($perfilActual['avatar_url']) {
                    $this->eliminarImagenPerfil($perfilActual['avatar_url']);
                }
                
                $resultAvatar = CloudinaryConfig::upload($avatar, 'avatar', ['usuario_id' => $usuario_id]);
                if ($resultAvatar) {
                    $avatarUrl = $resultAvatar['url'];
                }
            }
            
            if ($banner && $banner['error'] === UPLOAD_ERR_OK) {
                if ($perfilActual['banner_url']) {
                    $this->eliminarImagenPerfil($perfilActual['banner_url']);
                }
                
                $resultBanner = CloudinaryConfig::upload($banner, 'banner', ['usuario_id' => $usuario_id]);
                if ($resultBanner) {
                    $bannerUrl = $resultBanner['url'];
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
        if (empty($url)) return;
        
        try {
            $pattern = '/\/upload\/(?:v\d+\/)?(.+)\.\w+$/';
            if (preg_match($pattern, $url, $matches)) {
                $cloudId = $matches[1];
                CloudinaryConfig::delete($cloudId);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar imagen de perfil: " . $e->getMessage());
        }
    }
}
