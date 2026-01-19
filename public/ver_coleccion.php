<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Coleccion.php';

// Solo clientes/reclutadores pueden acceder
if ($_SESSION['rol_id'] != 2) {
    header('Location: dashboard_artista.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: mis_colecciones.php');
    exit();
}

$coleccion_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$modeloColeccion = new Coleccion();

// Obtener la colección (verificando propiedad)
$coleccion = $modeloColeccion->obtenerPorId($coleccion_id, $usuario_id);

if (!$coleccion) {
    header('Location: mis_colecciones.php?error=no_encontrado');
    exit();
}

// Obtener posts de la colección
$posts = $modeloColeccion->obtenerPosts($coleccion_id, $usuario_id);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($coleccion['nombre']); ?> | Mis Colecciones</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/explorar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #0a0a0a;
            color: #e0e0e0;
        }
        
        .main-content {
            background: #0a0a0a;
            padding: 30px;
        }
        
        .coleccion-header {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 227, 194, 0.05));
            border: 1px solid #222;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .coleccion-header h1 {
            margin: 0 0 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .coleccion-header h1 i {
            color: var(--primary);
        }

        .coleccion-descripcion {
            color: var(--text-muted);
            margin-bottom: 15px;
        }

        .coleccion-meta {
            display: flex;
            gap: 20px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .coleccion-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .coleccion-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .breadcrumb {
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Grid de posts - estilo similar a explorar pero más compacto */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .post-card {
            background: #141414;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #222;
            transition: all 0.3s;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .post-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .post-card-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .post-imagen {
            position: relative;
            height: 140px;
            overflow: hidden;
            background: #0a0a0a;
        }

        .post-imagen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .post-card:hover .post-imagen img {
            transform: scale(1.05);
        }

        .sin-imagen {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1a1a1a;
            color: #333;
            font-size: 2rem;
        }

        .post-info {
            padding: 12px 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .post-info h3 {
            margin: 0 0 8px;
            font-size: 0.95rem;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .post-artista {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #aaa;
            margin-bottom: 8px;
        }

        .post-artista img {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            object-fit: cover;
        }

        .post-artista .placeholder-avatar {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            color: #fff;
        }
        
        .post-artista span {
            color: #ccc;
        }

        /* Botón quitar de colección */
        .btn-quitar {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.75);
            border: none;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.2s;
            z-index: 10;
            font-size: 0.8rem;
        }

        .post-card:hover .btn-quitar {
            opacity: 1;
        }

        .btn-quitar:hover {
            background: var(--danger);
        }

        .fecha-agregado {
            font-size: 0.7rem;
            color: #666;
            margin-top: auto;
            padding-top: 8px;
            border-top: 1px solid #222;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-muted);
            background: #141414;
            border-radius: 12px;
            border: 1px solid #222;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #444;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            color: var(--text-main);
            font-size: 1.2rem;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .posts-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .coleccion-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php 
        $active_page = 'mis_colecciones';
        include 'includes/sidebar_cliente.php';
        ?>
        
        <main class="main-content">
            <div class="breadcrumb">
                <a href="mis_colecciones.php"><i class="fas fa-folder"></i> Mis Colecciones</a>
                <span> / </span>
                <span><?php echo htmlspecialchars($coleccion['nombre']); ?></span>
            </div>

            <div class="coleccion-header">
                <h1>
                    <i class="fas fa-folder-open"></i>
                    <?php echo htmlspecialchars($coleccion['nombre']); ?>
                </h1>
                
                <?php if (!empty($coleccion['descripcion'])): ?>
                    <p class="coleccion-descripcion"><?php echo htmlspecialchars($coleccion['descripcion']); ?></p>
                <?php endif; ?>
                
                <div class="coleccion-meta">
                    <span><i class="fas fa-image"></i> <?php echo count($posts); ?> post<?php echo count($posts) != 1 ? 's' : ''; ?></span>
                    <span><i class="fas fa-calendar"></i> Creada <?php echo date('d/m/Y', strtotime($coleccion['fecha_creacion'])); ?></span>
                </div>
            </div>

            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Esta colección está vacía</h3>
                    <p>Explora trabajos y guárdalos aquí para encontrarlos fácilmente</p>
                    <a href="explorar.php" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-compass"></i> Explorar trabajos
                    </a>
                </div>
            <?php else: ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card" id="post-<?php echo $post['id']; ?>">
                            <button class="btn-quitar" 
                                    onclick="quitarDeColeccion(<?php echo $coleccion_id; ?>, <?php echo $post['id']; ?>)"
                                    title="Quitar de colección">
                                <i class="fas fa-trash"></i>
                            </button>
                            
                            <a href="ver_post_publico.php?id=<?php echo $post['id']; ?>" class="post-card-link">
                                <div class="post-imagen">
                                    <?php if (!empty($post['portada'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['portada']); ?>" 
                                             alt="<?php echo htmlspecialchars($post['titulo']); ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="sin-imagen">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="post-info">
                                    <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                    
                                    <div class="post-artista">
                                        <?php if (!empty($post['artista_avatar'])): ?>
                                            <img src="<?php echo htmlspecialchars($post['artista_avatar']); ?>" alt="">
                                        <?php else: ?>
                                            <div class="placeholder-avatar"><i class="fas fa-user"></i></div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($post['nombre_artistico'] ?? 'Artista'); ?></span>
                                    </div>
                                    
                                    <div class="fecha-agregado">
                                        <i class="fas fa-clock"></i> 
                                        Guardado <?php echo date('d/m/Y', strtotime($post['fecha_agregado'])); ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function quitarDeColeccion(coleccionId, postId) {
            if (!confirm('¿Quitar este post de la colección?')) return;
            
            fetch('procesador.php?action=toggle_coleccion', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `coleccion_id=${coleccionId}&post_id=${postId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Animación de salida
                    const card = document.getElementById('post-' + postId);
                    card.style.transform = 'scale(0.9)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        // Si no quedan más posts, recargar
                        if (document.querySelectorAll('.post-card').length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    alert(data.error || 'Error al quitar post');
                }
            });
        }
    </script>
</body>
</html>
