<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Usuario.php';

$usuario_id = $_SESSION['usuario_id'];
$rol_id = $_SESSION['rol_id'];

// Solo permitir acceso a clientes
if ($rol_id != 2) {
    header('Location: editar_perfil.php');
    exit();
}

// Obtener datos del usuario
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
    <style>
        .upgrade-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .upgrade-hero {
            text-align: center;
            padding: 60px 40px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(212, 175, 55, 0.1));
            border-radius: 20px;
            margin-bottom: 40px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .upgrade-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .upgrade-hero p {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .benefit-card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .benefit-card:hover {
            transform: translateY(-5px);
        }
        
        .benefit-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .benefit-icon i {
            font-size: 28px;
            color: #fff;
        }
        
        .benefit-card h3 {
            color: #fff;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .benefit-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .action-section {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
        }
        
        .action-section h2 {
            color: #fff;
            margin-bottom: 15px;
        }
        
        .action-section p {
            color: var(--text-muted);
            margin-bottom: 30px;
        }
        
        .btn-upgrade {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 18px 40px;
            font-size: 1.2rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
        }
        
        .btn-upgrade:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.4);
        }
        
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            color: var(--text-muted);
            text-decoration: none;
        }
        
        .btn-back:hover {
            color: #fff;
        }
        
        .current-account {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 20px;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .current-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .current-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .current-avatar i {
            color: #fff;
            font-size: 20px;
        }
        
        .current-info strong {
            display: block;
            color: #fff;
        }
        
        .current-info span {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
    </style>
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
