<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Post.php';
require_once '../app/Models/Iteracion.php';
require_once '../app/Models/Miniproyecto.php';

if (!isset($_GET['id'])) { 
    header('Location: dashboard_artista.php'); 
    exit(); 
}

$post_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$modeloPost = new Post();
$modeloIteracion = new Iteracion();
$modeloMini = new Miniproyecto();

$post = $modeloPost->obtenerPorId($post_id, $usuario_id);
if (!$post) { 
    die("Post no encontrado."); 
}

$iteraciones = $modeloIteracion->obtenerPorPost($post_id);
$esDestacado = $modeloPost->esDestacado($post_id);

$esPostIndividual = false;
if ($post['miniproyecto_id']) {
    $esPostIndividual = $modeloMini->esPostIndividual($post['miniproyecto_id']);
}

$totalImagenes = 0;
foreach ($iteraciones as $iter) {
    $totalImagenes += count($iter['imagenes']);
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT nombre_artistico, avatar_url FROM perfiles WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['titulo']); ?> | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/post-viewer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #0a0a0a;">
    <div class="app-layout" style="background: #0a0a0a;">
        <?php $active_page = 'dashboard'; include 'includes/sidebar.php'; ?>

        <main class="main-content" style="background: #0a0a0a;">
    <header class="fixed-header">
        <div class="header-content">
            <div class="breadcrumb-nav">
                <a href="dashboard_artista.php"><i class="fas fa-home"></i></a>
                <span>></span>
                <?php if ($post['miniproyecto_id']): ?>
                    <a href="ver_miniproyecto.php?id=<?php echo $post['miniproyecto_id']; ?>">Mini Proyecto</a>
                    <span>></span>
                <?php endif; ?>
                <span class="current"><?php echo htmlspecialchars($post['titulo']); ?></span>
            </div>
            
            <div class="header-actions">
                <button class="icon-btn <?php echo $esDestacado ? 'active' : ''; ?>" 
                        onclick="toggleDestacado()" 
                        title="<?php echo $esDestacado ? 'Quitar destacado' : 'Destacar'; ?>">
                    <i class="fas fa-star"></i>
                </button>
                
                <button class="icon-btn" onclick="window.location.href='dashboard_artista.php'" title="Mis trabajos">
                    <i class="fas fa-th-large"></i>
                </button>
                
                <?php if ($esPostIndividual): ?>
                    <button class="icon-btn" onclick="convertirAMiniproyecto()" title="Convertir a Mini Proyecto">
                        <i class="fas fa-folder-plus"></i>
                    </button>
                <?php endif; ?>
                
                <button class="icon-btn danger" onclick="eliminarPost()" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="viewer-container">
        
        <main class="main-content">

            <?php if (isset($_GET['mensaje'])): ?>
                <div class="alert-message success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    switch($_GET['mensaje']) {
                        case 'iteracion_creada': echo 'Nueva iteración creada'; break;
                        case 'iteracion_eliminada': echo 'Iteración eliminada'; break;
                        case 'convertido_a_miniproyecto': echo 'Convertido a Mini Proyecto'; break;
                        default: echo 'Acción completada';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (empty($iteraciones)): ?>
                <div class="empty-state">
                    <i class="fas fa-layer-group"></i>
                    <h3>Sin iteraciones</h3>
                    <p>Documenta tu proceso creativo subiendo la primera versión</p>
                    <a href="crear_iteracion.php?post_id=<?php echo $post_id; ?>" class="btn btn-primary">
                        + Subir Primera Iteración
                    </a>
                </div>
            <?php else: ?>
                
                <div class="image-viewer">
                    
                    <div id="normalView" class="viewer-mode active">
                        <div class="main-image-container">
                            <img id="mainImage" src="" alt="Imagen principal">
                        </div>
                    </div>
                    
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

                <div class="iteration-gallery">
                    <h4>Imágenes de esta iteración</h4>
                    <div class="gallery-grid" id="galleryGrid">
                    </div>
                </div>

                <div id="iterationInfo" class="iteration-info">
                </div>

            <?php endif; ?>

        </main>

        <aside class="sidebar">
            
            <div class="post-header">
                <h1><?php echo htmlspecialchars($post['titulo']); ?></h1>
                <div class="badges">
                    <span class="badge badge-category"><?php echo htmlspecialchars($post['nombre_categoria']); ?></span>
                    <?php if ($esPostIndividual): ?>
                        <span class="badge badge-individual"><i class="fas fa-file"></i> INDIVIDUAL</span>
                    <?php endif; ?>
                </div><br>
                <div class="author-info">
                <?php if (!empty($perfil['avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($perfil['avatar_url']); ?>" alt="" class="author-avatar">
                <?php else: ?>
                    <div class="author-avatar placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <div class="author-details">
                    <strong style= font-size:14px><?php echo htmlspecialchars($perfil['nombre_artistico'] ?? 'Artista'); ?></strong>
                    <span class="author-role">Editor</span>
                </div>
            </div>
            </div>                            
            

            <?php if (!empty($iteraciones)): ?>
                <?php if (count($iteraciones) >= 2): ?>
                    <button id="toggleCompareBtn" class="btn-compare" onclick="toggleCompareMode()">
                        <i class="fas fa-code-compare"></i> VER COMPARACIÓN
                    </button>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="timeline-section">
                <h3><i class="fas fa-clock-rotate-left"></i> Timeline</h3>
                
                <?php if ($totalImagenes < 50): ?>
                    <button onclick="window.location.href='crear_iteracion.php?post_id=<?php echo $post_id; ?>'" 
                            class="btn btn-secondary btn-new-iteration">
                        <i class="fas fa-plus"></i> Nueva Iteración
                    </button>
                <?php else: ?>
                    <div class="limit-notice">
                        <i class="fas fa-exclamation-triangle"></i>
                        Límite de 50 imágenes alcanzado
                    </div>
                <?php endif; ?>
                
                <div class="timeline">
                    <?php foreach ($iteraciones as $index => $iter): ?>
                        <div class="timeline-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                             data-iteration-id="<?php echo $iter['id']; ?>"
                             onclick="selectIteration(<?php echo $iter['id']; ?>)">
                            
                            <div class="timeline-line">
                                <div class="timeline-dot <?php echo $index === 0 ? 'current' : ''; ?>"></div>
                            </div>
                            
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
                                        
                                        if ($diff->days == 0) echo '(Hoy)';
                                        elseif ($diff->days == 1) echo '(Ayer)';
                                        else echo '(' . date('d/m', strtotime($iter['fecha_creacion'])) . ')';
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <button class="timeline-delete" 
                                    onclick="event.stopPropagation(); eliminarIteracion(<?php echo $iter['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sidebar-section stats">
                <h3><i class="fas fa-chart-simple"></i> Estadísticas</h3>
                
                <div class="stat-row">
                    <span>Iteraciones:</span>
                    <strong><?php echo count($iteraciones); ?></strong>
                </div>
                
                <div class="stat-row">
                    <span>Imágenes:</span>
                    <strong class="<?php echo $totalImagenes >= 40 ? 'warning' : ''; ?>">
                        <?php echo $totalImagenes; ?> / 50
                    </strong>
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

        </aside>

    </div>

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
                const mainImg = iteration.imagenes.find(img => img.es_principal) || iteration.imagenes[0];
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
                    
                    item.innerHTML = `
                        <img src="${img.url_archivo}" alt="" data-url="${img.url_archivo}">
                        ${img.es_principal ? '<span class="badge-principal">★ PRINCIPAL</span>' : ''}
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
                        <h4><i class="fas fa-sticky-note"></i> Notas de Cambio</h4>
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
                const currentImg = currentIteration.imagenes.find(img => img.es_principal) || currentIteration.imagenes[0];
                const compareImg = compareIteration.imagenes.find(img => img.es_principal) || compareIteration.imagenes[0];
                
                document.getElementById('beforeImage').src = currentImg.url_archivo;
                document.getElementById('afterImage').src = compareImg.url_archivo;
            }
        }

        function initCompareSlider() {
            const slider = document.getElementById('slider');
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

            // Touch
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
            if (e.key === 'Escape') closeModal();
        });

        function toggleDestacado() {
            window.location.href = 'procesador.php?action=toggle_destacado&id=<?php echo $post_id; ?>';
        }

        function convertirAMiniproyecto() {
            if (confirm('¿Convertir en Mini Proyecto? Podrás agregar más trabajos relacionados.')) {
                window.location.href = 'procesador.php?action=convertir_a_miniproyecto&post_id=<?php echo $post_id; ?>';
            }
        }

        function eliminarPost() {
            if (confirm('¿Eliminar este post y TODAS sus iteraciones?\n\nEsta acción no se puede deshacer.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'procesador.php?action=eliminar_post';
                form.innerHTML = '<input type="hidden" name="post_id" value="<?php echo $post_id; ?>">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function eliminarIteracion(id) {
            if (confirm('¿Eliminar esta iteración?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'procesador.php?action=eliminar_iteracion';
                form.innerHTML = `<input type="hidden" name="iteracion_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
        </main>
    </div>
</body>
</html>