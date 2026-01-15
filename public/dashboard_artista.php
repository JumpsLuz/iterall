<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Usuario.php';

$usuarioModel = new Usuario();

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM perfiles WHERE usuario_id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
    <body>
    <h1>Bienvenido, Artista #<?php echo $_SESSION['usuario_id']; ?></h1>
    <a href="procesador.php?action=logout">Cerrar Sesión</a>
    <hr>

    <h3>Completar mi Perfil Artístico</h3>
    <form action="procesador.php?action=actualizar_perfil" method="POST">
        <input type="text" name="nombre_artistico" placeholder="Tu nombre artístico" 
               value="<?php echo $perfil['nombre_artistico'] ?? ''; ?>" required><br><br>
        
        <textarea name="biografia" placeholder="Cuéntanos sobre ti..."><?php echo $perfil['biografia'] ?? ''; ?></textarea><br><br>
        
        <h4>Redes Sociales (JSON)</h4>
        <input type="text" name="redes[instagram]" placeholder="Instagram" value="<?php echo json_decode($perfil['redes_sociales_json'], true)['instagram'] ?? ''; ?>">
        <input type="text" name="redes[artstation]" placeholder="ArtStation" value="<?php echo json_decode($perfil['redes_sociales_json'], true)['artstation'] ?? ''; ?>">
        <br><br>
        
        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>