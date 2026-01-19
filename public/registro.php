<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .logo-dark { filter: brightness(0) invert(1); }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="https://res.cloudinary.com/dyqubcdf0/image/upload/v1768787917/ITERALL_NAME_ujwlge.svg" alt="ITERALL" style="max-width: 200px; height: auto; filter: brightness(0) invert(1);">
            </div>
            <h2>Crear cuenta en ITERALL</h2>
            <p class="subtitle">Comienza a organizar tu trabajo creativo</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    switch($_GET['error']) {
                        case 'email_exists':
                            echo 'Este email ya está registrado. <a href="index.php" style="color: var(--danger); text-decoration: underline;">Inicia sesión aquí</a>';
                            break;
                        case '1':
                            echo 'Error al registrar. Intenta nuevamente.';
                            break;
                        default:
                            echo 'Error desconocido.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form action="procesador.php?action=registrar" method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="tucorreo@ejemplo.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label">¿Cómo usarás ITERALL?</label>
                    <select name="rol_id" class="form-control" required>
                        <option value="">-- Selecciona una opción --</option>
                        <option value="1">Soy Artista</option>
                        <option value="2">Soy Cliente</option>
                    </select>
                    <span class="form-hint">Los artistas pueden crear y gestionar proyectos</span>
                </div>
                
                <button type="submit" class="btn btn-primary">Crear Cuenta</button>
            </form>

            <div class="auth-link">
                ¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a>
            </div>
        </div>
    </div>
</body>
</html>
