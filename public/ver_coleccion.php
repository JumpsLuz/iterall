<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Coleccion.php';

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

$coleccion = $modeloColeccion->obtenerPorId($coleccion_id, $usuario_id);

if (!$coleccion) {
    header('Location: mis_colecciones.php?error=no_encontrado');
    exit();
}

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
    <link rel="stylesheet" href="css/ver-coleccion.css">
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
