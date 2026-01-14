<?php
class Usuario {
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function registrar($email, $password, $rol_id) {
        try {
            $this->db->conn->beginTransaction();

            $passHashed = password_hash($password, PASSWORD_BCRYPT);

            $sqlUser = "INSERT INTO usuarios (email, password, rol_id) VALUES (?, ?, ?)";

            $stmtUser = $this->db->conn->prepare($sqlUser);
            $stmtUser->execute([$email, $passHashed, $rol_id]);

            $usuarioId = $this->db->conn->lastInsertId();

            $sqlPerfil = "INSERT INTO perfiles (usuario_id) VALUES (?)";
            $stmtPerfil = $this->db->conn->prepare($sqlPerfil);
            $stmtPerfil->execute([$usuarioId]);

            $this->db->conn->commit();
            return true;
            } catch (Exception $e) {
            $this->db->conn->rollBack();
            return false;
            }
        }
        public function autenticar($email, $password) {
            $sql = "SELECT * FROM usuarios WHERE email = ?";
            $stmt = $this->db->conn->prepare($sql);
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                return $usuario;
            }
            return false;
        }
        }
