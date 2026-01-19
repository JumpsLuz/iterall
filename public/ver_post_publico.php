<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Post.php';
require_once '../app/Models/Iteracion.php';
require_once '../app/Models/Coleccion.php';
require_once '../app/Helpers/CategoryTagHelper.php';

if (!isset($_GET['id'])) { 
    header('Location: explorar.php'); 
    exit(); 
}

$post_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$rol_id = $_SESSION['rol_id'];

$modeloPost = new Post();
$modeloIteracion = new Iteracion();
$modeloColeccion = new Coleccion();

$post = $modeloPost->obtenerPublicoPorId($post_id);

if (!$post) { 
    header('Location: explorar.php?error=no_encontrado');
    exit();
}

$postCategories = CategoryTagHelper::getPostCategories($post_id);
$postTags = CategoryTagHelper::getPostTags($post_id);

$iteraciones = $modeloIteracion->obtenerPorPost($post_id);

$esCliente = ($rol_id == 2);
$coleccionesUsuario = [];
$postGuardadoEn = [];
if ($esCliente) {
    $coleccionesUsuario = $modeloColeccion->obtenerPorUsuario($usuario_id);
    $postGuardadoEn = $modeloColeccion->postEstaGuardado($post_id, $usuario_id);
}

$esPropietario = ($post['artista_id'] == $usuario_id);

$totalImagenes = 0;
foreach ($iteraciones as $iter) {
    $totalImagenes += count($iter['imagenes']);
}

