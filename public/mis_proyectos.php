<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';

if ($_SESSION['rol_id'] != 1) {
    header('Location: explorar.php');
    exit();
}

$modeloProyecto = new Proyecto();
$proyectos = $modeloProyecto->obtenerPorUsuario($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html>
    <body>
        <h1>Mis Proyectos</h1>
        <a href="dashboard_artista.php" class="btn-dashboard">Volver al Dashboard</a>
        <a href="crear_proyecto.php" class="btn-crear">Nuevo Proyecto</a><br><br>
        <?php if (isset($_GET['mensaje'])): ?>
            <?php
            switch($_GET['mensaje']) {
                case 'proyecto_creado':
                    echo 'Proyecto creado exitosamente';
                    break;
                case 'proyecto_eliminado':
                    echo 'Proyecto eliminado correctamente.';
                    break;
                default:
                    echo 'Operación realizada con éxito.';
            }
            ?>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                Hubo un error al procesar la solicitud.
            </div>
        <?php endif; ?>

        <?php if (empty($proyectos)): ?>
            <h3>No tienes proyectos creados aún.</h3>
            <p>¡Comienza creando tu primer proyecto!</p>
            <a href="crear_proyecto.php" class="btn-crear">Crear Proyecto</a>
        <?php else: ?>
            <?php foreach ($proyectos as $proyecto): ?>
                <div class="proyecto-item">
                    <h3><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($proyecto['descripcion'])); ?></p>
                    <a href="ver_proyecto.php?id=<?php echo $proyecto['id']; ?>">Ver Detalles</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
</html>