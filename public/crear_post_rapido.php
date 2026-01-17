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
?>

<!DOCTYPE html>
<html>
    <body>
        <h1>Publicar Nuevo Post</h1>
        <p>Este post se creará automáticamente dentro de su propia carpeta.</p>

        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;">
                <?php 
                switch($_GET['error']) {
                    case 'campos_vacios':
                        echo 'Debes completar todos los campos obligatorios.';
                        break;
                    case 'db_error':
                        echo 'Error al guardar. Intenta nuevamente.';
                        break;
                    default:
                        echo 'Error desconocido.';
                }
                ?>
            </p>
        <?php endif; ?>

        <form action="procesador.php?action=crear_post_rapido" method="POST">
            
            <label>Título de la obra *</label><br>
            <input type="text" name="titulo" required><br><br>

            <label>Categoría *</label><br>
            <select name="categoria_id" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Descripción (Opcional)</label><br>
            <textarea name="descripcion" placeholder="Explica de qué trata esta pieza..."></textarea><br><br>


            <button type="submit">Publicar Ahora</button>
            <a href="dashboard_artista.php">Cancelar</a>
        </form>
    </body>
</html>