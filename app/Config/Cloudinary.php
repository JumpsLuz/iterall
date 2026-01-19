<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class CloudinaryConfig {
    private static $instance = null;
    private $uploadApi;

    const DEFAULT_AVATAR_PUBLIC_ID = 'iterall/default/default_user';
    const DEFAULT_BANNER_PUBLIC_ID = 'iterall/default/default_banner';

    private function __construct() {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

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

        $this->uploadApi = new UploadApi();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new CloudinaryConfig();
        }
        return self::$instance;
    }

    /**
     * @return string 
     */
    public static function getDefaultAvatarUrl() {
        return "https://res.cloudinary.com/dyqubcdf0/image/upload/v1768774226/default_user.png";
    }

    /**
     * @return string 
     */
    public static function getDefaultBannerUrl() {
        return "https://res.cloudinary.com/dyqubcdf0/image/upload/v1768774145/default_banner.png";
    }

    /**
     * @param string $filePath 
     * @param array $options 
     * @return array 
     */
    public function uploadImage($filePath, $options = []) {
        
        try {
            error_log("=== CLOUDINARY DEBUG ===");
            error_log("File Path: " . $filePath);
            error_log("File exists: " . (file_exists($filePath) ? 'YES' : 'NO'));
            error_log("Options: " . json_encode($options));
        
            $defaultOptions = [
                'folder' => 'iterall/iteraciones',
                'resource_type' => 'image',
                'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'auto'
                ]
            ];

            $uploadOptions = array_merge($defaultOptions, $options);
            
            $result = $this->uploadApi->upload($filePath, $uploadOptions);

            error_log("Upload Success: " . json_encode($result));
        
            return [
                'success' => true,
                'url' => $result['secure_url'],
                'cloud_id' => $result['public_id'],
                'format' => $result['format'],
                'width' => $result['width'],
                'height' => $result['height']
            ];

        } catch (\Exception $e) {
            error_log("CLOUDINARY ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @param string $cloudId - 
     * @return bool
     */
    public function deleteImage($cloudId) {
        try {
            $result = $this->uploadApi->destroy($cloudId, ['resource_type' => 'image']);
            return $result['result'] === 'ok';
        } catch (\Exception $e) {
            error_log("Error al eliminar imagen de Cloudinary: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array $file 
     * @return array 
     */
    public static function validateImage($file) {
        $maxSize = 5 * 1024 * 1024; 
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'No se recibió ningún archivo'];
        }

        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'El archivo excede el tamaño máximo de 5MB'];
        }

        if (!in_array($file['type'], $allowedTypes)) {
            return ['valid' => false, 'error' => 'Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP'];
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'El archivo no es una imagen válida'];
        }

        return ['valid' => true, 'error' => null];
    }
}