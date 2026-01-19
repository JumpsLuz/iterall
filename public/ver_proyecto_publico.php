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

// Obtener proyecto público (sin verificar propiedad)
$proyecto = $modeloProyecto->obtenerPublicoPorId($proyecto_id);

if (!$proyecto) { 
    header('Location: explorar.php?error=proyecto_no_encontrado');
    exit();
}

// Si es el dueño, redirigir a la vista de edición
if ($proyecto['artista_id'] == $usuario_id) {
    header('Location: ver_proyecto.php?id=' . $proyecto_id);
    exit();
}

// Obtener categorías y etiquetas del proyecto
$projectCategories = CategoryTagHelper::getProjectCategories($proyecto_id);
$projectTags = CategoryTagHelper::getProjectTags($proyecto_id);

// Obtener miniproyectos (contenido público)
$miniproyectosHijos = $modeloMini->obtenerPorProyectoPadre($proyecto_id);

// Contar trabajos y iteraciones totales
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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/explorar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .project-public-header {
            background: linear-gradient(135deg, #1a1a2e, #0f0f0f);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .project-banner {
            height: 200px;
            background: #1a1a1a;
            position: relative;
        }

        .project-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
        }

        .project-banner-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        }

        .project-info-section {
            padding: 20px 30px 30px;
            display: flex;
            gap: 25px;
            align-items: flex-start;
        }

        .project-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            background: #222;
            border: 3px solid #333;
            overflow: hidden;
            flex-shrink: 0;
            margin-top: -60px;
            position: relative;
            z-index: 5;
        }

        .project-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .project-avatar-large .placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #555;
        }

        .project-details-public {
            flex: 1;
        }

        .project-details-public h1 {
            font-size: 1.8rem;
            margin: 0 0 12px 0;
            color: #fff;
        }

        .project-meta-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .project-tags-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 15px;
        }

        .project-description {
            color: var(--text-muted);
            line-height: 1.6;
            max-width: 700px;
        }

        .project-stats-row {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }

        .project-stat {
            text-align: center;
        }

        .project-stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .project-stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .artist-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #333;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.2s;
            margin-top: 20px;
        }

        .artist-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
        }

        .artist-link img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .artist-link .artist-name {
            font-weight: 600;
            color: #fff;
        }

        .artist-link .artist-role {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .content-section {
            margin-top: 30px;
        }

        .content-section h2 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .content-section h2 i {
            color: var(--primary);
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .content-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: all 0.2s;
            text-decoration: none;
        }

        .content-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: var(--shadow);
        }

        .content-card-image {
            height: 150px;
            background: #1a1a1a;
            overflow: hidden;
        }

        .content-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .content-card-image .placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #444;
        }

        .content-card-body {
            padding: 15px;
        }

        .content-card-body h3 {
            font-size: 1rem;
            margin: 0 0 8px 0;
            color: #fff;
        }

        .content-card-body p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }
    </style>
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
