<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Post.php';
require_once '../app/Models/Proyecto.php';
require_once '../app/Helpers/CategoryTagHelper.php';

$modeloPost = new Post();
$modeloProyecto = new Proyecto();

$categorias = $modeloProyecto->obtenerCategorias();

$etiquetasPopulares = $modeloPost->obtenerEtiquetasPopulares(15);

$filtros = [
    'categoria_id' => $_GET['categoria'] ?? null,
    'busqueda' => $_GET['q'] ?? null,
    'etiqueta' => $_GET['etiqueta'] ?? null,
    'orden' => $_GET['orden'] ?? 'reciente',
    'limite' => 24,
    'offset' => (($_GET['pagina'] ?? 1) - 1) * 24
];

$tipoVista = $_GET['tipo'] ?? 'trabajos';
$items = [];
$totalItems = 0;

if ($tipoVista === 'proyectos') {
    $items = $modeloProyecto->obtenerPublicos($filtros);
    $totalItems = $modeloProyecto->contarPublicos($filtros);
} else {
    $items = $modeloPost->obtenerPublicos($filtros);
    $totalItems = $modeloPost->contarPublicos($filtros);
}

$totalPaginas = ceil($totalItems / 24);
$paginaActual = ($_GET['pagina'] ?? 1);

$esArtista = ($_SESSION['rol_id'] == 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/explorar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-layout">
        <?php 
        $active_page = 'explorar'; 
        if ($esArtista) {
            include 'includes/sidebar.php';
        } else {
            include 'includes/sidebar_cliente.php';
        }
        ?>

        <main class="main-content">
            <div class="container">
                
                <!-- Barra de búsqueda principal -->
                <div class="busqueda-hero">
                    <h1><i class="fas fa-compass"></i> Explorar <?php echo $tipoVista === 'proyectos' ? 'Proyectos' : 'Trabajos'; ?></h1>
                    <p class="text-muted">Descubre el proceso creativo de artistas talentosos</p>
                    
                    <form action="explorar.php" method="GET" class="busqueda-form">
                        <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipoVista); ?>">
                        <div class="busqueda-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   name="q" 
                                   class="busqueda-input" 
                                   placeholder="Buscar por título o artista..." 
                                   value="<?php echo htmlspecialchars($filtros['busqueda'] ?? ''); ?>">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </form>
                </div>

                <!-- Tabs de Tipo -->
                <div class="tipo-tabs" style="margin-bottom: 20px; border-bottom: 1px solid #333; display: flex; gap: 20px;">
                    <a href="explorar.php?tipo=trabajos<?php echo $filtros['busqueda'] ? '&q=' . urlencode($filtros['busqueda']) : ''; ?><?php echo $filtros['categoria_id'] ? '&categoria=' . $filtros['categoria_id'] : ''; ?>" 
                       style="padding: 10px 20px; text-decoration: none; color: <?php echo $tipoVista === 'trabajos' ? 'var(--primary)' : 'var(--text-muted)'; ?>; border-bottom: 2px solid <?php echo $tipoVista === 'trabajos' ? 'var(--primary)' : 'transparent'; ?>; font-weight: 500;">
                       <i class="fas fa-image"></i> Trabajos
                    </a>
                    <a href="explorar.php?tipo=proyectos<?php echo $filtros['busqueda'] ? '&q=' . urlencode($filtros['busqueda']) : ''; ?><?php echo $filtros['categoria_id'] ? '&categoria=' . $filtros['categoria_id'] : ''; ?>" 
                       style="padding: 10px 20px; text-decoration: none; color: <?php echo $tipoVista === 'proyectos' ? 'var(--primary)' : 'var(--text-muted)'; ?>; border-bottom: 2px solid <?php echo $tipoVista === 'proyectos' ? 'var(--primary)' : 'transparent'; ?>; font-weight: 500;">
                       <i class="fas fa-folder"></i> Proyectos
                    </a>
                </div>

                <!-- Filtros -->
                <div class="filtros-container">
                    <div class="filtros-categorias">
                        <a href="explorar.php?tipo=<?php echo $tipoVista; ?>" class="filtro-chip <?php echo empty($filtros['categoria_id']) ? 'active' : ''; ?>">
                            Todas
                        </a>
                        <?php foreach ($categorias as $cat): ?>
                            <a href="explorar.php?tipo=<?php echo $tipoVista; ?>&categoria=<?php echo $cat['id']; ?><?php echo $filtros['busqueda'] ? '&q=' . urlencode($filtros['busqueda']) : ''; ?>" 
                               class="filtro-chip <?php echo $filtros['categoria_id'] == $cat['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="filtros-orden">
                        <label>Ordenar:</label>
                        <select onchange="window.location.href=this.value">
                            <option value="explorar.php?tipo=<?php echo $tipoVista; ?>&orden=reciente<?php echo $filtros['categoria_id'] ? '&categoria=' . $filtros['categoria_id'] : ''; ?><?php echo $filtros['busqueda'] ? '&q=' . urlencode($filtros['busqueda']) : ''; ?>" 
                                    <?php echo $filtros['orden'] === 'reciente' ? 'selected' : ''; ?>>
                                Más recientes
                            </option>
                            <option value="explorar.php?tipo=<?php echo $tipoVista; ?>&orden=antiguo<?php echo $filtros['categoria_id'] ? '&categoria=' . $filtros['categoria_id'] : ''; ?><?php echo $filtros['busqueda'] ? '&q=' . urlencode($filtros['busqueda']) : ''; ?>"
                                    <?php echo $filtros['orden'] === 'antiguo' ? 'selected' : ''; ?>>
                                Más antiguos
                            </option>
                            <?php if ($tipoVista === 'trabajos'): ?>
                            <option value="explorar.php?tipo=trabajos&orden=iteraciones<?php echo $filtros['categoria_id'] ? '&categoria=' . $filtros['categoria_id'] : ''; ?><?php echo $filtros['busqueda'] ? '&q=' . urlencode($filtros['busqueda']) : ''; ?>"
                                    <?php echo $filtros['orden'] === 'iteraciones' ? 'selected' : ''; ?>>
                                Más iteraciones
                            </option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Etiquetas populares (Solo para trabajos) -->
                <?php if ($tipoVista === 'trabajos' && !empty($etiquetasPopulares) && empty($filtros['etiqueta'])): ?>
                <div class="etiquetas-populares">
                    <span class="etiquetas-label"><i class="fas fa-hashtag"></i> Populares:</span>
                    <?php foreach ($etiquetasPopulares as $etq): ?>
                        <a href="explorar.php?etiqueta=<?php echo urlencode($etq['nombre_etiqueta']); ?>" 
                           class="etiqueta-link">
                            #<?php echo htmlspecialchars($etq['nombre_etiqueta']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Indicador de filtro activo -->
                <?php if (!empty($filtros['etiqueta'])): ?>
                <div class="filtro-activo">
                    <span>Mostrando posts con etiqueta: <strong>#<?php echo htmlspecialchars($filtros['etiqueta']); ?></strong></span>
                    <a href="explorar.php" class="btn btn-secondary btn-sm">× Limpiar filtro</a>
                </div>
                <?php endif; ?>

                <!-- Resultados -->
                <div class="resultados-info">
                    <span><?php echo $totalItems; ?> <?php echo $tipoVista === 'proyectos' ? 'proyecto' : 'trabajo'; ?><?php echo $totalItems != 1 ? 's' : ''; ?> encontrado<?php echo $totalItems != 1 ? 's' : ''; ?></span>
                </div>

                <?php if (empty($items)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search" style="font-size: 3rem; color: #555; margin-bottom: 15px;"></i>
                        <h3>No se encontraron <?php echo $tipoVista === 'proyectos' ? 'proyectos' : 'trabajos'; ?></h3>
                        <p>Intenta con otros filtros o términos de búsqueda</p>
                    </div>
                <?php else: ?>
                    <div class="galeria-posts">
                        <?php foreach ($items as $item): ?>
                            <?php if ($tipoVista === 'trabajos'): ?>
                                <?php 
                                    $linkVer = ($item['artista_id'] == $_SESSION['usuario_id']) ? 'ver_post.php' : 'ver_post_publico.php';
                                    $postCategories = CategoryTagHelper::getPostCategories($item['id']);
                                    $postTags = CategoryTagHelper::getPostTags($item['id']);
                                ?>
                                <a href="<?php echo $linkVer; ?>?id=<?php echo $item['id']; ?>" class="post-card-publico">
                                    <div class="post-imagen">
                                        <?php if (!empty($item['portada'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['portada']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['titulo']); ?>"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <div class="placeholder-img">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="post-overlay">
                                            <div class="post-stats">
                                                <span><i class="fas fa-layer-group"></i> <?php echo $item['total_iteraciones']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="post-info">
                                        <h3 class="post-titulo"><?php echo htmlspecialchars($item['titulo']); ?></h3>
                                        <div class="post-artista">
                                            <?php if (!empty($item['artista_avatar'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['artista_avatar']); ?>" class="artista-avatar-mini" alt="">
                                            <?php else: ?>
                                                <div class="artista-avatar-mini placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span class="artista-nombre"><?php echo htmlspecialchars($item['nombre_artistico']); ?></span>
                                        </div>
                                        <div class="post-meta">
                                            <?php if (!empty($postCategories)): ?>
                                                <?php foreach ($postCategories as $cat): ?>
                                                    <span class="categoria-badge"><?php echo htmlspecialchars($cat['nombre_categoria']); ?></span>
                                                <?php endforeach; ?>
                                            <?php elseif (!empty($item['nombre_categoria'])): ?>
                                                <span class="categoria-badge"><?php echo htmlspecialchars($item['nombre_categoria']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($postTags)): ?>
                                        <div class="post-tags">
                                            <?php 
                                            $tagCount = 0;
                                            foreach ($postTags as $tag): 
                                                if ($tag['nombre_etiqueta'] !== '#@#_no_mini_proyecto_#@#' && strtolower($tag['nombre_etiqueta']) !== 'destacado' && $tagCount < 3):
                                                    $tagCount++;
                                            ?>
                                                <span class="tag-badge">#<?php echo htmlspecialchars($tag['nombre_etiqueta']); ?></span>
                                            <?php endif; endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php else: ?>
                                <?php
                                    $linkProyecto = ($item['artista_id'] == $_SESSION['usuario_id']) ? 'ver_proyecto.php' : 'ver_proyecto_publico.php';
                                    $projectCategories = CategoryTagHelper::getProjectCategories($item['id']);
                                    $projectTags = CategoryTagHelper::getProjectTags($item['id']);
                                ?>
                                <a href="<?php echo $linkProyecto; ?>?id=<?php echo $item['id']; ?>" class="post-card-publico">
                                    <div class="post-imagen">
                                        <?php if (!empty($item['avatar_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['avatar_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['titulo']); ?>"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <div class="placeholder-img">
                                                <i class="fas fa-folder"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="post-info">
                                        <h3 class="post-titulo"><?php echo htmlspecialchars($item['titulo']); ?></h3>
                                        <div class="post-artista">
                                            <?php if (!empty($item['artista_avatar'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['artista_avatar']); ?>" class="artista-avatar-mini" alt="">
                                            <?php else: ?>
                                                <div class="artista-avatar-mini placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span class="artista-nombre"><?php echo htmlspecialchars($item['nombre_artistico']); ?></span>
                                        </div>
                                        <div class="post-meta">
                                            <?php if (!empty($projectCategories)): ?>
                                                <?php foreach ($projectCategories as $cat): ?>
                                                    <span class="categoria-badge"><?php echo htmlspecialchars($cat['nombre_categoria']); ?></span>
                                                <?php endforeach; ?>
                                            <?php elseif (!empty($item['nombre_categoria'])): ?>
                                                <span class="categoria-badge"><?php echo htmlspecialchars($item['nombre_categoria']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($projectTags)): ?>
                                        <div class="post-tags">
                                            <?php 
                                            $tagCount = 0;
                                            foreach ($projectTags as $tag): 
                                                if ($tag['nombre_etiqueta'] !== '#@#_no_mini_proyecto_#@#' && strtolower($tag['nombre_etiqueta']) !== 'destacado' && $tagCount < 3):
                                                    $tagCount++;
                                            ?>
                                                <span class="tag-badge">#<?php echo htmlspecialchars($tag['nombre_etiqueta']); ?></span>
                                            <?php endif; endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($item['descripcion'])): ?>
                                        <p class="post-descripcion"><?php echo htmlspecialchars(mb_strimwidth(strip_tags($item['descripcion']), 0, 60, '...')); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>


                    </div>

                    <!-- Paginación -->
                    <?php if ($totalPaginas > 1): ?>
                    <div class="paginacion">
                        <?php if ($paginaActual > 1): ?>
                            <a href="explorar.php?pagina=<?php echo $paginaActual - 1; ?>&tipo=<?php echo $tipoVista; ?><?php echo $filtros['categoria_id'] ? '&categoria=' . $filtros['categoria_id'] : ''; ?><?php echo $filtros['busqueda'] ? '&q=' . urlencode($filtros['busqueda']) : ''; ?><?php echo $filtros['etiqueta'] ? '&etiqueta=' . urlencode($filtros['etiqueta']) : ''; ?>" 
                               class="btn btn-secondary">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        
                        <span class="paginacion-info">Página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?></span>
                        
                        <?php if ($paginaActual < $totalPaginas): ?>
                            <a href="explorar.php?pagina=<?php echo $paginaActual + 1; ?>&tipo=<?php echo $tipoVista; ?><?php echo $filtros['categoria_id'] ? '&categoria=' . $filtros['categoria_id'] : ''; ?><?php echo $filtros['busqueda'] ? '&q=' . urlencode($filtros['busqueda']) : ''; ?><?php echo $filtros['etiqueta'] ? '&etiqueta=' . urlencode($filtros['etiqueta']) : ''; ?>" 
                               class="btn btn-secondary">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </main>
    </div>
</body>
</html>