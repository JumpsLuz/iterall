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
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Perfil Artista</title>
        <link rel="stylesheet" href="css/dashboard.css">
    </head>
    <body>

        <header class="profile-header">
            <div class="banner-container" style="background-color: #ccc; height: 200px; overflow: hidden;">
                <?php if (!empty($perfil['banner_url'])): ?>
                    <img src="<?php echo htmlspecialchars($perfil['banner_url']); ?>" style="width: 100%;">
                <?php else: ?>
                    <div style="padding: 20px;">Sin Banner</div>
                <?php endif; ?>
            </div>
            
            <div class="profile-info">
                <img src="<?php echo $perfil['avatar_url'] ?? 'img/default-avatar.png'; ?>" alt="Avatar" width="100">
                <h1><?php echo htmlspecialchars($perfil['nombre_artistico'] ?? 'Artista'); ?></h1>
                <p><?php echo htmlspecialchars($perfil['biografia'] ?? 'Sin biografía'); ?></p>
                
                <a href="completar_perfil.php">Editar Perfil</a> | 
                <a href="procesador.php?action=logout">Cerrar Sesión</a>
            </div>
        </header>

        <hr>

        <nav>
            <strong>Acciones:</strong>
            <a href="crear_post_rapido.php">[+ Post Rápido]</a>
            <a href="crear_proyecto.php">[+ Crear Proyecto Grande]</a>
        </nav>

        <hr>

        <section>
            <h2>Destacados</h2>
            <?php if (empty($destacados)): ?>
                <p>No tienes posts destacados. Ve a un post y marca "Destacar" para que aparezca aquí.</p>
            <?php else: ?>
                <div style="display: flex; gap: 10px;">
                    <?php foreach ($destacados as $post): ?>
                        <div style="border: 1px solid #000; padding: 10px;">
                            <a href="ver_post.php?id=<?php echo $post['id']; ?>">
                                <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                            </a>
                            <small>En: <?php echo htmlspecialchars($post['nombre_miniproyecto']); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <hr>

        <section>
            <h2>Recientes</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <?php if (empty($recientes)): ?>
                    <p>No hay actividad reciente.</p>
                <?php else: ?>
                    <?php foreach ($recientes as $item): ?>
                        <div style="border: 1px solid #ccc; padding: 10px; width: 200px;">
                            
                            <?php if ($item['cantidad_posts'] == 1): ?>
                                <strong>{} <?php echo htmlspecialchars($item['titulo']); ?></strong>
                                <br>
                                <small>Post Individual</small>
                                <br>
                                <a href="ver_miniproyecto.php?id=<?php echo $item['id']; ?>">Ver Post</a>

                            <?php else: ?>
                                <strong>[] <?php echo htmlspecialchars($item['titulo']); ?></strong>
                                <br>
                                <small>Colección (<?php echo $item['cantidad_posts']; ?> posts)</small>
                                <br>
                                <a href="ver_miniproyecto.php?id=<?php echo $item['id']; ?>">Abrir Carpeta</a>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <hr>

        <section>
            <h2>Proyectos Principales</h2>
            <?php if (empty($proyectos)): ?>
                <p>No tienes proyectos grandes activos.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($proyectos as $proy): ?>
                        <li>
                            <a href="ver_proyecto.php?id=<?php echo $proy['id']; ?>">
                                <?php echo htmlspecialchars($proy['titulo']); ?>
                            </a> 
                            (<?php echo htmlspecialchars($proy['nombre_estado']); ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a href="mis_proyectos.php">Ver todos mis proyectos</a>
        </section>

    </body>
</html>