$esArtista = ($rol_id == 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['titulo']); ?> | ITERALL</title>
    <?php include 'includes/favicon.php'; ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/post-viewer.css">
    <link rel="stylesheet" href="css/post-publico.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #0a0a0a;">
    <header class="fixed-header">
        <div class="header-content">
            <div class="breadcrumb-nav">
                <a href="explorar.php"><i class="fas fa-compass"></i> Explorar</a>
                <span>></span>
                <span class="current"><?php echo htmlspecialchars($post['titulo']); ?></span>
            </div>
            
            <div class="header-actions">
                <?php if ($esCliente): ?>
                    <!-- Botón guardar en colección -->
                    <button class="icon-btn <?php echo !empty($postGuardadoEn) ? 'active' : ''; ?>" 
                            onclick="abrirModalColecciones()" 
                            title="Guardar en colección"
                            id="btnGuardar">
                        <i class="<?php echo !empty($postGuardadoEn) ? 'fas' : 'far'; ?> fa-bookmark"></i>
                    </button>
                <?php endif; ?>
                
                <?php if ($esPropietario): ?>
                    <a href="ver_post.php?id=<?php echo $post_id; ?>" class="icon-btn" title="Editar mi post">
                        <i class="fas fa-edit"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <?php if ($esCliente): ?>
    <!-- Modal para elegir colección -->
    <div class="modal-backdrop" id="modalColecciones">
        <div class="modal-coleccion">
            <div class="modal-coleccion-header">
                <h3><i class="fas fa-bookmark"></i> Guardar en colección</h3>
                <button class="modal-close-btn" onclick="cerrarModalColecciones()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-coleccion-body">
                <?php if (empty($coleccionesUsuario)): ?>
                    <div class="empty-colecciones">
                        <i class="fas fa-folder-open"></i>
                        <p>No tienes colecciones aún</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($coleccionesUsuario as $col): ?>
                        <?php 
                        $yaGuardado = false;
                        foreach ($postGuardadoEn as $guardado) {
                            if ($guardado['id'] == $col['id']) {
                                $yaGuardado = true;
                                break;
                            }
                        }
                        ?>
                        <button class="coleccion-option <?php echo $yaGuardado ? 'guardado' : ''; ?>"
                                onclick="toggleEnColeccion(<?php echo $col['id']; ?>, <?php echo $post_id; ?>)"
                                id="coleccion-<?php echo $col['id']; ?>">
                            <i class="<?php echo $yaGuardado ? 'fas fa-check-circle' : 'far fa-folder'; ?>"></i>
                            <span><?php echo htmlspecialchars($col['nombre']); ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="modal-coleccion-footer">
                <a href="mis_colecciones.php?crear=1" class="btn-nueva-coleccion">
                    <i class="fas fa-plus"></i> Nueva Colección
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="viewer-container">
        <div class="viewer-main">
            
            <?php if (empty($iteraciones)): ?>
                <div class="empty-state">
                    <i class="fas fa-layer-group"></i>
                    <h3>Este post aún no tiene iteraciones</h3>
                    <p>El artista aún no ha subido contenido</p>
                </div>
            <?php else: ?>
                
                <div class="image-viewer">
                    <!-- Vista normal -->
                    <div id="normalView" class="viewer-mode active">
                        <div class="main-image-container">
                            <img id="mainImage" src="" alt="Imagen principal">
                        </div>
                    </div>
                    
                    <!-- Vista comparación -->
                    <div id="compareView" class="viewer-mode">
                        <div class="comparison-container">
                            <div class="comparison-wrapper">
                                <div class="comparison-before">
                                    <img id="beforeImage" src="" alt="Before">
                                </div>
                                <div class="comparison-after">
                                    <img id="afterImage" src="" alt="After">
                                </div>
                                <div class="comparison-slider" id="slider">
                                    <div class="slider-handle">
                                        <i class="fas fa-arrows-left-right"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="comparison-labels">
                                <span class="label-before">ANTES</span>
                                <span class="label-after">DESPUÉS</span>
                            </div>
                        </div>
                        
                        <div class="compare-selector">
                            <label>Comparar con:</label>
                            <select id="compareWithSelect" onchange="updateComparison()">
                                <?php foreach (array_reverse($iteraciones) as $iter): ?>
                                    <?php if (!empty($iter['imagenes'])): ?>
                                        <option value="<?php echo $iter['id']; ?>">
                                            Iteración <?php echo $iter['numero_version']; ?> - <?php echo date('d/m/Y', strtotime($iter['fecha_creacion'])); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Galería de imágenes de la iteración -->
                <div class="iteration-gallery">
                    <h4>Imágenes de esta iteración</h4>
                    <div class="gallery-grid" id="galleryGrid"></div>
                </div>

                <!-- Info de la iteración -->
                <div id="iterationInfo" class="iteration-info"></div>

            <?php endif; ?>

        </div>

        <!-- Sidebar con info del post y artista -->
        <aside class="sidebar">
            
            <!-- Info del Post -->
            <div class="post-header">
                <h1><?php echo htmlspecialchars($post['titulo']); ?></h1>
                
                <!-- Categorías -->
                <div class="badges categories-badges">
                    <?php if (!empty($postCategories)): ?>
                        <?php foreach ($postCategories as $cat): ?>
                            <span class="badge badge-category-sm"><?php echo htmlspecialchars($cat['nombre_categoria']); ?></span>
                        <?php endforeach; ?>
                    <?php elseif (!empty($post['nombre_categoria'])): ?>
                        <span class="badge badge-category-sm"><?php echo htmlspecialchars($post['nombre_categoria']); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Etiquetas -->
                <?php if (!empty($postTags)): ?>
                <div class="tags-badges">
                    <?php foreach ($postTags as $tag): ?>
                        <?php if ($tag['nombre_etiqueta'] !== '#@#_no_mini_proyecto_#@#' && strtolower($tag['nombre_etiqueta']) !== 'destacado'): ?>
                            <a href="explorar.php?etiqueta=<?php echo urlencode($tag['nombre_etiqueta']); ?>" 
                               class="badge badge-tag">#<?php echo htmlspecialchars($tag['nombre_etiqueta']); ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Info del Artista -->
                <a href="perfil_publico.php?id=<?php echo $post['artista_id']; ?>" class="author-info-link">
                    <?php if (!empty($post['artista_avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($post['artista_avatar']); ?>" alt="" class="author-avatar">
                    <?php else: ?>
                        <div class="author-avatar placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div class="author-details">
                        <strong><?php echo htmlspecialchars($post['nombre_artistico'] ?? 'Artista'); ?></strong>
                        <span class="author-role">Ver perfil</span>
                    </div>
                </a>
            </div>

            <?php if (!empty($iteraciones)): ?>
                <?php if (count($iteraciones) >= 2): ?>
                    <button id="toggleCompareBtn" class="btn-compare" onclick="toggleCompareMode()">
                        <i class="fas fa-code-compare"></i> VER COMPARACIÓN
                    </button>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Timeline -->
            <div class="timeline-section">
                <h3><i class="fas fa-clock-rotate-left"></i> Proceso Creativo</h3>
                
                <div class="timeline">
                    <?php foreach ($iteraciones as $index => $iter): ?>
                        <div class="timeline-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                             data-iteration-id="<?php echo $iter['id']; ?>"
                             onclick="selectIteration(<?php echo $iter['id']; ?>)">
                            
                            <div class="timeline-content">
                                <?php if (!empty($iter['imagenes'])): ?>
                                    <div class="timeline-thumb">
                                        <img src="<?php echo htmlspecialchars($iter['imagenes'][0]['url_archivo']); ?>" alt="">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="timeline-text">
                                    <strong>Iteración <?php echo $iter['numero_version']; ?></strong>
                                    <span class="timeline-date">
                                        <?php 
                                        $fecha = new DateTime($iter['fecha_creacion']);
                                        $ahora = new DateTime();
                                        $diff = $ahora->diff($fecha);
                                        
                                        if ($diff->days == 0) echo 'Hoy';
                                        elseif ($diff->days == 1) echo 'Ayer';
                                        else echo date('d/m/Y', strtotime($iter['fecha_creacion']));
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="sidebar-section stats">
                <h3><i class="fas fa-chart-simple"></i> Estadísticas</h3>
                
                <div class="stat-row">
                    <span>Iteraciones:</span>
                    <strong><?php echo count($iteraciones); ?></strong>
                </div>
                
                <div class="stat-row">
                    <span>Imágenes:</span>
                    <strong><?php echo $totalImagenes; ?></strong>
                </div>
                
                <?php 
                $tiempoTotal = array_sum(array_column($iteraciones, 'tiempo_dedicado_min'));
                if ($tiempoTotal > 0):
                ?>
                <div class="stat-row">
                    <span>Tiempo total:</span>
                    <strong>
                        <?php 
                        $h = floor($tiempoTotal / 60);
                        $m = $tiempoTotal % 60;
                        echo ($h > 0 ? "{$h}h " : "") . "{$m}min";
                        ?>
                    </strong>
                </div>
                <?php endif; ?>
                
                <div class="stat-row">
                    <span>Creado:</span>
                    <strong><?php echo date('d/m/Y', strtotime($post['fecha_creacion'])); ?></strong>
                </div>
            </div>

            <!-- Botón de contacto -->
            <div class="sidebar-section">
                <a href="perfil_publico.php?id=<?php echo $post['artista_id']; ?>#contacto" 
                   class="btn btn-primary" style="width: 100%; text-align: center;">
                    <i class="fas fa-envelope"></i> Contactar Artista
                </a>
            </div>

        </aside>
    </div>

    <!-- Modal imagen grande -->
    <div id="imageModal" class="modal-overlay" onclick="closeModal()">
        <span class="modal-close">&times;</span>
        <img id="modalImage" class="modal-image" src="" alt="">
    </div>

    <script>
        const iteracionesData = <?php echo json_encode($iteraciones); ?>;
        let currentIterationId = <?php echo !empty($iteraciones) ? $iteraciones[0]['id'] : 'null'; ?>;
        let compareMode = false;

        document.addEventListener('DOMContentLoaded', function() {
            if (currentIterationId) {
                selectIteration(currentIterationId);
            }
            <?php if (count($iteraciones) >= 2): ?>
                initCompareSlider();
            <?php endif; ?>
        });

        function selectIteration(iterationId) {
            currentIterationId = iterationId;
            const iteration = iteracionesData.find(i => i.id == iterationId);
            if (!iteration) return;

            document.querySelectorAll('.timeline-item').forEach(item => {
                item.classList.remove('active');
                if (item.dataset.iterationId == iterationId) {
                    item.classList.add('active');
                }
            });

            if (iteration.imagenes && iteration.imagenes.length > 0) {
                const mainImg = iteration.imagenes.find(img => img.es_principal == 1) || iteration.imagenes[0];
                document.getElementById('mainImage').src = mainImg.url_archivo;
            }

            updateGallery(iteration);
            updateInfo(iteration);

            if (compareMode) {
                updateComparison();
            }
        }

        function updateGallery(iteration) {
            const gallery = document.getElementById('galleryGrid');
            gallery.innerHTML = '';

            if (iteration.imagenes && iteration.imagenes.length > 0) {
                iteration.imagenes.forEach((img, index) => {
                    const item = document.createElement('div');
                    item.className = 'gallery-item';
                    if (index === 0) item.classList.add('active');
                    
                    const esPrincipal = img.es_principal == 1 || img.es_principal === true;
                    
                    item.innerHTML = `
                        <img src="${img.url_archivo}" alt="" data-url="${img.url_archivo}">
                        ${esPrincipal ? '<span class="badge-principal">★ PRINCIPAL</span>' : ''}
                    `;
                    
                    item.addEventListener('click', function() {
                        if (!compareMode) {
                            document.getElementById('mainImage').src = img.url_archivo;
                            document.querySelectorAll('.gallery-item').forEach(g => g.classList.remove('active'));
                            item.classList.add('active');
                        } else {
                            openModal(img.url_archivo);
                        }
                    });
                    
                    gallery.appendChild(item);
                });
            }
        }

        function updateInfo(iteration) {
            const infoDiv = document.getElementById('iterationInfo');
            let html = '';

            if (iteration.notas_cambio && iteration.notas_cambio.trim()) {
                html += `
                    <div class="info-section">
                        <h4><i class="fas fa-sticky-note"></i> Notas del Artista</h4>
                        <p>${iteration.notas_cambio.replace(/\n/g, '<br>')}</p>
                    </div>
                `;
            }

            if (iteration.tiempo_dedicado_min) {
                const h = Math.floor(iteration.tiempo_dedicado_min / 60);
                const m = iteration.tiempo_dedicado_min % 60;
                const tiempo = (h > 0 ? `${h}h ` : '') + `${m}min`;
                
                html += `
                    <div class="info-section">
                        <h4><i class="fas fa-clock"></i> Tiempo Dedicado</h4>
                        <p>${tiempo}</p>
                    </div>
                `;
            }

            infoDiv.innerHTML = html;
        }

        function toggleCompareMode() {
            compareMode = !compareMode;
            const normalView = document.getElementById('normalView');
            const compareView = document.getElementById('compareView');
            const btn = document.getElementById('toggleCompareBtn');

            if (compareMode) {
                normalView.classList.remove('active');
                compareView.classList.add('active');
                btn.innerHTML = '<i class="fas fa-image"></i> VER NORMAL';
                btn.classList.add('active');
                updateComparison();
            } else {
                normalView.classList.add('active');
                compareView.classList.remove('active');
                btn.innerHTML = '<i class="fas fa-code-compare"></i> VER COMPARACIÓN';
                btn.classList.remove('active');
            }
        }

        function updateComparison() {
            const currentIteration = iteracionesData.find(i => i.id == currentIterationId);
            const compareSelect = document.getElementById('compareWithSelect');
            const compareIteration = iteracionesData.find(i => i.id == compareSelect.value);

            if (currentIteration?.imagenes?.[0] && compareIteration?.imagenes?.[0]) {
                const currentImg = currentIteration.imagenes.find(img => img.es_principal == 1) || currentIteration.imagenes[0];
                const compareImg = compareIteration.imagenes.find(img => img.es_principal == 1) || compareIteration.imagenes[0];
                
                document.getElementById('beforeImage').src = currentImg.url_archivo;
                document.getElementById('afterImage').src = compareImg.url_archivo;
            }
        }

        function initCompareSlider() {
            const slider = document.getElementById('slider');
            if (!slider) return;
            
            const container = slider.parentElement;
            const afterDiv = container.querySelector('.comparison-after');
            
            let isDragging = false;

            function updatePosition(clientX) {
                const rect = container.getBoundingClientRect();
                const x = Math.max(0, Math.min(clientX - rect.left, rect.width));
                const percentage = (x / rect.width) * 100;
                
                slider.style.left = percentage + '%';
                afterDiv.style.clipPath = `inset(0 0 0 ${percentage}%)`;
            }

            slider.addEventListener('mousedown', () => isDragging = true);
            document.addEventListener('mousemove', (e) => {
                if (isDragging) updatePosition(e.clientX);
            });
            document.addEventListener('mouseup', () => isDragging = false);

            container.addEventListener('click', (e) => updatePosition(e.clientX));

            slider.addEventListener('touchstart', () => isDragging = true);
            document.addEventListener('touchmove', (e) => {
                if (isDragging) updatePosition(e.touches[0].clientX);
            });
            document.addEventListener('touchend', () => isDragging = false);
        }

        function openModal(url) {
            document.getElementById('modalImage').src = url;
            document.getElementById('imageModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
                cerrarModalColecciones();
            }
        });

        // Funcionalidad de guardar en colección con modal
        function abrirModalColecciones() {
            document.getElementById('modalColecciones').classList.add('active');
        }
        
        function cerrarModalColecciones() {
            const modal = document.getElementById('modalColecciones');
            if (modal) modal.classList.remove('active');
        }
        
        // Cerrar modal al hacer clic en el backdrop
        document.getElementById('modalColecciones')?.addEventListener('click', (e) => {
            if (e.target.id === 'modalColecciones') {
                cerrarModalColecciones();
            }
        });

        function toggleEnColeccion(coleccionId, postId) {
            const btn = document.getElementById('coleccion-' + coleccionId);
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Guardando...</span>';
            btn.disabled = true;
            
            fetch('procesador.php?action=toggle_coleccion', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `coleccion_id=${coleccionId}&post_id=${postId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Actualizar UI sin recargar página
                    const isNowSaved = data.action === 'added';
                    btn.classList.toggle('guardado', isNowSaved);
                    btn.querySelector('i').className = isNowSaved ? 'fas fa-check-circle' : 'far fa-folder';
                    btn.querySelector('span').textContent = btn.querySelector('span').textContent;
                    
                    // Actualizar botón principal
                    const mainBtn = document.getElementById('btnGuardar');
                    const hayGuardados = document.querySelectorAll('.coleccion-option.guardado').length > 0;
                    mainBtn.classList.toggle('active', hayGuardados);
                    mainBtn.querySelector('i').className = hayGuardados ? 'fas fa-bookmark' : 'far fa-bookmark';
                } else {
                    alert(data.error || 'Error al guardar');
                    btn.innerHTML = originalHtml;
                }
                btn.disabled = false;
            })
            .catch(() => {
                alert('Error de conexión');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>