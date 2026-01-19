<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Usuario.php';

$usuario_id = $_SESSION['usuario_id'];
$rol_id = $_SESSION['rol_id'];

if ($rol_id != 2) {
    header('Location: editar_perfil.php');
    exit();
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT u.*, p.nombre_artistico, p.avatar_url FROM usuarios u LEFT JOIN perfiles p ON u.id = p.usuario_id WHERE u.id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convertirse en Artista | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/convertir-artista.css">
</head>
<body>
    <div class="app-layout">
        <?php $active_page = 'editar_perfil'; include 'includes/sidebar_cliente.php'; ?>

        <main class="main-content">
            <div class="upgrade-container">
                
                <!-- Hero Section -->
                <div class="upgrade-hero">
                    <h1><i class="fas fa-palette"></i> Conviértete en Artista</h1>
                    <p>
                        Desbloquea todas las herramientas para crear tu portafolio profesional, 
                        compartir tu proceso creativo y conectar con la comunidad artística.
                    </p>
                </div>
                
                <!-- Benefits Grid -->
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <h3>Portafolio Profesional</h3>
                        <p>Crea un portafolio impresionante con proyectos, posts y miniaturas de alta calidad.</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h3>Documenta tu Proceso</h3>
                        <p>Muestra las iteraciones de tu trabajo y el proceso creativo detrás de cada pieza.</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3>Organización Flexible</h3>
                        <p>Organiza tu trabajo en proyectos y mini-proyectos con categorías y etiquetas.</p>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Perfil Público</h3>
                        <p>Ten un perfil público donde reclutadores y clientes puedan ver todo tu trabajo.</p>
                    </div>
                </div>
                
                <!-- Action Section -->
                <div class="action-section">
                    <div class="current-account">
                        <div class="current-avatar">
                            <?php if (!empty($usuario['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($usuario['avatar_url']); ?>" alt="Avatar">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="current-info">
                            <strong><?php echo htmlspecialchars($usuario['nombre_artistico'] ?? $usuario['email']); ?></strong>
                            <span>Cuenta actual: Cliente / Reclutador</span>
                        </div>
                    </div>
                    
                    <h2>¿Listo para comenzar?</h2>
                    <p>
                        Tu cuenta será actualizada a Artista y podrás configurar tu perfil completo 
                        con nombre artístico, bio, redes sociales y más.
                    </p>
                    
                    <a href="completar_perfil.php?upgrade=1" class="btn-upgrade">
                        <i class="fas fa-rocket"></i>
                        Convertirme en Artista
                    </a>
                    
                    <br>
                    <a href="explorar.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver a explorar
                    </a>
                </div>
                
            </div>
        </main>
    </div>
</body>
</html>
