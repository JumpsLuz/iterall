<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Post.php';
require_once '../app/Models/Iteracion.php';

if ($_SESSION['rol_id'] != 1) {
    header('Location: explorar.php');
    exit();
}

if (!isset($_GET['post_id'])) {
    header('Location: dashboard_artista.php');
    exit();
}

$postId = $_GET['post_id'];
$usuarioId = $_SESSION['usuario_id'];

$modeloPost = new Post();
$post = $modeloPost->obtenerPorId($postId, $usuarioId);

if (!$post) {
    header('Location: dashboard_artista.php?error=post_no_encontrado');
    exit();
}

$modeloIteracion = new Iteracion();
$iteraciones = $modeloIteracion->obtenerPorPost($postId);
$totalImagenes = 0;
foreach ($iteraciones as $iter) {
    $totalImagenes += count($iter['imagenes']);
}

$espacioDisponible = 50 - $totalImagenes;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Iteración | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/iteracion.css">
</head>
<body>
    <div class="container" style="max-width: 800px;">
        
        <div class="navbar">
            <a href="ver_post.php?id=<?php echo $postId; ?>" class="btn btn-secondary">← Volver al Post</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>Nueva Iteración de: "<?php echo htmlspecialchars($post['titulo']); ?>"</h2>
                <p class="text-muted" style="margin-top: 10px;">
                    Espacio disponible: <strong><?php echo $espacioDisponible; ?> imágenes</strong> de 50 totales.
                </p>

                <?php if ($totalImagenes >= 40): ?>
                    <div class="limit-warning">
                        <h4><i class="fas fa-exclamation-triangle"></i> 
                            <?php if ($espacioDisponible == 0): ?>
                                Límite alcanzado
                            <?php else: ?>
                                Cerca del límite
                            <?php endif; ?>
                        </h4>
                        <p>
                            <?php if ($espacioDisponible == 0): ?>
                                Has alcanzado el límite de 50 imágenes para este post. 
                                Elimina algunas iteraciones antiguas si deseas agregar nuevas.
                            <?php else: ?>
                                Solo puedes subir <?php echo $espacioDisponible; ?> imagen(es) más. 
                                El límite total es de 50 imágenes por post.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error" style="margin-top: 20px;">
                        <?php 
                        switch($_GET['error']) {
                            case 'sin_imagenes':
                                echo '<i class="fas fa-exclamation-triangle"></i> Debes subir al menos una imagen';
                                break;
                            case 'limite_excedido':
                                echo '<i class="fas fa-exclamation-triangle"></i> Excedes el límite de imágenes permitidas';
                                break;
                            case 'error_crear_iteracion':
                                echo '<i class="fas fa-exclamation-triangle"></i> Error al crear la iteración. Intenta nuevamente';
                                break;
                            default:
                                echo '<i class="fas fa-exclamation-triangle"></i> Error desconocido';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($espacioDisponible > 0): ?>
                <form action="procesador.php?action=crear_iteracion" method="POST" enctype="multipart/form-data" id="formIteracion">
                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                    <input type="hidden" name="espacio_disponible" value="<?php echo $espacioDisponible; ?>">

                    <div class="form-group">
                        <label class="form-label">Imágenes * (máximo <?php echo min(20, $espacioDisponible); ?> por iteración)</label>
                        <p class="form-hint" style="margin-bottom: 10px;">
                            <i class="fas fa-info-circle"></i> La primera imagen (#1) será la principal. Arrastra para reordenar.
                        </p>
                        <div class="upload-zone" id="uploadZone">
                            <p style="font-size: 2rem; margin-bottom: 10px;">📤</p>
                            <p><strong>Click para seleccionar imágenes</strong></p>
                            <p class="text-muted" style="font-size: 0.85rem;">o arrastra y suelta aquí</p>
                            <p class="text-muted" style="font-size: 0.75rem; margin-top: 10px;">
                                Máximo <?php echo min(20, $espacioDisponible); ?> imágenes | 5MB por archivo | JPG, PNG, GIF, WEBP
                            </p>
                        </div>
                        <input type="file" name="imagenes[]" id="inputImagenes" multiple accept="image/*" style="display: none;" required>
                        <input type="hidden" name="imagen_principal_index" id="imagenPrincipalIndex" value="0">
                        <input type="hidden" name="orden_imagenes" id="ordenImagenes" value="">
                    </div>

                    <div id="previewContainer" style="display: none;"></div>

                    <div class="form-group">
                        <label class="form-label">Notas de Cambio</label>
                        <textarea name="notas_cambio" class="form-control" rows="4" 
                                  placeholder="¿Qué cambiaste en esta iteración? (Ej: Ajusté los colores, mejoré la iluminación...)"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiempo Dedicado (minutos)</label>
                        <input type="number" name="tiempo_dedicado_min" class="form-control" 
                               placeholder="Ej: 120" min="1" step="1">
                        <span class="form-hint">Opcional: Tiempo aproximado que te tomó esta iteración</span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 20px;" id="btnSubmit" disabled>
                        Guardar Nueva Iteración
                    </button>
                </form>
                <?php else: ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> No puedes agregar más imágenes a este post. 
                        Límite de 50 imágenes alcanzado.
                    </div>
                    <a href="ver_post.php?id=<?php echo $postId; ?>" class="btn btn-secondary" style="width: 100%; margin-top: 20px;">
                        Volver al Post
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/iteracion-upload.js"></script>
</body>
</html>