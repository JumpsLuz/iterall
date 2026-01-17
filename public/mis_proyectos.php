<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';

if ($_SESSION['rol_id'] != 1) { header('Location: explorar.php'); exit(); }

$modeloProyecto = new Proyecto();
$proyectos = $modeloProyecto->obtenerPorUsuario($_SESSION['usuario_id']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Proyectos | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="container">
        <div class="navbar">
            <a href="dashboard_artista.php" class="btn btn-secondary">← Volver al Dashboard</a>
        </div>

        <div class="section-header">
            <h1>Mis Proyectos</h1>
            <a href="crear_proyecto.php" class="btn btn-primary">+ Nuevo Proyecto</a>
        </div>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="badge badge-status" style="display:block; padding: 10px; margin-bottom: 20px; background: rgba(16,185,129,0.2); color: var(--success);">
                <?php 
                switch($_GET['mensaje']) {
                    case 'proyecto_creado':
                        echo '✓ Proyecto creado exitosamente';
                        break;
                    case 'proyecto_eliminado':
                        echo '✓ Proyecto eliminado correctamente';
                        break;
                    default:
                        echo '✓ Acción completada';
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="badge badge-status" style="display:block; padding: 10px; margin-bottom: 20px; background: rgba(239,68,68,0.2); color: var(--danger);">
                <?php 
                switch($_GET['error']) {
                    case 'no_se_pudo_eliminar':
                        echo '⚠️ Error al eliminar el proyecto. Intenta nuevamente.';
                        if (isset($_GET['detalle'])) {
                            echo '<br><small>' . htmlspecialchars($_GET['detalle']) . '</small>';
                        }
                        break;
                    case 'proyecto_no_encontrado':
                        echo '⚠️ El proyecto no existe o no tienes permiso para acceder.';
                        break;
                    case 'not_found':
                        echo '⚠️ Proyecto no encontrado.';
                        break;
                    default:
                        echo '⚠️ Ocurrió un error.';
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($proyectos)): ?>
            <div class="empty-state">
                <h3>No tienes proyectos creados aún.</h3>
                <p>Los proyectos sirven para agrupar múltiples mini proyectos y trabajos (ej: Un Videojuego, Un Cómic).</p>
                <br>
                <a href="crear_proyecto.php" class="btn btn-primary">Crear mi primer proyecto</a>
            </div>
        <?php else: ?>
            <div class="grid-gallery">
                <?php foreach ($proyectos as $proyecto): ?>
                    <div class="card">
                        <div style="height: 120px; background: #333; display:flex; align-items:center; justify-content:center; color:#555;">
                            [Portada Proyecto]
                        </div>
                        
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                            <span class="badge badge-category"><?php echo htmlspecialchars($proyecto['nombre_categoria'] ?? 'General'); ?></span>
                            <span class="badge badge-status"><?php echo htmlspecialchars($proyecto['nombre_estado'] ?? 'Activo'); ?></span>
                            <hr style="margin: 10px 0; border-color: #333;">
                            <p><?php echo nl2br(htmlspecialchars(substr($proyecto['descripcion'], 0, 100))); ?><?php echo strlen($proyecto['descripcion']) > 100 ? '...' : ''; ?></p>
                        </div>
                        
                        <div class="card-footer">
                            <small>Actualizado: <?php echo date('d/m/Y', strtotime($proyecto['fecha_actualizacion'])); ?></small>
                            <a href="ver_proyecto.php?id=<?php echo $proyecto['id']; ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>