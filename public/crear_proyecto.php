<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';

if ($_SESSION['rol_id'] != 1) {
    header('Location: explorar.php');
    exit();
}

$modeloProyecto = new Proyecto();
$categorias = $modeloProyecto->obtenerCategorias();
$estados = $modeloProyecto->obtenerEstados();
?>
<!DOCTYPE html>
<html>
    <body>
        <h2>Crear Nuevo Proyecto</h2>

        <?php if (isset($_GET['error'])): ?>
            Hubo un error al crear el proyecto. Por favor, inténtalo de nuevo.
        <?php endif; ?>

        <form action="procesador.php?action=crear_proyecto" method="POST">
            <label>Titulo del Proyecto *</label>
            <input type="text" name="titulo" required><br><br>

            <label>Descripción</label>
            <textarea name="descripcion" placeholder="Describe tu proyecto..."></textarea><br><br>

            <label>Categoría *</label>
            <select name="categoria_id" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>">
                        <?php echo $categoria['nombre_categoria']; ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Estado *</label>
            <select name="estado_id" required>
                <option value="">Selecciona un estado</option>
                <?php foreach ($estados as $estado): ?>
                    <option value="<?php echo $estado['id']; ?>">
                        <?php echo $estado['nombre_estado']; ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <input type="checkbox" name="es_publico" value="1">
            <label for="es_publico">Hacer proyecto público</label><br><br>
            
            <button type="submit">Crear Proyecto</button>
            <a href="mis_proyectos.php"><button type="button">Cancelar</button></a>
        </form>
    </body>
</html>