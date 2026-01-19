<?php
header('Content-Type: application/json');
require_once '../../app/Config/Database.php';

if (!isset($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$query = trim($_GET['q']);
$db = Database::getInstance();

$stmt = $db->prepare("
    SELECT id, nombre_etiqueta 
    FROM etiquetas 
    WHERE nombre_etiqueta LIKE ? 
    AND nombre_etiqueta != '#@#_no_mini_proyecto_#@#'
    AND nombre_etiqueta != 'destacado'
    ORDER BY nombre_etiqueta 
    LIMIT 10
");

$searchTerm = '%' . $query . '%';
$stmt->execute([$searchTerm]);
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($tags);
