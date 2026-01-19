<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Miniproyecto.php';
require_once '../app/Models/Post.php';
require_once '../app/Models/Proyecto.php';

if (!isset($_GET['id'])) {
    header('Location: explorar.php');
    exit();
}

$miniproyecto_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$modeloMini = new Miniproyecto();
$mini = $modeloMini->obtenerPublicoPorId($miniproyecto_id);

if (!$mini) {
    echo "Mini-proyecto no encontrado.";
    exit();
}

// Si el usuario es el dueño, redirigir a la vista privada
if ($mini['creador_id'] == $usuario_id) {
    header('Location: ver_miniproyecto.php?id=' . $miniproyecto_id);
    exit();
}

$modeloPost = new Post();
$posts = $modeloPost->obtenerPorMiniproyecto($miniproyecto_id);

$esPostIndividual = $modeloMini->esPostIndividual($miniproyecto_id);

// Si es un post individual, redirigir al post directamente
if ($esPostIndividual && count($posts) == 1) {
    header('Location: ver_post_publico.php?id=' . $posts[0]['id']);
    exit();
}

$proyectoPadre = null;
if ($mini['proyecto_id']) {
    $modeloProyecto = new Proyecto();
    $proyectoPadre = $modeloProyecto->obtenerPublicoPorId($mini['proyecto_id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($mini['titulo']); ?> | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .public-header {
            background: var(--bg-card);
            padding: 20px 30px;
            margin-bottom: 30px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255,255,255,0.1);
            color: var(--accent);
        }
        
        .creator-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
        }
        
        .creator-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(135deg, var(--primary), var(--accent));
        }
        
        .creator-name {
            color: #fff;
            font-weight: 500;
        }
        
        .creator-name a {
            color: inherit;
            text-decoration: none;
        }
        
        .creator-name a:hover {
            color: var(--accent);
        }
        
        .mini-info-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .mini-info-card h1 {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .mini-info-card h1 i {
            color: var(--accent);
        }
        
        .mini-description {
            color: var(--text-muted);
            line-height: 1.6;
        }
        
        .posts-section h2 {
            margin-bottom: 20px;
            color: #fff;
        }
        
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .post-card {
            background: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            display: block;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .post-card-image {
            height: 160px;
            background: rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .post-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .post-card-image i {
            font-size: 48px;
            color: var(--text-muted);
        }
        
        .post-card-body {
            padding: 20px;
        }
        
        .post-card-body h3 {
            color: #fff;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .post-card-body small {
            color: var(--text-muted);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-card);
            border-radius: 12px;
        }
        
        .empty-state p {
            color: var(--text-muted);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php 
        $active_page = 'explorar'; 
        if ($_SESSION['rol_id'] == 2) {
            include 'includes/sidebar_cliente.php';
        } else {
            include 'includes/sidebar.php';
        }
        ?>

        <main class="main-content">
            <div class="container">
                
                <!-- Header con navegación -->
                <div class="public-header">
                    <?php if ($proyectoPadre): ?>
                        <a href="ver_proyecto_publico.php?id=<?php echo $proyectoPadre['id']; ?>" class="back-btn">
                            <i class="fas fa-arrow-left"></i>
                            Volver al Proyecto
                        </a>
                    <?php else: ?>
                        <a href="explorar.php" class="back-btn">
                            <i class="fas fa-arrow-left"></i>
                            Volver a Explorar
                        </a>
                    <?php endif; ?>
                    
                    <div class="creator-info">
                        <?php if (!empty($mini['creador_foto'])): ?>
                            <img src="<?php echo htmlspecialchars($mini['creador_foto']); ?>" alt="Avatar" class="creator-avatar">
                        <?php else: ?>
                            <div class="creator-avatar" style="display: flex; align-items: center; justify-content: center; color: #fff;">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <span class="creator-name">
                            <a href="perfil_publico.php?id=<?php echo $mini['creador_id']; ?>">
                                <?php echo htmlspecialchars($mini['creador_nombre']); ?>
                            </a>
                        </span>
                    </div>
                </div>

                <!-- Información del mini proyecto -->
                <div class="mini-info-card">
                    <h1>
                        <i class="fas fa-folder"></i>
                        <?php echo htmlspecialchars($mini['titulo']); ?>
                    </h1>
                    
                    <?php if (!empty($mini['descripcion'])): ?>
                        <p class="mini-description">
                            <?php echo nl2br(htmlspecialchars($mini['descripcion'])); ?>
                        </p>
                    <?php else: ?>
                        <p class="mini-description">
                            <em>Sin descripción del mini proyecto.</em>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Posts del mini proyecto -->
                <div class="posts-section">
                    <h2>Contenido (<?php echo count($posts); ?> trabajos)</h2>

                    <?php if (empty($posts)): ?>
                        <div class="empty-state">
                            <p>Este mini proyecto aún no tiene contenido.</p>
                        </div>
                    <?php else: ?>
                        <div class="posts-grid">
                            <?php foreach ($posts as $post): ?>
                                <a href="ver_post_publico.php?id=<?php echo $post['id']; ?>" class="post-card">
                                    <div class="post-card-image">
                                        <?php if (!empty($post['portada'])): ?>
                                            <img src="<?php echo htmlspecialchars($post['portada']); ?>" alt="Portada">
                                        <?php else: ?>
                                            <i class="fas fa-image"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="post-card-body">
                                        <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                        <small>
                                            <i class="far fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($post['fecha_creacion'])); ?>
                                        </small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</body>
</html>
