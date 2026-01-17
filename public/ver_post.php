<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Post.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard_artista.php');
    exit();
}

$post_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$modeloPost = new Post();
$post = $modeloPost->obtenerPorId($post_id, $usuario_id);

if (!$post) {
    echo "Post no encontrado o privado.";
    exit();
}

$iteraciones = $modeloPost->obtenerIteraciones($post_id);
$esDestacado = $modeloPost->esDestacado($post_id);
$contadorDestacados = $modeloPost->contarDestacados($usuario_id);
?>
<!DOCTYPE html>
<html>
    <body>
        
        <nav>
            <a href="dashboard_artista.php">Dashboard</a>
            <?php if ($post['miniproyecto_id']): ?>
                > <a href="ver_miniproyecto.php?id=<?php echo $post['miniproyecto_id']; ?>">Volver a Carpeta</a>
            <?php endif; ?>
            > <strong><?php echo htmlspecialchars($post['titulo']); ?></strong>
        </nav>
        
        <hr>

        <header>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1><?php echo htmlspecialchars($post['titulo']); ?></h1>
                
                <a href="procesador.php?action=toggle_destacado&id=<?php echo $post['id']; ?>">
                    <button style="<?php echo $esDestacado ? 'background: gold; color: black;' : ''; ?>">
                        <?php 
                            if ($esDestacado) {
                                echo '★ Quitar de Destacados';
                            } else {
                                if ($contadorDestacados >= 5) {
                                    echo '☆ Destacar (Límite alcanzado: ' . $contadorDestacados . '/5)';
                                } else {
                                    echo '☆ Destacar en Perfil (' . $contadorDestacados . '/5)';
                                }
                            }
                        ?>
                    </button>
                </a>
            </div>

            <p><strong>Categoría:</strong> <?php echo htmlspecialchars($post['nombre_categoria']); ?></p>
            
            <?php if (!empty($post['descripcion'])): ?>
                <p><?php echo nl2br(htmlspecialchars($post['descripcion'])); ?></p>
            <?php endif; ?>
        </header>

        <hr>

        <main>
            <div style="display: flex; justify-content: space-between;">
                <h2>📜 Historial de Versiones</h2>
                <button disabled>+ Subir Nueva Versión (Próximamente)</button>
            </div>

            <?php if (empty($iteraciones)): ?>
                <div style="border: 2px dashed #ccc; padding: 20px; text-align: center;">
                    <p>No has subido ninguna versión de este trabajo aún.</p>
                    <p><em>Sube tu primera imagen para empezar.</em></p>
                </div>
            <?php else: ?>
                
                <?php foreach ($iteraciones as $iter): ?>
                    <div style="border: 1px solid #eee; margin-bottom: 20px; padding: 10px;">
                        <h3>Versión <?php echo $iter['numero_version']; ?></h3>
                        <small>Subido el: <?php echo $iter['fecha_creacion']; ?></small>
                        
                        <?php if ($iter['tiempo_dedicado_min']): ?>
                            <p>⏱ Tiempo dedicado: <?php echo $iter['tiempo_dedicado_min']; ?> min</p>
                        <?php endif; ?>
                        
                        <p><?php echo htmlspecialchars($iter['notas_cambio']); ?></p>
                        
                        <div style="background: #ddd; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                            [Imagen]
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </main>
    </body>
</html>