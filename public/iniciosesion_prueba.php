<?php require_once '../vendor/autoload.php'; ?>
<!DOCTYPE html>
<html>
    <body>
        <h2>Iniciar Sesión</h2>
        <form action="procesador.php?action=login" method="POST">
            <input type="email" name="email" placeholder="tucorreo@aqui.com" required><br><br>
            <input type="password" name="password" placeholder="Contraseña" required><br><br>
            <button type="submit">Iniciar Sesión</button>
        </form>
    </body>
</html>