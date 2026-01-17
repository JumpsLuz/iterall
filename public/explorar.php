<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="explorer-container">
        <div class="explorer-header">
            <h1>Panel de Exploración</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['email']); ?></p>
        </div>

        <div class="welcome-message">
            <h3 style="margin-bottom: 10px;">Proximamente</h3>
            <p style="color: var(--text-muted); margin: 0;">
                despues
            </p>
        </div>

        <div class="navbar">
            <a href="procesador.php?action=logout" class="btn btn-danger">Cerrar Sesión</a>
        </div>

        <hr>
    </div>
</body>
</html>