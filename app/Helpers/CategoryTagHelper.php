<?php

/**
 * CategoryTagHelper
 * Helper for managing categories and tags for posts and projects
 * 
 * Note: Posts and projects have a single categoria_id column (not many-to-many)
 * Tags use the post_etiquetas and proyecto_etiquetas pivot tables
 */
class CategoryTagHelper {
    
    private static function getDb() {
        return Database::getInstance();
    }
    
    /**
     * Save category for a project (single category)
     * Projects have categoria_id column directly
     */
    public static function saveProjectCategories($proyecto_id, $categoria_ids) {
        if (empty($categoria_ids)) return;
        
        $db = self::getDb();
        // Take the first category (projects have single categoria_id)
        $categoria_id = is_array($categoria_ids) ? $categoria_ids[0] : $categoria_ids;
        
        $stmt = $db->prepare("UPDATE proyectos SET categoria_id = ? WHERE id = ?");
        $stmt->execute([$categoria_id, $proyecto_id]);
    }
    
    /**
     * Save category for a post (single category)
     * Posts have categoria_id column directly
     */
    public static function savePostCategories($post_id, $categoria_ids) {
        if (empty($categoria_ids)) return;
        
        $db = self::getDb();
        // Take the first category (posts have single categoria_id)
        $categoria_id = is_array($categoria_ids) ? $categoria_ids[0] : $categoria_ids;
        
        $stmt = $db->prepare("UPDATE posts SET categoria_id = ? WHERE id = ?");
        $stmt->execute([$categoria_id, $post_id]);
    }
    
    /**
     * Get category for a project (returns array for compatibility)
     */
    public static function getProjectCategories($proyecto_id) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("
                SELECT c.* FROM categorias c
                INNER JOIN proyectos p ON c.id = p.categoria_id
                WHERE p.id = ?
            ");
            $stmt->execute([$proyecto_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching project categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get category for a post (returns array for compatibility)
     */
    public static function getPostCategories($post_id) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("
                SELECT c.* FROM categorias c
                INNER JOIN posts p ON c.id = p.categoria_id
                WHERE p.id = ?
            ");
            $stmt->execute([$post_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching post categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save tags for a project (create if they don't exist)
     */
    public static function saveProjectTags($proyecto_id, $tag_names) {
        $db = self::getDb();
        
        // Delete existing tags
        $stmt = $db->prepare("DELETE FROM proyecto_etiquetas WHERE proyecto_id = ?");
        $stmt->execute([$proyecto_id]);
        
        foreach ($tag_names as $tag_name) {
            $tag_name = trim($tag_name);
            if (empty($tag_name) || $tag_name === '#@#_no_mini_proyecto_#@#' || strtolower($tag_name) === 'destacado') {
                continue;
            }
            
            // Get or create tag
            $tag_id = self::getOrCreateTag($tag_name);
            
            // Associate with project
            $stmt = $db->prepare("INSERT IGNORE INTO proyecto_etiquetas (proyecto_id, etiqueta_id) VALUES (?, ?)");
            $stmt->execute([$proyecto_id, $tag_id]);
        }
    }
    
    /**
     * Save tags for a post (create if they don't exist)
     */
    public static function savePostTags($post_id, $tag_names) {
        $db = self::getDb();
        
        // Delete existing tags
        $stmt = $db->prepare("DELETE FROM post_etiquetas WHERE post_id = ?");
        $stmt->execute([$post_id]);
        
        foreach ($tag_names as $tag_name) {
            $tag_name = trim($tag_name);
            if (empty($tag_name) || $tag_name === '#@#_no_mini_proyecto_#@#' || strtolower($tag_name) === 'destacado') {
                continue;
            }
            
            // Get or create tag
            $tag_id = self::getOrCreateTag($tag_name);
            
            // Associate with post
            $stmt = $db->prepare("INSERT IGNORE INTO post_etiquetas (post_id, etiqueta_id) VALUES (?, ?)");
            $stmt->execute([$post_id, $tag_id]);
        }
    }
    
    /**
     * Get or create a tag
     */
    private static function getOrCreateTag($tag_name) {
        $db = self::getDb();
        
        // Try to find existing
        $stmt = $db->prepare("SELECT id FROM etiquetas WHERE nombre_etiqueta = ?");
        $stmt->execute([$tag_name]);
        $tag = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tag) {
            return $tag['id'];
        }
        
        // Create new
        $stmt = $db->prepare("INSERT INTO etiquetas (nombre_etiqueta) VALUES (?)");
        $stmt->execute([$tag_name]);
        return $db->lastInsertId();
    }
    
    /**
     * Get tags for a project
     */
    public static function getProjectTags($proyecto_id) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("
                SELECT e.* FROM etiquetas e
                INNER JOIN proyecto_etiquetas pe ON e.id = pe.etiqueta_id
                WHERE pe.proyecto_id = ?
                AND e.nombre_etiqueta != '#@#_no_mini_proyecto_#@#'
                AND LOWER(e.nombre_etiqueta) != 'destacado'
            ");
            $stmt->execute([$proyecto_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching project tags: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get tags for a post
     */
    public static function getPostTags($post_id) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("
                SELECT e.* FROM etiquetas e
                INNER JOIN post_etiquetas pe ON e.id = pe.etiqueta_id
                WHERE pe.post_id = ?
                AND e.nombre_etiqueta != '#@#_no_mini_proyecto_#@#'
                AND LOWER(e.nombre_etiqueta) != 'destacado'
            ");
            $stmt->execute([$post_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching post tags: " . $e->getMessage());
            return [];
        }
    }
}
