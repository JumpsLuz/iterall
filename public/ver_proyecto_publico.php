<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';
require_once '../app/Models/Miniproyecto.php';
require_once '../app/Models/Post.php';
require_once '../app/Helpers/CategoryTagHelper.php';

if (!isset($_GET['id'])) { 
    header('Location: explorar.php'); 
    exit(); 
}

$proyecto_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$rol_id = $_SESSION['rol_id'];

$modeloProyecto = new Proyecto();
$modeloMini = new Miniproyecto();
$modeloPost = new Post();

$proyecto = $modeloProyecto->obtenerPublicoPorId($proyecto_id);

if (!$proyecto) { 
    header('Location: explorar.php?error=proyecto_no_encontrado');
    exit();
}

if ($proyecto['artista_id'] == $usuario_id) {
    header('Location: ver_proyecto.php?id=' . $proyecto_id);
    exit();
}

$projectCategories = CategoryTagHelper::getProjectCategories($proyecto_id);
$projectTags = CategoryTagHelper::getProjectTags($proyecto_id);

$miniproyectosHijos = $modeloMini->obtenerPorProyectoPadre($proyecto_id);

$db = Database::getInstance();
$stmt = $db->prepare("SELECT COUNT(*) as total FROM posts WHERE proyecto_id = ?");
$stmt->execute([$proyecto_id]);
$totalPosts = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM iteraciones i
        JOIN posts p ON i.post_id = p.id
        WHERE p.proyecto_id = ?");
$stmt->execute([$proyecto_id]);
$totalIteraciones = $stmt->fetch()['total'];

$esArtista = ($rol_id == 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($proyecto['titulo']); ?> | ITERALL</title>
    <?php include 'includes/favicon.php'; ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/explorar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/proyecto-publico.css">
</head>
<body>
    <div class="app-layout">
        <?php 
        $active_page = 'explorar'; 
        if ($esArtista) {
            include 'includes/sidebar.php';
        } else {
            include 'includes/sidebar_cliente.php';
        }
        ?>

        <main class="main-content">
            <div class="container">
                
                <!-- Breadcrumb -->
                <div class="breadcrumb" style="margin-bottom: 20px;">
                    <a href="explorar.php">Explorar</a> > 
                    <a href="explorar.php?tipo=proyectos">Proyectos</a> > 
                    <span style="color: white;"><?php echo htmlspecialchars($proyecto['titulo']); ?></span>
                </div>

                <!-- Project Header -->
                <div class="project-public-header">
                    <div class="project-banner">
                        <?php if (!empty($proyecto['banner_url'])): ?>
                            <img src="<?php echo htmlspecialchars($proyecto['banner_url']); ?>" 
                                 alt="Banner de <?php echo htmlspecialchars($proyecto['titulo']); ?>">
                        <?php endif; ?>
                        <div class="project-banner-overlay"></div>
                    </div>
                    
                    <div class="project-info-section">
                        <div class="project-avatar-large">
                            <?php if (!empty($proyecto['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($proyecto['avatar_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($proyecto['titulo']); ?>">
                            <?php else: ?>
                                <div class="placeholder"><i class="fas fa-folder"></i></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="project-details-public">
                            <h1><?php echo htmlspecialchars($proyecto['titulo']); ?></h1>
                            
                            <div class="project-meta-badges">
                                <?php if (!empty($projectCategories)): ?>
                                    <?php foreach ($projectCategories as $cat): ?>
                                        <span class="badge badge-category"><?php echo htmlspecialchars($cat['nombre_categoria']); ?></span>
                                    <?php endforeach; ?>
                                <?php elseif (!empty($proyecto['nombre_categoria'])): ?>
                                    <span class="badge badge-category"><?php echo htmlspecialchars($proyecto['nombre_categoria']); ?></span>
                                <?php endif; ?>
                                <span class="badge badge-status"><?php echo htmlspecialchars($proyecto['nombre_estado']); ?></span>
                            </div>
                            
                            <?php if (!empty($projectTags)): ?>
                            <div class="project-tags-badges">
                                <?php foreach ($projectTags as $tag): ?>
                                    <?php if ($tag['nombre_etiqueta'] !== '#@#_no_mini_proyecto_#@#' && strtolower($tag['nombre_etiqueta']) !== 'destacado'): ?>
                                        <span class="badge badge-tag">#<?php echo htmlspecialchars($tag['nombre_etiqueta']); ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($proyecto['descripcion'])): ?>
                                <p class="project-description"><?php echo nl2br(htmlspecialchars($proyecto['descripcion'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="project-stats-row">
                                <div class="project-stat">
                                    <div class="project-stat-number"><?php echo $totalPosts; ?></div>
                                    <div class="project-stat-label">Trabajos</div>
                                </div>
                                <div class="project-stat">
                                    <div class="project-stat-number"><?php echo $totalIteraciones; ?></div>
                                    <div class="project-stat-label">Iteraciones</div>
                                </div>
                            </div>
                            
                            <a href="perfil_publico.php?id=<?php echo $proyecto['artista_id']; ?>" class="artist-link">
                                <?php if (!empty($proyecto['artista_avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($proyecto['artista_avatar']); ?>" alt="">
                                <?php else: ?>
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="artist-name"><?php echo htmlspecialchars($proyecto['nombre_artistico']); ?></div>
                                    <div class="artist-role">Ver perfil del artista</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="content-section">
                    <h2><i class="fas fa-layer-group"></i> Contenido del Proyecto</h2>
                    
                    <?php if (empty($miniproyectosHijos)): ?>
                        <div class="empty-state">
                            <p>Este proyecto aún no tiene contenido público.</p>
                        </div>
                    <?php else: ?>
                        <div class="content-grid">
                            <?php foreach ($miniproyectosHijos as $mini): ?>
                                <?php 
                                $esPostIndividual = $mini['es_post_individual'] > 0;
                                
                                if ($esPostIndividual): 
                                    $primer_post_id = $modeloMini->obtenerPrimerPostId($mini['id']); 
                                ?>
                                    <a href="ver_post_publico.php?id=<?php echo $primer_post_id; ?>" class="content-card">
                                        <?php if (!empty($mini['miniatura'])): ?>
                                            <div class="content-card-image">
                                                <img src="<?php echo htmlspecialchars($mini['miniatura']); ?>" alt="">
                                            </div>
                                        <?php else: ?>
                                            <div class="content-card-image">
                                                <div class="placeholder"><i class="fas fa-image"></i></div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="content-card-body">
                                            <h3><?php echo htmlspecialchars($mini['titulo']); ?></h3>
                                            <p>Post Individual</p>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <a href="ver_miniproyecto_publico.php?id=<?php echo $mini['id']; ?>" class="content-card">
                                        <?php if (!empty($mini['miniatura'])): ?>
                                            <div class="content-card-image">
                                                <img src="<?php echo htmlspecialchars($mini['miniatura']); ?>" alt="">
                                            </div>
                                        <?php else: ?>
                                            <div class="content-card-image">
                                                <div class="placeholder"><i class="fas fa-folder"></i></div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="content-card-body">
                                            <h3><?php echo htmlspecialchars($mini['titulo']); ?></h3>
                                            <p><?php echo $mini['cantidad_posts']; ?> trabajos</p>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</body>
</html>
