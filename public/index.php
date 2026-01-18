<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ITERALL</title>
        <link rel="stylesheet" href="css/style.css">
    </head>

    <body>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Bienvenido de vuelta</h2>
            <p class="subtitle">Inicia sesión para continuar con tus proyectos</p>
            
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

            <form action="procesador.php?action=login" method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="tucorreo@ejemplo.com" required>
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
