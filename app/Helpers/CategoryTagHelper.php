<?php

class CategoryTagHelper {
    
    private static function getDb() {
        return Database::getInstance();
    }
    
    /**
     * Save multiple categories for a project
     */
    public static function saveProjectCategories($proyecto_id, $categoria_ids) {
        $db = self::getDb();
        
        $stmt = $db->prepare("DELETE FROM proyecto_categorias WHERE proyecto_id = ?");
        $stmt->execute([$proyecto_id]);
        
        // Insert new
        $stmt = $db->prepare("INSERT INTO proyecto_categorias (proyecto_id, categoria_id) VALUES (?, ?)");
        foreach ($categoria_ids as $cat_id) {
            $stmt->execute([$proyecto_id, $cat_id]);
        }
    }
    
    /**
     * Save multiple categories for a post
     */
    public static function savePostCategories($post_id, $categoria_ids) {
        $db = self::getDb();
        
        $stmt = $db->prepare("DELETE FROM post_categorias WHERE post_id = ?");
        $stmt->execute([$post_id]);
        
        // Insert new
        $stmt = $db->prepare("INSERT INTO post_categorias (post_id, categoria_id) VALUES (?, ?)");
        foreach ($categoria_ids as $cat_id) {
            $stmt->execute([$post_id, $cat_id]);
        }
    }
    
    /**
     * Get categories for a project
     */
    public static function getProjectCategories($proyecto_id) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT c.* FROM categorias c
            INNER JOIN proyecto_categorias pc ON c.id = pc.categoria_id
            WHERE pc.proyecto_id = ?
        ");
        $stmt->execute([$proyecto_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get categories for a post
     */
    public static function getPostCategories($post_id) {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT c.* FROM categorias c
            INNER JOIN post_categorias pc ON c.id = pc.categoria_id
            WHERE pc.post_id = ?
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    }
    
    /**
     * Get tags for a post
     */
    public static function getPostTags($post_id) {
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
    }
}
