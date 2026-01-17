<?php require_once '../vendor/autoload.php'; ?>
<!DOCTYPE html>
<html>
    <body>
        <h2>Iniciar Sesión</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;">
                <?php 
                switch($_GET['error']) {
                    case '1':
                        echo 'Credenciales incorrectas.';
                        break;
                    case 'sesion_requerida':
                        echo 'Debes iniciar sesión primero.';
                        break;
                    default:
                        echo 'Error desconocido.';
                }
                ?>
            </p>
        <?php endif; ?>

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'sesion_cerrada'): ?>
            <p style="color: green;">✓ Sesión cerrada correctamente.</p>
        <?php endif; ?>

        <form action="procesador.php?action=login" method="POST">
            <input type="email" name="email" placeholder="tucorreo@aqui.com" required><br><br>
            <input type="password" name="password" placeholder="Contraseña" required><br><br>
            <button type="submit">Iniciar Sesión</button>
            <a href="registro.php">¿No tienes cuenta? Regístrate aquí.</a>
        </form>
    </body>
</html>