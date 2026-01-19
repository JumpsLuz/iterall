<?php
session_start();

// Verificar si ya hay una sesión activa
$sesionActiva = isset($_SESSION['usuario_id']) && isset($_SESSION['rol_id']);
$redirectUrl = '';
if ($sesionActiva) {
    // Determinar a dónde redirigir según el rol
    $redirectUrl = ($_SESSION['rol_id'] == 2) ? 'explorar.php' : 'dashboard_artista.php';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ITERALL</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="css/auth.css">
    <body>
    <div class="auth-container">
        <div class="auth-card">
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="https://res.cloudinary.com/dyqubcdf0/image/upload/v1768787917/ITERALL_NAME_ujwlge.svg" alt="ITERALL" style="max-width: 200px; height: auto; filter: brightness(0) invert(1);">
            </div>
            
            <?php if ($sesionActiva): ?>
                <!-- Usuario ya tiene sesión activa -->
                <div class="session-active">
                    <p class="welcome-back">
                        <i class="fas fa-hand-wave"></i> ¡Hola de nuevo! Ya tienes una sesión activa.
                    </p>
                    <a href="<?php echo $redirectUrl; ?>" class="btn-go">
                        <i class="fas fa-rocket"></i> Ir a ITERALL
                    </a>
                    <a href="procesador.php?action=logout" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión e iniciar con otra cuenta
                    </a>
                </div>
                
                <div class="divider"><span>o inicia con otra cuenta</span></div>
            <?php else: ?>
                <h2>Bienvenido de vuelta</h2>
                <p class="subtitle">Inicia sesión para continuar con tus proyectos</p>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    switch($_GET['error']) {
                        case '1':
                            echo '<i class="fas fa-exclamation-triangle"></i> Credenciales incorrectas. Verifica tu email y contraseña.';
                            break;
                        case 'sesion_requerida':
                            echo '<i class="fas fa-exclamation-triangle"></i> Debes iniciar sesión primero.';
                            break;
                        case 'sesion_expirada':
                            echo '<i class="fas fa-exclamation-triangle"></i> Tu sesión ha expirado. Por favor, inicia sesión nuevamente.';
                            break;
                        default:
                            echo '<i class="fas fa-exclamation-triangle"></i> Error desconocido. Intenta nuevamente.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'sesion_cerrada'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check"></i> Sesión cerrada correctamente.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'cuenta_eliminada'): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Tu cuenta ha sido eliminada permanentemente.
                </div>
            <?php endif; ?>

            <form action="procesador.php?action=login" method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="tucorreo@ejemplo.com" required maxlength="255">
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </form>

            <div class="auth-link">
                ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
            </div>
        </div>
    </div>
</body>
</html>
