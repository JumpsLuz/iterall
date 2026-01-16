<?php 
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';

if ($_SESSION['rol_id'] != 1) {
    header('Location: explorar.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: mis_proyectos.php');
    exit();
}

$modeloProyecto = new Proyecto();
$proyecto = $modeloProyecto->obtenerPorId($_GET['id'], $_SESSION['usuario_id']);

if (!$proyecto) {
    header('Location: mis_proyectos.php?error=proyecto_no_encontrado');
    exit();
}

$categorias = $modeloProyecto->obtenerCategorias();
$estados = $modeloProyecto->obtenerEstados();
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Editar Proyecto</h2>
    
    <?php if (isset($_GET['error'])): ?>
        <p><strong>Error:</strong> Hubo un error al actualizar el proyecto. Intenta nuevamente.</p>
    <?php endif; ?>

    <form action="procesador.php?action=editar_proyecto" method="POST">
        <input type="hidden" name="proyecto_id" value="<?php echo $proyecto['id']; ?>">
        
        <label>Título del Proyecto *</label><br>
        <input type="text" name="titulo" required maxlength="255" 
               value="<?php echo htmlspecialchars($proyecto['titulo']); ?>"><br><br>

        <label>Descripción</label><br>
        <textarea name="descripcion" placeholder="Describe tu proyecto..."><?php echo htmlspecialchars($proyecto['descripcion']); ?></textarea><br><br>

        <label>Categoría *</label><br>
        <select name="categoria_id" required>
            <option value="">-- Selecciona una categoría --</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" 
                        <?php echo ($cat['id'] == $proyecto['categoria_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Estado *</label><br>
        <select name="estado_id" required>
            <option value="">-- Selecciona un estado --</option>
            <?php foreach ($estados as $est): ?>
                <option value="<?php echo $est['id']; ?>"
                        <?php echo ($est['id'] == $proyecto['estado_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($est['nombre_estado']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <input type="checkbox" name="es_publico" id="es_publico" value="1"
               <?php echo $proyecto['es_publico'] ? 'checked' : ''; ?>>
        <label for="es_publico">Hacer público este proyecto</label><br><br>

        <button type="submit">Guardar Cambios</button>
        <a href="ver_proyecto.php?id=<?php echo $proyecto['id']; ?>">
            <button type="button">Cancelar</button>
        </a>
    </form>
</body>
</html>