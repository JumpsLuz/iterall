<?php require_once '../app/Config/auth_check.php'; ?>
<!DOCTYPE html>
<html>
    <body>
        <h2>Configura tu perfil para empezar.</h2>
        
        <form action="procesador.php?action=actualizar_perfil" method="POST">
            <label>Nombre Artístico:</label>
            <input type="text" name="nombre_artistico" required><br><br>

            <label>Biofrafía</label>
            <textarea name="biografia"></textarea><br><br>

            <label>Avatar</label><br><br>
            <!-- Implementar carga de imagen para avatary y banner despues -->
            <label>Banner</label><br><br>

            <button type="submit">Guardar Perfil</button>
        </form>
    </body>
</html>
