<?php require_once '../vendor/autoload.php'; ?>
<!DOCTYPE html>
<html>
    <body>
        <h2>Registro de Usuario</h2>
        <form action="procesador.php?action=registrar" method="POST">
            <input type="email" name="email" placeholder="tucorreo@aqui.com" required><br><br>
            <input type="password" name="password" placeholder="Contraseña" required><br><br>

            <label>Rol:</label>
            <select name="rol_id" required>
                <option value="1">Artista</option>
                <option value="2">Cliente</option>
            </select><br><br>
            <button type="submit">Registrar</button>
            <a href="login.php">¿Ya tienes cuenta? Inicia sesión aquí.</a>
        </form>
    </body>
</html>