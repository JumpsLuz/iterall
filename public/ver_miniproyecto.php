<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Miniproyecto.php';
require_once '../app/Models/Post.php';
require_once '../app/Models/Proyecto.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard_artista.php');
    exit();
}

$miniproyecto_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$modeloMini = new Miniproyecto();
$mini = $modeloMini->obtenerPorId($miniproyecto_id, $usuario_id);

if (!$mini) {
    echo "Mini-proyecto no encontrado o no tienes permiso.";
    exit();
}

$modeloPost = new Post();
$posts = $modeloPost->obtenerPorMiniproyecto($miniproyecto_id);

$esPostIndividual = $modeloMini->esPostIndividual($miniproyecto_id);

if ($esPostIndividual && count($posts) == 1) {
    header('Location: ver_post.php?id=' . $posts[0]['id']);
    exit();
}

$proyectoPadre = null;
if ($mini['proyecto_id']) {
    $modeloProyecto = new Proyecto();
    $proyectoPadre = $modeloProyecto->obtenerPorId($mini['proyecto_id'], $usuario_id);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($mini['titulo']); ?> | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-layout">
        <?php $active_page = 'dashboard'; include 'includes/sidebar.php'; ?>

        <main class="main-content">
    <div class="container">
        
        <div class="breadcrumb">
            <a href="dashboard_artista.php">Dashboard</a> 
            
            <?php if ($proyectoPadre): ?>
                > <a href="ver_proyecto.php?id=<?php echo $proyectoPadre['id']; ?>">
                    <?php echo htmlspecialchars($proyectoPadre['titulo']); ?>
                  </a>
            <?php else: ?>
                > <span>Mini Proyecto Independiente</span>
            <?php endif; ?>
            
            > <strong><?php echo htmlspecialchars($mini['titulo']); ?></strong>
        </div>

        <div class="card" style="margin-bottom: 30px; padding: 20px;">
            <h1><i class="fas fa-folder"></i> <?php echo htmlspecialchars($mini['titulo']); ?></h1>
            
            <?php if (!empty($mini['descripcion'])): ?>
                <p style="margin-top: 15px; color: var(--text-muted);">
                    <?php echo nl2br(htmlspecialchars($mini['descripcion'])); ?>
                </p>
            <?php else: ?>
                <p style="margin-top: 15px; color: var(--text-muted);">
                    <em>Sin descripción del mini proyecto.</em>
                </p>
            <?php endif; ?>
        </div>

        <div class="section-header">
            <h2>Contenido (<?php echo count($posts); ?> items)</h2>
            <a href="crear_post.php?miniproyecto_id=<?php echo $mini['id']; ?>" class="btn btn-primary">+ Agregar Nuevo Post</a>
        </div>

        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <p>Este mini proyecto está vacío.</p>
                <p>Agrega tu primer post para comenzar a documentar tu proceso creativo.</p>
            </div>
        <?php else: ?>
            <div class="grid-gallery">
                <?php foreach ($posts as $post): ?>
                    <div class="card">
                        <?php if (!empty($post['portada'])): ?>
                            <div class="card-image">
                                <img src="<?php echo htmlspecialchars($post['portada']); ?>" alt="Portada del post" style="width: 100%; height: 120px; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                            <small class="text-muted">Subido el: <?php echo date('d/m/Y', strtotime($post['fecha_creacion'])); ?></small>
                        </div>
                        <div class="card-footer">
                            <a href="ver_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary" style="width: 100%;">Ver Trabajo y Versiones</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
        </main>
    </div>
</body>
</html>