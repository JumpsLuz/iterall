<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Usuario.php';
require_once '../app/Models/Post.php';

if (!isset($_GET['id'])) {
    header('Location: explorar.php');
    exit();
}

$artista_id = $_GET['id'];
$rol_id = $_SESSION['rol_id'];
$esArtista = ($rol_id == 1);

$modeloUsuario = new Usuario();
$modeloPost = new Post();

$db = Database::getInstance();

$sql = "SELECT u.id, u.email, u.rol_id,
    p.nombre_artistico, p.biografia, p.avatar_url, p.banner_url, p.redes_sociales_json
        FROM usuarios u
        LEFT JOIN perfiles p ON p.usuario_id = u.id
        WHERE u.id = ? AND u.rol_id = 1";
$stmt = $db->prepare($sql);
$stmt->execute([$artista_id]);
$artista = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$artista) {
    header('Location: explorar.php?error=artista_no_encontrado');
    exit();
}

$redesSociales = [];
$redesJson = $artista['redes_sociales_json'] ?? '';
$redesArray = json_decode($redesJson, true);
if (is_array($redesArray)) {
    foreach ($redesArray as $plataforma => $url) {
        if (!empty($url)) {
            $redesSociales[] = [
                'plataforma' => $plataforma,
                'url' => $url
            ];
        }
    }
}

$filtros = [
    'artista_id' => $artista_id,
    'limite' => 50,
    'offset' => 0,
    'orden' => 'reciente'
];
$postsArtista = $modeloPost->obtenerPublicos($filtros);
$totalPosts = $modeloPost->contarPublicos($filtros);

$sql = "SELECT COUNT(*) as total FROM iteraciones i
        JOIN posts p ON i.post_id = p.id
        JOIN proyectos pr ON p.proyecto_id = pr.id
        WHERE p.creador_id = ? AND pr.es_publico = 1";
$stmt = $db->prepare($sql);
$stmt->execute([$artista_id]);
$totalIteraciones = $stmt->fetch()['total'];

