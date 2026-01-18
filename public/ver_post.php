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
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .gallery-item {
            position: relative;
            border-radius: var(--radius);
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .gallery-item:hover {
            transform: scale(1.05);
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        .gallery-item.principal::after {
            content: '★ PRINCIPAL';
            position: absolute;
            top: 8px;
            left: 8px;
            background: var(--accent);
            color: black;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
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
                        echo '<i class="fas fa-check"></i> Nueva versión creada exitosamente';
                        break;
                    case 'iteracion_eliminada':
                        echo '<i class="fas fa-check"></i> Versión eliminada correctamente';
                        break;
                    case 'iteracion_actualizada':
                        echo '<i class="fas fa-check"></i> Versión actualizada';
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
                      onsubmit="return confirm('¿Eliminar este post y todas sus versiones?');" 
                      style="display:inline;">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Eliminar Post</button>
                </form>
            </div>
        </div>

        <hr>

        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 30px;">
            
            <div>
                <div class="section-header" style="margin-top:0;">
                    <h2>Historial de Versiones (<?php echo count($iteraciones); ?>)</h2>
                    <a href="crear_iteracion.php?post_id=<?php echo $post_id; ?>" class="btn btn-primary">+ Nueva Versión</a>
                </div>

                <?php if (empty($iteraciones)): ?>
                    <div class="empty-state">
                        <p>Aún no has subido ninguna versión de este trabajo.</p>
                        <p>Comienza documentando tu proceso creativo subiendo la primera iteración.</p>
                        <br>
                        <a href="crear_iteracion.php?post_id=<?php echo $post_id; ?>" class="btn btn-primary">Subir Primera Versión</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($iteraciones as $iter): ?>
                        <div class="iteration-card">
                            <div class="iteration-header">
                                <div>
                                    <span class="version-badge">Versión <?php echo $iter['numero_version']; ?></span>
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
                                      onsubmit="return confirm('¿Eliminar esta versión?');" 
                                      style="display:inline;">
                                    <input type="hidden" name="iteracion_id" value="<?php echo $iter['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>

                            <?php if (!empty($iter['imagenes'])): ?>
                                <div class="gallery-grid">
                                    <?php foreach ($iter['imagenes'] as $imagen): ?>
                                        <div class="gallery-item <?php echo $imagen['es_principal'] ? 'principal' : ''; ?>" 
                                             onclick="openModal('<?php echo htmlspecialchars($imagen['url_archivo']); ?>')">
                                            <img src="<?php echo htmlspecialchars($imagen['url_archivo']); ?>" 
                                                 alt="Imagen versión <?php echo $iter['numero_version']; ?>" 
                                                 loading="lazy">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Sin imágenes en esta versión</p>
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
                                <small class="text-muted">Total versiones:</small>
                                <p style="font-weight: bold; font-size: 1.2rem; color: var(--primary);">
                                    <?php echo count($iteraciones); ?>
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
                                <p style="font-weight: bold; font-size: 1.2rem; color: var(--accent);">
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