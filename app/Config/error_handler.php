<?php
/**
 * Archivo de configuración de errores para producción
 * Include este archivo al inicio de Database.php y Cloudinary.php
 */

// Solo en desarrollo (localhost)
if ($_SERVER['SERVER_NAME'] === 'localhost' || strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Producción: Log errores, no mostrarlos
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../../error_log.txt');
}

// Función global para logging
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../../error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr\n";
    @file_put_contents($logFile, $logMessage, FILE_APPEND);
}
