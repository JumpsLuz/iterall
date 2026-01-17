<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';

if ($_SESSION['rol_id'] != 1) { header('Location: explorar.php'); exit(); }

$proyecto_id = $_GET['proyecto_id'] ?? null; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Carpeta | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <div class="navbar">
            <a href="dashboard_artista.php" class="btn btn-secondary">← Cancelar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>📂 Crear Nueva Carpeta</h2>
                <p class="text-muted">Una carpeta te permite agrupar múltiples posts (bocetos, referencias, finales) bajo un mismo nombre.</p>

                <form action="procesador.php?action=crear_carpeta" method="POST">
                    
                    <?php if ($proyecto_id): ?>
                        <input type="hidden" name="proyecto_id" value="<?php echo htmlspecialchars($proyecto_id); ?>">
                        <div class="badge badge-category" style="margin-bottom:15px;">↳ Dentro de Proyecto Grande</div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Nombre de la Carpeta *</label>
                        <input type="text" name="titulo" class="form-control" required placeholder="Ej: Diseño de Personaje - Guerrero">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="¿Qué contendrá esta carpeta?"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Crear Carpeta Vacía</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>