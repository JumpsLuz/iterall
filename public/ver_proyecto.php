<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';

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
?>
<!DOCTYPE html>
<html>
    <body>
        <h1><?php echo htmlspecialchars($proyecto['titulo']); ?></h1>
    <p>
        <a href="mis_proyectos.php">← Volver</a> | 
        <a href="editar_proyecto.php?id=<?php echo $proyecto['id']; ?>">Editar</a>
    </p>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'actualizado'): ?>
        <p><strong>Proyecto actualizado correctamente.</strong></p>
    <?php endif; ?>

    <hr>

    <p>
        <strong>Categoría:</strong> <?php echo htmlspecialchars($proyecto['nombre_categoria']); ?><br>
        <strong>Estado:</strong> <?php echo htmlspecialchars($proyecto['nombre_estado']); ?><br>
        <strong>Visibilidad:</strong> <?php echo $proyecto['es_publico'] ? 'Público' : 'Privado'; ?>
    </p>

    <?php if (!empty($proyecto['descripcion'])): ?>
    <p>
        <strong>Descripción:</strong><br>
        <?php echo nl2br(htmlspecialchars($proyecto['descripcion'])); ?>
    </p>
    <?php endif; ?>

    <p>
        <strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($proyecto['fecha_creacion'])); ?><br>
        <strong>Última actualización:</strong> <?php echo date('d/m/Y H:i', strtotime($proyecto['fecha_actualizacion'])); ?>
    </p>

    <hr>

    <h2>Mini-proyectos</h2>
    <p><em>Aún no hay mini-proyectos en este proyecto. (Esta funcionalidad estará disponible próximamente)</em></p>

    <hr>

    <h2>Posts del proyecto</h2>
    <p><em>Aún no hay posts en este proyecto. (Esta funcionalidad estará disponible próximamente)</em></p>
</body>
</html>