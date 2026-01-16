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

$proyectoPadre = null;
if ($mini['proyecto_id']) {
    $modeloProyecto = new Proyecto();
    $proyectoPadre = $modeloProyecto->obtenerPorId($mini['proyecto_id'], $usuario_id);
}
?>
<!DOCTYPE html>
<html>
    <body>
        
        <nav>
            <a href="dashboard_artista.php">Dashboard</a> 
            
            <?php if ($proyectoPadre): ?>
                > <a href="ver_proyecto.php?id=<?php echo $proyectoPadre['id']; ?>">
                    <?php echo htmlspecialchars($proyectoPadre['titulo']); ?>
                  </a>
            <?php else: ?>
                > <span>Colección Suelta</span>
            <?php endif; ?>
            
            > <strong><?php echo htmlspecialchars($mini['titulo']); ?></strong>
        </nav>

        <hr>

        <header>
            <h1>[] <?php echo htmlspecialchars($mini['titulo']); ?></h1>
            
            <?php if (!empty($mini['descripcion'])): ?>
                <p><?php echo nl2br(htmlspecialchars($mini['descripcion'])); ?></p>
            <?php else: ?>
                <p><em>Sin descripción de la colección.</em></p>
            <?php endif; ?>

            <button>Editar Detalles de la Carpeta</button> 
        </header>

        <hr>

        <main>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Contenido (<?php echo count($posts); ?> items)</h2>
                
                <a href="crear_post.php?miniproyecto_id=<?php echo $mini['id']; ?>">
                    <button>+ Agregar Nuevo Post a esta Colección</button>
                </a>
            </div>

            <?php if (empty($posts)): ?>
                <p>Esta carpeta está vacía.</p>
            <?php else: ?>
                
                <ul>
                    <?php foreach ($posts as $post): ?>
                        <li style="margin-bottom: 15px;">
                            <strong><?php echo htmlspecialchars($post['titulo']); ?></strong>
                            <br>
                            <small>Subido el: <?php echo $post['fecha_creacion']; ?></small>
                            <br>
                            <a href="ver_post.php?id=<?php echo $post['id']; ?>">Ver Trabajo y Versiones</a>
                        </li>
                    <?php endforeach; ?>
                </ul>

            <?php endif; ?>
        </main>

    </body>
</html>