<?php
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>ITERALL</title>
    </head>

    <body>
        <header>
            <h1>ITERALL</h1>
            <p>Organiza, itera y profesionaliza tu arte.</p>
        </header>
        <main>
            <?php if (!isset($_SESSION['usuario_id'])): ?>
                <section>
                    
                    <h2>Bienvenido</h2>
                    <a href="login.php">Iniciar Sesión</a>
                    <a href="registro.php">Registrarse Gratis</a>

                </section>
            <?php else: ?>
                <p>Ya tienes una sesión activa. <a href="todavianohagoesto.php?action=go_home">Ir a mi Panel</a></p>
                // tengo que mejorar el manejo de redirecciones
            <?php endif; ?>
        </main>
    </body>
</html>
