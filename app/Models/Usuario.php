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
        public function actualizarPerfil($usuario_id, $nombre_artistico, $biografia, $redes_sociales) {
            try {
                $sql = "UPDATE perfiles SET nombre_artistico = ?, biografia = ?, redes_sociales_json = ? WHERE usuario_id = ?";
                
                $stmtActu = $this->db->prepare($sql);
                $redesJson = json_encode($redes_sociales);

                return $stmtActu->execute([$nombre_artistico, $biografia, $redesJson, $usuario_id]);
            } catch (PDOException $e) {
                return false;
            }
        }
}