$iconosRedes = [
    'instagram' => 'fab fa-instagram',
    'twitter' => 'fab fa-twitter',
    'artstation' => 'fab fa-artstation',
    'behance' => 'fab fa-behance',
    'deviantart' => 'fab fa-deviantart',
    'linkedin' => 'fab fa-linkedin',
    'youtube' => 'fab fa-youtube',
    'tiktok' => 'fab fa-tiktok',
    'twitch' => 'fab fa-twitch',
    'facebook' => 'fab fa-facebook',
    'web' => 'fas fa-globe',
    'portfolio' => 'fas fa-briefcase',
    'otro' => 'fas fa-link'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artista['nombre_artistico'] ?? 'Artista'); ?> | ITERALL</title>
    <?php include 'includes/favicon.php'; ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/perfil-publico.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #0a0a0a;">
    <div class="page-back">
        <a href="explorar.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>

    <!-- Banner -->
    <div class="profile-banner">
        <?php if (!empty($artista['banner_url'])): ?>
            <img src="<?php echo htmlspecialchars($artista['banner_url']); ?>" alt="Banner">
        <?php endif; ?>
        <div class="profile-banner-overlay"></div>
    </div>

    <!-- Info del perfil -->
    <div class="profile-info-container">
        <div class="profile-card">
            <!-- Avatar -->
            <?php if (!empty($artista['avatar_url'])): ?>
                <img src="<?php echo htmlspecialchars($artista['avatar_url']); ?>" 
                     alt="" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar placeholder">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>

            <!-- Detalles -->
            <div class="profile-details">
                <h1><?php echo htmlspecialchars($artista['nombre_artistico'] ?? 'Artista'); ?></h1>
                
                <?php if (!empty($artista['biografia'])): ?>
                    <p class="profile-bio"><?php echo nl2br(htmlspecialchars($artista['biografia'])); ?></p>
                <?php endif; ?>

                <div class="profile-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo $totalPosts; ?></span>
                        <span class="stat-label">Trabajos</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo $totalIteraciones; ?></span>
                        <span class="stat-label">Iteraciones</span>
                    </div>
                </div>

                <?php if (!empty($redesSociales)): ?>
                <div class="profile-redes">
                    <?php foreach ($redesSociales as $red): ?>
                        <a href="<?php echo htmlspecialchars($red['url']); ?>" 
                           class="red-social-link" 
                           target="_blank"
                           title="<?php echo ucfirst($red['plataforma']); ?>">
                            <i class="<?php echo $iconosRedes[$red['plataforma']] ?? 'fas fa-link'; ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Acciones -->
            <div class="profile-actions">
                <a href="#contacto" class="btn-contacto">
                    <i class="fas fa-envelope"></i> Contactar
                </a>
            </div>
        </div>
    </div>

    <!-- Trabajos -->
    <section class="trabajos-section">
        <div class="section-header">
            <h2><i class="fas fa-images"></i> Trabajos Públicos</h2>
            <span class="text-muted"><?php echo $totalPosts; ?> trabajo<?php echo $totalPosts != 1 ? 's' : ''; ?></span>
        </div>

        <?php if (empty($postsArtista)): ?>
            <div class="empty-trabajos">
                <i class="fas fa-folder-open"></i>
                <h3>Sin trabajos públicos aún</h3>
                <p>Este artista no tiene trabajos públicos en este momento</p>
            </div>
        <?php else: ?>
            <div class="trabajos-grid">
                <?php foreach ($postsArtista as $post): ?>
                    <a href="ver_post_publico.php?id=<?php echo $post['id']; ?>" class="trabajo-card">
                        <div class="trabajo-imagen">
                            <?php if (!empty($post['portada'])): ?>
                                <img src="<?php echo htmlspecialchars($post['portada']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['titulo']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="sin-imagen"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <span class="trabajo-iteraciones">
                                <i class="fas fa-layer-group"></i> 
                                <?php echo $post['total_iteraciones']; ?>
                            </span>
                        </div>
                        <div class="trabajo-info">
                            <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                            <?php if (!empty($post['nombre_categoria'])): ?>
                                <span class="trabajo-categoria"><?php echo htmlspecialchars($post['nombre_categoria']); ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Sección Contacto -->
    <section id="contacto" class="contacto-section">
        <div class="contacto-card">
            <div class="contacto-info">
                <h2><i class="fas fa-paper-plane"></i> Contactar a <?php echo htmlspecialchars($artista['nombre_artistico'] ?? 'este artista'); ?></h2>
                <p>¿Te interesa trabajar con este artista? Envía un mensaje para iniciar una conversación.</p>
                
                <?php if (!empty($redesSociales)): ?>
                <p>También puedes encontrar<?php echo ($artista['nombre_artistico'] ? 'lo' : 'le'); ?> en:</p>
                <div class="profile-redes">
                    <?php foreach ($redesSociales as $red): ?>
                        <a href="<?php echo htmlspecialchars($red['url']); ?>" 
                           class="red-social-link" 
                           target="_blank"
                           title="<?php echo ucfirst($red['plataforma']); ?>">
                            <i class="<?php echo $iconosRedes[$red['plataforma']] ?? 'fas fa-link'; ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="contacto-form">
                <form id="formContacto" onsubmit="enviarContacto(event)">
                    <input type="hidden" name="artista_id" value="<?php echo $artista_id; ?>">
                    
                    <div class="form-group">
                        <label for="asunto">Asunto</label>
                        <input type="text" id="asunto" name="asunto" required 
                               placeholder="Ej: Consulta sobre comisión de arte">
                    </div>

                    <div class="form-group">
                        <label for="mensaje">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" required
                                  placeholder="Cuéntale al artista sobre tu proyecto o consulta..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Enviar Mensaje
                    </button>
                </form>
            </div>
        </div>
    </section>

    <script>
        function enviarContacto(e) {
            e.preventDefault();
            
            alert('no pues aun no lo implemente');
            
            document.getElementById('formContacto').reset();
        }
    </script>
</body>
</html>
