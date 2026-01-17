<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';
require_once '../app/Models/Miniproyecto.php';

if (!isset($_GET['id'])) { header('Location: mis_proyectos.php'); exit(); }

$modeloProyecto = new Proyecto();
$proyecto = $modeloProyecto->obtenerPorId($_GET['id'], $_SESSION['usuario_id']);
$modeloMini = new Miniproyecto();
$miniproyectosHijos = $modeloMini->obtenerPorProyectoPadre($proyecto['id']);

if (!$proyecto) { header('Location: mis_proyectos.php?error=not_found'); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($proyecto['titulo']); ?> | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="container">
        
        <div class="breadcrumb">
            <a href="dashboard_artista.php">Dashboard</a> > 
            <a href="mis_proyectos.php">Mis Proyectos</a> > 
            <span style="color: white;"><?php echo htmlspecialchars($proyecto['titulo']); ?></span>
        </div>

        <div class="card" style="margin-bottom: 30px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h1><?php echo htmlspecialchars($proyecto['titulo']); ?></h1>
                    <div style="margin-top: 10px;">
                        <span class="badge badge-category"><?php echo htmlspecialchars($proyecto['nombre_categoria']); ?></span>
                        <span class="badge badge-status"><?php echo htmlspecialchars($proyecto['nombre_estado']); ?></span>
                        <span class="badge" style="border: 1px solid #555;"><?php echo $proyecto['es_publico'] ? 'Público' : 'Privado'; ?></span>
                    </div>
                    <?php if (!empty($proyecto['descripcion'])): ?>
                        <p style="margin-top: 15px; color: var(--text-muted); max-width: 800px;">
                            <?php echo nl2br(htmlspecialchars($proyecto['descripcion'])); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div style="display: flex; gap: 10px;">
                     <a href="editar_proyecto.php?id=<?php echo $proyecto['id']; ?>" class="btn btn-secondary">⚙️ Configuración</a>
                </div>
            </div>
        </div>

        <div class="section-header">
            <h2>Carpetas y Colecciones</h2>
            <div>
                <a href="crear_post_rapido.php?proyecto_id=<?php echo $proyecto['id']; ?>" class="btn btn-primary">+ Nueva Carpeta</a>
                <a href="crear_post.php?proyecto_id=<?php echo $proyecto['id']; ?>" class="btn btn-secondary">+ Post Suelto</a>
            </div>
        </div>

        <?php if (empty($miniproyectosHijos)): ?>
            <div class="empty-state">
                <p>Este proyecto está vacío.</p>
                <p>Crea una "Carpeta" (Mini-proyecto) para organizar personajes, escenarios o props.</p>
            </div>
        <?php else: ?>
            <div class="grid-gallery">
                <?php foreach ($miniproyectosHijos as $mini): ?>
                    <div class="card">
                        <div class="card-body">
                            <h3>📂 <?php echo htmlspecialchars($mini['titulo']); ?></h3>
                            <p><?php echo $mini['cantidad_posts']; ?> items dentro</p>
                        </div>
                        <div class="card-footer">
                            <a href="ver_miniproyecto.php?id=<?php echo $mini['id']; ?>" class="btn btn-primary" style="width: 100%;">Abrir Carpeta</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>