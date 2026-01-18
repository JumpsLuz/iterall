<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Post.php';
require_once '../app/Models/Iteracion.php';

if (!isset($_GET['id'])) { 
    header('Location: dashboard_artista.php'); 
    exit(); 
}

$post_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$modeloPost = new Post();
$modeloIteracion = new Iteracion();

$post = $modeloPost->obtenerPorId($post_id, $usuario_id);
if (!$post) { 
    die("Post no encontrado."); 
}

$iteraciones = $modeloIteracion->obtenerPorPost($post_id);
$esDestacado = $modeloPost->esDestacado($post_id);

$totalImagenes = 0;
foreach ($iteraciones as $iter) {
    $totalImagenes += count($iter['imagenes']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['titulo']); ?> | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .iteration-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
        }
        .iteration-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        .version-badge {
            background: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        /* Grid adaptativo */
        .gallery-grid {
            display: grid;
            gap: 15px;
            margin: 15px 0;
        }
        
        /* Grid 3x3 para 9 o menos imágenes */
        .gallery-grid.small {
            grid-template-columns: repeat(3, 1fr);
        }
        
        /* Grid 5x4 para 10 o más imágenes */
        .gallery-grid.large {
            grid-template-columns: repeat(5, 1fr);
        }
        
        .gallery-item {
            position: relative;
            border-radius: var(--radius);
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
            aspect-ratio: 1;
        }
        .gallery-item:hover {
            transform: scale(1.05);
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        /* Indicador visual de imagen principal */
        .gallery-item.principal {
            border: 3px solid var(--accent);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.3);
        }
        
        .principal-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: var(--accent);
            color: black;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Modal para visualizar imagen completa */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
        }
        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
            margin-top: 50px;
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .image-counter {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .image-counter.warning {
            border-color: var(--accent);
            background: rgba(245, 158, 11, 0.1);
        }
        
        @media (max-width: 768px) {
            .gallery-grid.small {
                grid-template-columns: repeat(2, 1fr);
            }
            .gallery-grid.large {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="breadcrumb">
            <a href="dashboard_artista.php">Dashboard</a> > 
            <?php if ($post['miniproyecto_id']): ?>
                <a href="ver_miniproyecto.php?id=<?php echo $post['miniproyecto_id']; ?>">Volver a Mini Proyecto</a> >
            <?php endif; ?>
            <span><?php echo htmlspecialchars($post['titulo']); ?></span>
        </div>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <?php 
                switch($_GET['mensaje']) {
                    case 'iteracion_creada':
                        echo '<i class="fas fa-check"></i> Nueva iteración creada exitosamente';
                        break;
                    case 'iteracion_eliminada':
                        echo '<i class="fas fa-check"></i> Iteración eliminada correctamente';
                        break;
                    case 'iteracion_actualizada':
                        echo '<i class="fas fa-check"></i> Iteración actualizada';
                        break;
                    case 'principal_actualizada':
                        echo '<i class="fas fa-check"></i> Imagen principal actualizada';
                        break;
                    default:
                        echo '<i class="fas fa-check"></i> Acción completada';
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> Ocurrió un error. Intenta nuevamente.
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <?php if (!empty($post['portada'])): ?>
                    <div style="margin-bottom: 15px;">
                        <img src="<?php echo htmlspecialchars($post['portada']); ?>" alt="Portada del post" style="width: 100%; max-width: 300px; height: 150px; object-fit: cover; border-radius: var(--radius);">
                    </div>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($post['titulo']); ?></h1>
                <span class="badge badge-category"><?php echo htmlspecialchars($post['nombre_categoria']); ?></span>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <a href="procesador.php?action=toggle_destacado&id=<?php echo $post['id']; ?>" 
                   class="btn <?php echo $esDestacado ? 'btn-gold' : 'btn-secondary'; ?>">
                    <?php echo $esDestacado ? '★ Destacado' : '☆ Destacar'; ?>
                </a>
                
                <form action="procesador.php?action=eliminar_post" method="POST" 
                      onsubmit="return confirm('¿Eliminar este post y todas sus iteraciones?');" 
                      style="display:inline;">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Eliminar Post</button>
                </form>
            </div>
        </div>

        <hr>

        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 30px;">
            
            <div>
                <!-- Contador de imágenes -->
                <div class="image-counter <?php echo $totalImagenes >= 40 ? 'warning' : ''; ?>">
                    <div>
                        <strong>Total de imágenes:</strong> <?php echo $totalImagenes; ?> / 50
                    </div>
                    <?php if ($totalImagenes >= 40): ?>
                        <div style="color: var(--accent); font-weight: bold;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <?php if ($totalImagenes >= 50): ?>
                                Límite alcanzado
                            <?php else: ?>
                                Quedan <?php echo 50 - $totalImagenes; ?> espacios
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="section-header" style="margin-top:0;">
                    <h2>Historial de Iteraciones (<?php echo count($iteraciones); ?>)</h2>
                    <?php if ($totalImagenes < 50): ?>
                        <a href="crear_iteracion.php?post_id=<?php echo $post_id; ?>" class="btn btn-primary">+ Nueva Iteración</a>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled title="Límite de imágenes alcanzado">Límite alcanzado</button>
                    <?php endif; ?>
                </div>

                <?php if (empty($iteraciones)): ?>
                    <div class="empty-state">
                        <p>Aún no has subido ninguna iteración de este trabajo.</p>
                        <p>Comienza documentando tu proceso creativo subiendo la primera iteración.</p>
                        <br>
                        <a href="crear_iteracion.php?post_id=<?php echo $post_id; ?>" class="btn btn-primary">Subir Primera Iteración</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($iteraciones as $iter): ?>
                        <div class="iteration-card">
                            <div class="iteration-header">
                                <div>
                                    <span class="version-badge">Iteración <?php echo $iter['numero_version']; ?></span>
                                    <small class="text-muted" style="margin-left: 15px;">
                                        <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($iter['fecha_creacion'])); ?>
                                    </small>
                                    <?php if ($iter['tiempo_dedicado_min']): ?>
                                        <small class="text-muted" style="margin-left: 10px;">
                                            <i class="fas fa-clock"></i> <?php echo $iter['tiempo_dedicado_min']; ?> min
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <form action="procesador.php?action=eliminar_iteracion" method="POST" 
                                      onsubmit="return confirm('¿Eliminar esta iteración?');" 
                                      style="display:inline;">
                                    <input type="hidden" name="iteracion_id" value="<?php echo $iter['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>

                            <?php if (!empty($iter['imagenes'])): ?>
                                <?php 
                                $numImagenes = count($iter['imagenes']);
                                $gridClass = $numImagenes <= 9 ? 'small' : 'large';
                                ?>
                                <div class="gallery-grid <?php echo $gridClass; ?>">
                                    <?php foreach ($iter['imagenes'] as $imagen): ?>
                                        <div class="gallery-item <?php echo $imagen['es_principal'] ? 'principal' : ''; ?>" 
                                             onclick="openModal('<?php echo htmlspecialchars($imagen['url_archivo']); ?>')">
                                            <img src="<?php echo htmlspecialchars($imagen['url_archivo']); ?>" 
                                                 alt="Imagen iteración <?php echo $iter['numero_version']; ?>" 
                                                 loading="lazy">
                                            <?php if ($imagen['es_principal']): ?>
                                                <div class="principal-badge">
                                                    ★ PRINCIPAL
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Sin imágenes en esta iteración</p>
                            <?php endif; ?>

                            <?php if (!empty($iter['notas_cambio'])): ?>
                                <div style="background: rgba(59, 130, 246, 0.1); padding: 12px; border-radius: var(--radius); margin-top: 15px;">
                                    <strong style="color: var(--primary);"><i class="fas fa-sticky-note"></i> Notas:</strong>
                                    <p style="margin-top: 5px; color: var(--text-main);">
                                        <?php echo nl2br(htmlspecialchars($iter['notas_cambio'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <aside>
                <div class="card">
                    <div class="card-body">
                        <h4>Información del Trabajo</h4>
                        
                        <div style="margin-top: 20px;">
                            <p><strong>Descripción:</strong></p>
                            <p class="text-muted" style="margin-top: 5px;">
                                <?php echo !empty($post['descripcion_miniproyecto']) 
                                    ? nl2br(htmlspecialchars($post['descripcion_miniproyecto'])) 
                                    : 'Sin descripción.'; ?>
                            </p>
                        </div>

                        <hr style="border-color:#333; margin: 20px 0;">

                        <div style="display: grid; gap: 10px;">
                            <div>
                                <small class="text-muted">Total iteraciones:</small>
                                <p style="font-weight: bold; font-size: 1.2rem; color: var(--primary);">
                                    <?php echo count($iteraciones); ?>
                                </p>
                            </div>

                            <div>
                                <small class="text-muted">Total imágenes:</small>
                                <p style="font-weight: bold; font-size: 1.2rem; color: var(--accent);">
                                    <?php echo $totalImagenes; ?> / 50
                                </p>
                            </div>

                            <?php 
                            $tiempoTotal = 0;
                            foreach ($iteraciones as $iter) {
                                $tiempoTotal += $iter['tiempo_dedicado_min'] ?? 0;
                            }
                            if ($tiempoTotal > 0):
                            ?>
                            <div>
                                <small class="text-muted">Tiempo total dedicado:</small>
                                <p style="font-weight: bold; font-size: 1.2rem; color: var(--success);">
                                    <?php 
                                    $horas = floor($tiempoTotal / 60);
                                    $minutos = $tiempoTotal % 60;
                                    echo $horas > 0 ? "{$horas}h " : "";
                                    echo "{$minutos}min";
                                    ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <div>
                                <small class="text-muted">Creado el:</small>
                                <p><?php echo date('d/m/Y', strtotime($post['fecha_creacion'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

        </div>
    </div>

    <div id="imageModal" class="modal" onclick="closeModal()">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        function openModal(imageUrl) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = imageUrl;
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>