<?php
class Post {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear($datos) {
        try {
            $sql = "INSERT INTO posts (creador_id, titulo, categoria_id, miniproyecto_id, proyecto_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            $resultado = $stmt->execute([
                $datos['creador_id'],
                $datos['titulo'],
                $datos['categoria_id'],
                $datos['miniproyecto_id'] ?? null,
                $datos['proyecto_id'] ?? null
            ]);
            
            return $resultado ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Error al crear post: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorId($id, $usuario_id) {
        $sql = "SELECT p.*, c.nombre_categoria, mp.descripcion as descripcion_miniproyecto,
                (SELECT url_archivo FROM imagenes_iteracion ii
                 JOIN iteraciones i ON ii.iteracion_id = i.id
                 WHERE i.post_id = p.id AND ii.es_principal = 1
                 ORDER BY i.numero_version DESC LIMIT 1) as portada
                FROM posts p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN miniproyectos mp ON p.miniproyecto_id = mp.id
                WHERE p.id = ? AND p.creador_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerIteraciones($post_id) {
        $sql = "SELECT * FROM iteraciones WHERE post_id = ? ORDER BY numero_version DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDestacados($usuario_id) {
        try {
            $sql = "SELECT p.*, mp.titulo AS nombre_miniproyecto, c.nombre_categoria,
                    (SELECT url_archivo FROM imagenes_iteracion ii
                     JOIN iteraciones i ON ii.iteracion_id = i.id
                     WHERE i.post_id = p.id AND ii.es_principal = 1
                     ORDER BY i.numero_version DESC LIMIT 1) as portada
                    FROM posts p 
                    JOIN post_etiquetas pe ON p.id = pe.post_id
                    JOIN etiquetas e ON pe.etiqueta_id = e.id
                    LEFT JOIN miniproyectos mp ON p.miniproyecto_id = mp.id
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    WHERE p.creador_id = ? AND e.nombre_etiqueta = 'Destacado'
                    ORDER BY p.fecha_creacion DESC LIMIT 5";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener destacados: " . $e->getMessage());
            return [];
        }
    }

    public function contarDestacados($usuario_id) {
        $sql = "SELECT COUNT(*) FROM posts p
                JOIN post_etiquetas pe ON p.id = pe.post_id
                JOIN etiquetas e ON pe.etiqueta_id = e.id
                WHERE p.creador_id = ? AND e.nombre_etiqueta = 'Destacado'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchColumn();
    }

    public function toggleDestacado($post_id, $usuario_id) {
        try {
            $checkOwner = $this->db->prepare("SELECT creador_id FROM posts WHERE id = ?");
            $checkOwner->execute([$post_id]);
            $post = $checkOwner->fetch(PDO::FETCH_ASSOC);

            if (!$post || $post['creador_id'] != $usuario_id) {
                error_log("Intento de destacar post ajeno. Post: $post_id, Usuario: $usuario_id");
                return false;
            }

            $stmtEtiqueta = $this->db->prepare("SELECT id FROM etiquetas WHERE nombre_etiqueta = 'Destacado'");
            $stmtEtiqueta->execute();
            $etiqueta = $stmtEtiqueta->fetch(PDO::FETCH_ASSOC);

            if (!$etiqueta) {
                $this->db->exec("INSERT INTO etiquetas (nombre_etiqueta) VALUES ('Destacado')");
                $etiqueta_id = $this->db->lastInsertId();
            } else {
                $etiqueta_id = $etiqueta['id'];
            }

            $check = $this->db->prepare("SELECT * FROM post_etiquetas WHERE post_id = ? AND etiqueta_id = ?");
            $check->execute([$post_id, $etiqueta_id]);

            if ($check->fetch()) {
                $del = $this->db->prepare("DELETE FROM post_etiquetas WHERE post_id = ? AND etiqueta_id = ?");
                return $del->execute([$post_id, $etiqueta_id]);
            } else {
                if ($this->contarDestacados($usuario_id) >= 5) {
                    return 'limite_alcanzado';
                }
                $ins = $this->db->prepare("INSERT INTO post_etiquetas (post_id, etiqueta_id) VALUES (?, ?)");
                return $ins->execute([$post_id, $etiqueta_id]);
            }
        } catch (PDOException $e) {
            error_log("Error en toggleDestacado: " . $e->getMessage());
            return false;
        }
    }

