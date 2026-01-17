<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Post.php';

if (!isset($_GET['id'])) { header('Location: dashboard_artista.php'); exit(); }

$post_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$modeloPost = new Post();
$post = $modeloPost->obtenerPorId($post_id, $usuario_id);

if (!$post) { die("Post no encontrado."); }

$iteraciones = $modeloPost->obtenerIteraciones($post_id);
$esDestacado = $modeloPost->esDestacado($post_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['titulo']); ?> | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="container">
        
        <div class="breadcrumb">
            <a href="dashboard_artista.php">Dashboard</a> > 
            <?php if ($post['miniproyecto_id']): ?>
                <a href="ver_miniproyecto.php?id=<?php echo $post['miniproyecto_id']; ?>">Volver a Mini Proyecto</a> >
            <?php endif; ?>
            <span><?php echo htmlspecialchars($post['titulo']); ?></span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1><?php echo htmlspecialchars($post['titulo']); ?></h1>
                <span class="badge badge-category"><?php echo htmlspecialchars($post['nombre_categoria']); ?></span>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <a href="procesador.php?action=toggle_destacado&id=<?php echo $post['id']; ?>" class="btn <?php echo $esDestacado ? 'btn-gold' : 'btn-secondary'; ?>">
                    <?php echo $esDestacado ? '★ Destacado' : '☆ Destacar'; ?>
                </a>
                
                <form action="procesador.php?action=eliminar_post" method="POST" onsubmit="return confirm('¿Eliminar?');" style="display:inline;">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" class="btn btn-danger">🗑️ Eliminar</button>
                </form>
            </div>
        </div>

        <hr>

        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 30px;">
            
            <div>
                <div class="section-header" style="margin-top:0;">
                    <h2>Historial de Versiones</h2>
                    <button class="btn btn-secondary" disabled>+ Subir Nueva Versión</button>
                </div>

                <?php if (empty($iteraciones)): ?>
                    <div class="empty-state">
                        <p>No has subido ninguna versión aún.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($iteraciones as $iter): ?>
                            <div class="timeline-item">
                                <h3 style="color: var(--primary);">Versión <?php echo $iter['numero_version']; ?></h3>
                                <small class="text-muted"><?php echo date('d M Y, H:i', strtotime($iter['fecha_creacion'])); ?></small>
                                
                                <div class="timeline-img-placeholder">
                                    [Imagen Versión <?php echo $iter['numero_version']; ?>]
                                </div>
                                
                                <div style="background: var(--bg-card); padding: 15px; margin-top: 10px; border-radius: var(--radius);">
                                    <strong>Notas:</strong>
                                    <p><?php echo htmlspecialchars($iter['notas_cambio']); ?></p>
                                    <?php if ($iter['tiempo_dedicado_min']): ?>
                                        <small>⏱ Tiempo: <?php echo $iter['tiempo_dedicado_min']; ?> min</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <aside>
                <div class="card">
                    <div class="card-body">
                        <h4>Detalles</h4>
                        <p style="margin-top:10px;">
                            <strong>Descripción:</strong><br>
                            <?php echo !empty($post['descripcion_miniproyecto']) ? nl2br(htmlspecialchars($post['descripcion_miniproyecto'])) : 'Sin descripción.'; ?>
                        </p>
                        <hr style="border-color:#333;">
                        <small class="text-muted">Total versiones: <?php echo count($iteraciones); ?></small>
                    </div>
                </div>
            </aside>

        </div>
    </div>
</body>
</html>