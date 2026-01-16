<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';
require_once '../app/Models/Miniproyecto.php';

if ($_SESSION['rol_id'] != 1) { header('Location: explorar.php'); exit(); }

$miniproyecto_id = $_GET['miniproyecto_id'] ?? null;
$proyecto_id = $_GET['proyecto_id'] ?? null; 

if (!$miniproyecto_id && !$proyecto_id) {
    die("Error: Debes acceder a esta página desde una carpeta o proyecto.");
}

$nombrePadre = "Carpeta desconocida";
if ($miniproyecto_id) {
    $modeloMini = new Miniproyecto();
    $padre = $modeloMini->obtenerPorId($miniproyecto_id, $_SESSION['usuario_id']);
    if ($padre) $nombrePadre = $padre['titulo'];
}

$modeloProyecto = new Proyecto();
$categorias = $modeloProyecto->obtenerCategorias();
?>
<!DOCTYPE html>
<html>
    <body>
        
        <h2>Agregar nuevo trabajo a: "<?php echo htmlspecialchars($nombrePadre); ?>"</h2>
        
        <form action="procesador.php?action=crear_post" method="POST">
            <?php if ($miniproyecto_id): ?>
                <input type="hidden" name="miniproyecto_id" value="<?php echo $miniproyecto_id; ?>">
            <?php endif; ?>
            
            <?php if ($proyecto_id): ?>
                <input type="hidden" name="proyecto_id" value="<?php echo $proyecto_id; ?>">
            <?php endif; ?>

            <label>Título del Post *</label><br>
            <input type="text" name="titulo" required><br><br>

            <label>Categoría *</label><br>
            <select name="categoria_id" required>
                <option value="">Selecciona...</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Descripción (Opcional)</label><br>
            <textarea name="descripcion" placeholder="Notas sobre esta pieza..."></textarea><br><br>

            <button type="submit">Guardar Post</button>
            <a href="<?php echo $miniproyecto_id ? 'ver_miniproyecto.php?id='.$miniproyecto_id : 'dashboard_artista.php'; ?>">
                Cancelar
            </a>
        </form>
    </body>
</html>