<?php

require_once '../vendor/autoload.php';
require_once '../app/Config/Database.php';

$action = $_GET['action'] ?? 'home';

echo "Bienvenido a ITERALL - Acción: " . $action;
