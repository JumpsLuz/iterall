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

// Obtener redes sociales del artista (desde perfiles.redes_sociales_json)
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

// Obtener posts públicos del artista
$filtros = [
    'artista_id' => $artista_id,
    'limite' => 50,
    'offset' => 0,
    'orden' => 'reciente'
];
$postsArtista = $modeloPost->obtenerPublicos($filtros);
$totalPosts = $modeloPost->contarPublicos($filtros);

// Contar iteraciones totales
$sql = "SELECT COUNT(*) as total FROM iteraciones i
        JOIN posts p ON i.post_id = p.id
        JOIN proyectos pr ON p.proyecto_id = pr.id
        WHERE p.creador_id = ? AND pr.es_publico = 1";
$stmt = $db->prepare($sql);
$stmt->execute([$artista_id]);
$totalIteraciones = $stmt->fetch()['total'];

// Iconos de redes sociales
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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-banner {
            height: 280px;
            background: linear-gradient(135deg, #1a1a2e, #0f0f0f);
            position: relative;
            border-radius: 0 0 20px 20px;
            overflow: hidden;
        }

        .profile-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-banner-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        }

        .profile-info-container {
            max-width: 1200px;
            margin: -80px auto 0;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }

        .profile-card {
            background: #141414;
            border: 1px solid #222;
            border-radius: 20px;
            padding: 30px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 30px;
            align-items: start;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid var(--primary);
            object-fit: cover;
            background: #1a1a1a;
        }

        .profile-avatar.placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #444;
        }

        .profile-details h1 {
            margin: 0 0 10px;
            font-size: 2rem;
        }

        .profile-bio {
            color: var(--text-muted);
            margin-bottom: 15px;
            line-height: 1.6;
            max-width: 600px;
        }

        .profile-stats {
            display: flex;
            gap: 25px;
            margin-bottom: 20px;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .profile-redes {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .red-social-link {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 1.1rem;
        }

        .red-social-link:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            transform: translateY(-3px);
        }

        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-contacto {
            padding: 12px 25px;
            background: var(--primary);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-contacto:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }

        /* Sección de trabajos */
        .trabajos-section {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-header h2 {
            margin: 0;
        }

        .trabajos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .trabajo-card {
            background: #141414;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #222;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .trabajo-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
        }

        .trabajo-imagen {
            aspect-ratio: 4 / 3;
            overflow: hidden;
            position: relative;
        }

        .trabajo-imagen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .trabajo-card:hover .trabajo-imagen img {
            transform: scale(1.05);
        }

        .trabajo-imagen .sin-imagen {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1a1a1a;
            color: #444;
            font-size: 3rem;
        }

        .trabajo-iteraciones {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #fff;
        }

        .trabajo-info {
            padding: 15px;
        }

        .trabajo-info h3 {
            margin: 0 0 5px;
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .trabajo-categoria {
            font-size: 0.8rem;
            color: var(--primary);
        }

        /* Contacto Modal */
        .contacto-section {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px 40px;
        }

        .contacto-card {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 227, 194, 0.05));
            border: 1px solid #333;
            border-radius: 20px;
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .contacto-info h2 {
            margin: 0 0 15px;
        }

        .contacto-info p {
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .contacto-form .form-group {
            margin-bottom: 20px;
        }

        .contacto-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .contacto-form input,
        .contacto-form textarea {
            width: 100%;
            padding: 12px 15px;
            background: #0f0f0f;
            border: 1px solid #333;
            border-radius: 10px;
            color: var(--text-main);
            font-size: 1rem;
        }

        .contacto-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .contacto-form input:focus,
        .contacto-form textarea:focus {
            border-color: var(--primary);
            outline: none;
        }

        /* Empty state */
        .empty-trabajos {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-trabajos i {
            font-size: 4rem;
            color: #333;
            margin-bottom: 15px;
        }

        /* Header fijo */
        .page-back {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        .back-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid #333;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        @media (max-width: 900px) {
            .profile-card {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .profile-avatar {
                margin: 0 auto;
            }
            
            .profile-stats {
                justify-content: center;
            }
            
            .profile-redes {
                justify-content: center;
            }
            
            .profile-actions {
                flex-direction: row;
                justify-content: center;
            }
            
            .contacto-card {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .profile-banner {
                height: 180px;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
            }
            
            .profile-details h1 {
                font-size: 1.5rem;
            }
            
            .trabajos-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
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
