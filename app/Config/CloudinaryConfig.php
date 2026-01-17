<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class CloudinaryConfig {
    private static $initialized = false;
    
    private static function init() {
        if (!self::$initialized) {
            if (!isset($_ENV['CLOUDINARY_CLOUD_NAME'])) {
                $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
                $dotenv->safeLoad(); 
            }
            
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
                    'api_key' => $_ENV['CLOUDINARY_API_KEY'],
                    'api_secret' => $_ENV['CLOUDINARY_API_SECRET']
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
            
            self::$initialized = true;
        }
    }
    
    /**
     * 
     * @param array $file 
     * @param string $tipo 
     * @param array $metadata 
     * @return array|false 
     */
    public static function upload($file, $tipo, $metadata = []) {
        self::init();
        
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            error_log("Error en upload: archivo no válido o error en upload");
            return false;
        }
        
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            error_log("Error: archivo excede 5MB");
            return false;
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            error_log("Error: tipo de archivo no permitido - $mimeType");
            return false;
        }
        
        try {
            $publicId = self::buildPublicId($tipo, $metadata);
            
            $tags = self::buildTags($tipo, $metadata);
            
            $options = [
                'folder' => "iterall/" . self::getFolderByType($tipo),
                'public_id' => $publicId,
                'overwrite' => self::shouldOverwrite($tipo),
                'resource_type' => 'image',
                'tags' => $tags,
                'context' => $metadata
            ];
            
            $uploadApi = new UploadApi();
            $result = $uploadApi->upload($file['tmp_name'], $options);
            
            return [
                'url' => $result['secure_url'],
                'cloud_id' => $result['public_id']
            ];
            
        } catch (Exception $e) {
            error_log("Error al subir a Cloudinary: " . $e->getMessage());
            return false;
        }
    }
    
    public static function delete($cloudId) {
        self::init();
        
        try {
            $uploadApi = new UploadApi();
            $result = $uploadApi->destroy($cloudId);
            return $result['result'] === 'ok';
        } catch (Exception $e) {
            error_log("Error al eliminar de Cloudinary: " . $e->getMessage());
            return false;
        }
    }
    
    private static function getFolderByType($tipo) {
        $folders = [
            'avatar' => 'avatars',
            'banner' => 'banners',
            'post' => 'posts',
            'iteration' => 'iterations'
        ];
        return $folders[$tipo] ?? 'misc';
    }
    
    private static function buildPublicId($tipo, $metadata) {
        switch ($tipo) {
            case 'avatar':
                return 'user_' . $metadata['usuario_id'];
            
            case 'banner':
                return 'profile_' . $metadata['usuario_id'];
            
            case 'post':
                return 'post_' . $metadata['post_id'];
            
            case 'iteration':
                $uuid = substr(uniqid(), -6);
                return sprintf(
                    'post_%d_v%d_%s',
                    $metadata['post_id'],
                    $metadata['numero_version'],
                    $uuid
                );
            
            default:
                return 'misc_' . uniqid();
        }
    }
    
    private static function buildTags($tipo, $metadata) {
        $tags = ['type:' . $tipo];
        
        if (isset($metadata['usuario_id'])) {
            $tags[] = 'user:' . $metadata['usuario_id'];
        }
        
        if (isset($metadata['post_id'])) {
            $tags[] = 'post:' . $metadata['post_id'];
        }
        
        if (isset($metadata['numero_version'])) {
            $tags[] = 'iteration:' . $metadata['numero_version'];
        }
        
        return $tags;
    }
    
    private static function shouldOverwrite($tipo) {
        return in_array($tipo, ['avatar', 'banner']);
    }
}