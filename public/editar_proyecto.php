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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Proyecto | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 800px;">
        
        <div class="navbar">
            <a href="ver_proyecto.php?id=<?php echo $proyecto['id']; ?>" class="btn btn-secondary">← Volver al Proyecto</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>⚙️ Configuración del Proyecto</h2>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="badge badge-status" style="background: rgba(239,68,68,0.2); color: var(--danger); display:block; margin-bottom: 15px;">
                        ⚠️ Error: Hubo un error al actualizar el proyecto. Intenta nuevamente.
                    </div>
                <?php endif; ?>

                <form action="procesador.php?action=editar_proyecto" method="POST">
                    <input type="hidden" name="proyecto_id" value="<?php echo $proyecto['id']; ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Título del Proyecto *</label>
                        <input type="text" name="titulo" class="form-control" required maxlength="255" 
                               value="<?php echo htmlspecialchars($proyecto['titulo']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4" placeholder="Describe tu proyecto..."><?php echo htmlspecialchars($proyecto['descripcion']); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Categoría *</label>
                            <select name="categoria_id" class="form-control" required>
                                <option value="">-- Selecciona una categoría --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo ($cat['id'] == $proyecto['categoria_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Estado *</label>
                            <select name="estado_id" class="form-control" required>
                                <option value="">-- Selecciona un estado --</option>
                                <?php foreach ($estados as $est): ?>
                                    <option value="<?php echo $est['id']; ?>"
                                            <?php echo ($est['id'] == $proyecto['estado_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($est['nombre_estado']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="es_publico" value="1"
                                   <?php echo $proyecto['es_publico'] ? 'checked' : ''; ?>>
                            <span>
                                <strong>Hacer público este proyecto</strong>
                                <br><small class="text-muted">Si no lo marcas, solo tú podrás verlo.</small>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Guardar Cambios</button>
                </form>
            </div>
        </div>

        <hr style="margin: 40px 0;">

        <div class="card" style="border-color: var(--danger);">
            <div class="card-body">
                <h3 style="color: var(--danger);">🗑️ Zona Peligrosa</h3>
                <p class="text-muted" style="margin: 15px 0;">
                    Esta acción eliminará permanentemente el proyecto y TODO su contenido: miniproyectos, posts, iteraciones e imágenes.
                    <strong>Esta acción no se puede deshacer.</strong>
                </p>
                
                <form action="procesador.php?action=eliminar_proyecto" method="POST" 
                      onsubmit="return confirm('⚠️ ADVERTENCIA FINAL\n\n¿Estás COMPLETAMENTE SEGURO de eliminar este proyecto?\n\nSe perderá:\n- Todos los miniproyectos\n- Todos los posts\n- Todas las iteraciones\n- Todas las imágenes\n\nEsta acción es IRREVERSIBLE.');"
                      style="margin-top: 15px;">
                    <input type="hidden" name="proyecto_id" value="<?php echo $proyecto['id']; ?>">
                    <button type="submit" class="btn btn-danger" style="width: 100%; padding: 12px;">
                        Eliminar Proyecto Definitivamente
                    </button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>