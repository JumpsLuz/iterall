<?php
require_once 'vendor/autoload.php';
require_once 'app/Config/Cloudinary.php';

echo "<h2>Test de Cloudinary</h2>";

try {
    $cloud = CloudinaryConfig::getInstance();
    echo "✓ Conexión establecida correctamente<br>";
    echo "Cloud Name: " . $_ENV['CLOUDINARY_CLOUD_NAME'] . "<br>";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}