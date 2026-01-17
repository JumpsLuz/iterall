<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Usuario.php';
require_once '../app/Models/Post.php';
require_once '../app/Models/Miniproyecto.php';
require_once '../app/Models/Proyecto.php';

$usuario_id = $_SESSION['usuario_id'];
$db = Database::getInstance();

$stmt = $db->prepare("SELECT * FROM perfiles WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

$modeloPost = new Post();
$destacados = $modeloPost->obtenerDestacados($usuario_id);

$modeloMini = new Miniproyecto();
$recientes = $modeloMini->obtenerPorUsuario($usuario_id);

$modeloProyecto = new Proyecto();
$proyectos = $modeloProyecto->obtenerPorUsuario($usuario_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header class="profile-header">
        <div class="banner-container">
            <?php if (!empty($perfil['banner_url'])): ?>
                <img src="<?php echo htmlspecialchars($perfil['banner_url']); ?>" class="banner-img">
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <img src="<?php echo $perfil['avatar_url'] ?? 'img/default-avatar.png'; ?>" class="avatar-img" alt="Avatar">
            <h1><?php echo htmlspecialchars($perfil['nombre_artistico'] ?? 'Artista'); ?></h1>
            <p class="text-muted"><?php echo htmlspecialchars($perfil['biografia'] ?? 'Sin biografía'); ?></p>
            
            <div style="margin-top: 15px;">
                <a href="completar_perfil.php" class="btn btn-secondary">Editar Perfil</a>
                <a href="procesador.php?action=logout" class="btn btn-danger">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="container">
        
        <div class="navbar">
            <a href="crear_post_rapido.php" class="btn btn-primary">+ Post Rápido</a>
            <a href="crear_mini_proyecto.php" class="btn btn-secondary">+ Nuevo Mini Proyecto</a>
            <a href="crear_proyecto.php" class="btn btn-secondary">+ Nuevo Proyecto Grande</a>
            <a href="mis_proyectos.php" class="btn btn-secondary">Ver Todos mis Proyectos</a>
        </div>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="badge badge-status" style="display:block; padding: 10px; margin-bottom: 20px;">
                ✓ Acción realizada con éxito
            </div>
        <?php endif; ?>

        <div class="section-header">
            <h2>⭐ Destacados</h2>
        </div>
        
        <?php if (empty($destacados)): ?>
            <div class="empty-state">
                No tienes posts destacados. Ve a un post y marca "Destacar" para exhibirlo aquí.
            </div>
        <?php else: ?>
            <div class="grid-gallery">
                <?php foreach ($destacados as $post): ?>
                    <div class="card" style="border-color: var(--accent);">
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                            <span class="badge badge-category"><?php echo htmlspecialchars($post['nombre_categoria'] ?? 'General'); ?></span>
                        </div>
                        <div class="card-footer">
                            <span>📂 <?php echo htmlspecialchars($post['nombre_miniproyecto'] ?? 'Sin mini proyecto'); ?></span>
                            <a href="ver_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">Ver</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="section-header">
            <h2>Actividad Reciente</h2>
        </div>

        <div class="grid-gallery">
            <?php if (empty($recientes)): ?>
                <p>No hay actividad reciente.</p>
            <?php else: ?>
                <?php foreach ($recientes as $item): ?>
                    <div class="card">
                        <div class="card-body">
                            <?php 
                            $esPostIndividual = $item['es_post_individual'] > 0;
                            
                            if ($esPostIndividual): 
                                $primer_post_id = $modeloMini->obtenerPrimerPostId($item['id']); 
                            ?>
                                <h3>📄 <?php echo htmlspecialchars($item['titulo']); ?></h3>
                                <p>Post Individual</p>
                                <a href="ver_post.php?id=<?php echo $primer_post_id; ?>" class="btn btn-secondary" style="width:100%">Ver Post</a>
                            <?php else: ?>
                                <h3>📁 <?php echo htmlspecialchars($item['titulo']); ?></h3>
                                <p>Mini Proyecto (<?php echo $item['cantidad_posts']; ?> posts)</p>
                                <a href="ver_miniproyecto.php?id=<?php echo $item['id']; ?>" class="btn btn-secondary" style="width:100%">Abrir Mini Proyecto</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="section-header">
            <h2>Proyectos Principales</h2>
        </div>
        <div class="grid-gallery">
             <?php foreach ($proyectos as $proy): ?>
                <div class="card">
                    <div class="card-body">
                         <h3><?php echo htmlspecialchars($proy['titulo']); ?></h3>
                         <span class="badge badge-status"><?php echo htmlspecialchars($proy['nombre_estado']); ?></span>
                    </div>
                    <div class="card-footer">
                        <a href="ver_proyecto.php?id=<?php echo $proy['id']; ?>" class="btn btn-primary">Gestionar</a>
                    </div>
                </div>
             <?php endforeach; ?>
        </div>

    </div>
</body>
</html>