    public function esDestacado($post_id) {
        $sql = "SELECT COUNT(*) FROM post_etiquetas pe
                JOIN etiquetas e ON pe.etiqueta_id = e.id
                WHERE pe.post_id = ? AND e.nombre_etiqueta = 'Destacado'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$post_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function obtenerPorMiniproyecto($miniproyecto_id) {
        try {
            $sql = "SELECT p.*, c.nombre_categoria,
                    (SELECT url_archivo FROM imagenes_iteracion ii
                     JOIN iteraciones i ON ii.iteracion_id = i.id
                     WHERE i.post_id = p.id AND ii.es_principal = 1
                     ORDER BY i.numero_version DESC LIMIT 1) as portada
                    FROM posts p
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    WHERE p.miniproyecto_id = ? 
                    ORDER BY p.fecha_creacion ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$miniproyecto_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener posts por miniproyecto: " . $e->getMessage());
            return [];
        }
    }
    
    public function convertirAMiniproyecto($post_id, $usuario_id) {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("SELECT p.*, mp.id as mini_id FROM posts p 
                                        LEFT JOIN miniproyectos mp ON p.miniproyecto_id = mp.id
                                        WHERE p.id = ? AND p.creador_id = ?");
            $stmt->execute([$post_id, $usuario_id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$post) {
                throw new Exception("Post no encontrado");
            }
            
            if ($post['mini_id']) {
                $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM posts WHERE miniproyecto_id = ?");
                $stmtCount->execute([$post['mini_id']]);
                $count = $stmtCount->fetchColumn();
                
                if ($count > 1) {
                    throw new Exception("Este post ya forma parte de un miniproyecto con múltiples items");
                }
                
                $stmtEtiqueta = $this->db->prepare("SELECT id FROM etiquetas WHERE nombre_etiqueta = ?");
                $stmtEtiqueta->execute(['#@#_no_mini_proyecto_#@#']);
                $etiqueta = $stmtEtiqueta->fetch(PDO::FETCH_ASSOC);
                
                if ($etiqueta) {
                    $stmtDel = $this->db->prepare("DELETE FROM post_etiquetas WHERE post_id = ? AND etiqueta_id = ?");
                    $stmtDel->execute([$post_id, $etiqueta['id']]);
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al convertir a miniproyecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener posts públicos para exploración
     * Filtra por categoría, etiquetas, búsqueda y ordenamiento
     */
    public function obtenerPublicos($filtros = []) {
        try {
            $params = [];
            $where = ["1=1"]; // Base condition
            
            // Solo posts que pertenecen a proyectos públicos o sin proyecto
            $where[] = "(pr.es_publico = 1 OR p.proyecto_id IS NULL)";
            
            // Filtro por categoría
            if (!empty($filtros['categoria_id'])) {
                $where[] = "p.categoria_id = ?";
                $params[] = $filtros['categoria_id'];
            }
            
            // Filtro por búsqueda de texto
            if (!empty($filtros['busqueda'])) {
                $where[] = "(p.titulo LIKE ? OR pf.nombre_artistico LIKE ?)";
                $busqueda = '%' . $filtros['busqueda'] . '%';
                $params[] = $busqueda;
                $params[] = $busqueda;
            }
            
            // Filtro por etiqueta
            if (!empty($filtros['etiqueta'])) {
                $where[] = "EXISTS (SELECT 1 FROM post_etiquetas pe2 
                            JOIN etiquetas e2 ON pe2.etiqueta_id = e2.id 
                            WHERE pe2.post_id = p.id AND e2.nombre_etiqueta = ?)";
                $params[] = $filtros['etiqueta'];
            }
            
            // Filtro por artista
            if (!empty($filtros['artista_id'])) {
                $where[] = "p.creador_id = ?";
                $params[] = $filtros['artista_id'];
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Ordenamiento
            $orden = "p.fecha_creacion DESC"; // Por defecto: más recientes
            if (!empty($filtros['orden'])) {
                switch ($filtros['orden']) {
                    case 'antiguo':
                        $orden = "p.fecha_creacion ASC";
                        break;
                    case 'iteraciones':
                        $orden = "total_iteraciones DESC, p.fecha_creacion DESC";
                        break;
                }
            }
            
            // Paginación
            $limite = $filtros['limite'] ?? 24;
            $offset = $filtros['offset'] ?? 0;
            
            $sql = "SELECT p.*, 
                    pf.nombre_artistico, pf.avatar_url as artista_avatar, u.id as artista_id,
                    c.nombre_categoria,
                    (SELECT COUNT(*) FROM iteraciones WHERE post_id = p.id) as total_iteraciones,
                    (SELECT url_archivo FROM imagenes_iteracion ii
                     JOIN iteraciones i ON ii.iteracion_id = i.id
                     WHERE i.post_id = p.id AND ii.es_principal = 1
                     ORDER BY i.numero_version DESC LIMIT 1) as portada
                    FROM posts p
                    JOIN usuarios u ON p.creador_id = u.id
                    JOIN perfiles pf ON pf.usuario_id = u.id
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
                    WHERE $whereClause
                    ORDER BY $orden
                    LIMIT $limite OFFSET $offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener posts públicos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar total de posts públicos (para paginación)
     */
    public function contarPublicos($filtros = []) {
        try {
            $params = [];
            $where = ["1=1"];
            $where[] = "(pr.es_publico = 1 OR p.proyecto_id IS NULL)";
            
            if (!empty($filtros['categoria_id'])) {
                $where[] = "p.categoria_id = ?";
                $params[] = $filtros['categoria_id'];
            }
            
            if (!empty($filtros['busqueda'])) {
                $where[] = "(p.titulo LIKE ? OR pf.nombre_artistico LIKE ?)";
                $busqueda = '%' . $filtros['busqueda'] . '%';
                $params[] = $busqueda;
                $params[] = $busqueda;
            }
            
            if (!empty($filtros['etiqueta'])) {
                $where[] = "EXISTS (SELECT 1 FROM post_etiquetas pe2 
                            JOIN etiquetas e2 ON pe2.etiqueta_id = e2.id 
                            WHERE pe2.post_id = p.id AND e2.nombre_etiqueta = ?)";
                $params[] = $filtros['etiqueta'];
            }
            
            if (!empty($filtros['artista_id'])) {
                $where[] = "p.creador_id = ?";
                $params[] = $filtros['artista_id'];
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT COUNT(DISTINCT p.id)
                    FROM posts p
                    JOIN usuarios u ON p.creador_id = u.id
                    JOIN perfiles pf ON pf.usuario_id = u.id
                    LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
                    WHERE $whereClause";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar posts públicos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener un post público por ID (sin verificar usuario)
     */
    public function obtenerPublicoPorId($post_id) {
        try {
            $sql = "SELECT p.*, 
                    pf.nombre_artistico, pf.avatar_url as artista_avatar, pf.biografia,
                    u.id as artista_id,
                    c.nombre_categoria,
                    pr.es_publico as proyecto_publico
                    FROM posts p
                    JOIN usuarios u ON p.creador_id = u.id
                    JOIN perfiles pf ON pf.usuario_id = u.id
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    LEFT JOIN proyectos pr ON p.proyecto_id = pr.id
                    WHERE p.id = ? AND (pr.es_publico = 1 OR p.proyecto_id IS NULL)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$post_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener post público: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener etiquetas populares
     */
    public function obtenerEtiquetasPopulares($limite = 20) {
        try {
            $sql = "SELECT e.nombre_etiqueta, COUNT(pe.post_id) as uso_count
                    FROM etiquetas e
                    JOIN post_etiquetas pe ON e.id = pe.etiqueta_id
                    WHERE e.nombre_etiqueta != '#@#_no_mini_proyecto_#@#'
                    AND LOWER(e.nombre_etiqueta) != 'destacado'
                    GROUP BY e.id
                    ORDER BY uso_count DESC
                    LIMIT " . (int)$limite;
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener etiquetas populares: " . $e->getMessage());
            return [];
        }
    }
}
