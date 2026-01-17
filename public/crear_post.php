<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';
require_once '../app/Models/Miniproyecto.php';

if ($_SESSION['rol_id'] != 1) { header('Location: explorar.php'); exit(); }

$miniproyecto_id = $_GET['miniproyecto_id'] ?? null;
$proyecto_id = $_GET['proyecto_id'] ?? null; 

if (!$miniproyecto_id && !$proyecto_id) {
    die("<div class='container'><h3 style='color:red'>Error: Acceso no válido. Debes entrar desde una carpeta.</h3></div>");
}
$tituloPadre = $nombrePadre; 
$forzarRenombre = ($esColeccion && $cantidadPostsActuales == 1);
$esColeccion = false;
$cantidadPostsActuales = 0;

if ($miniproyecto_id) {
    $modeloMini = new Miniproyecto();
    $padre = $modeloMini->obtenerPorId($miniproyecto_id, $_SESSION['usuario_id']);
    if ($padre) {
        $nombrePadre = $padre['titulo'];
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE miniproyecto_id = ?");
        $stmt->execute([$miniproyecto_id]);
        $cantidadPostsActuales = $stmt->fetchColumn();
        $esColeccion = true;
    }
}

$modeloProyecto = new Proyecto();
$categorias = $modeloProyecto->obtenerCategorias();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Trabajo | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 700px;">
        
        <div class="navbar">
            <a href="<?php echo $miniproyecto_id ? 'ver_miniproyecto.php?id='.$miniproyecto_id : 'dashboard_artista.php'; ?>" class="btn btn-secondary">← Cancelar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>Agregar trabajo a: "<?php echo htmlspecialchars($nombrePadre); ?>"</h2>
                
                <?php if ($esColeccion && $cantidadPostsActuales == 1): ?>
                    <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid var(--accent); padding: 15px; border-radius: var(--radius); margin: 15px 0;">
                        <h4 style="color: var(--accent); margin-bottom: 5px;">✨ ¡Estás creando una Colección!</h4>
                        <p style="font-size: 0.9rem; color: #ddd;">
                            Al agregar un segundo post, "<?php echo htmlspecialchars($nombrePadre); ?>" dejará de verse como un archivo único y se convertirá en una carpeta.
                            <br><strong>Tip:</strong> Aprovecha de actualizar la descripción de la carpeta abajo.
                        </p>
                    </div>
                <?php endif; ?>

                <form action="procesador.php?action=crear_post" method="POST" style="margin-top: 20px;">
                    <?php if ($miniproyecto_id): ?>
                        <input type="hidden" name="miniproyecto_id" value="<?php echo $miniproyecto_id; ?>">
                    <?php endif; ?>
                    <?php if ($proyecto_id): ?>
                        <input type="hidden" name="proyecto_id" value="<?php echo $proyecto_id; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Título del Nuevo Post *</label>
                        <input type="text" name="titulo" class="form-control" required placeholder="Ej: Vista Lateral, Render Final...">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Categoría *</label>
                        <select name="categoria_id" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre_categoria']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($miniproyecto_id): ?>
                        <hr style="border-color: #444; margin: 20px 0;">
                        
                        <div style="background: #252525; padding: 15px; border-radius: var(--radius); border: 1px solid #444;">
                            <h4 style="color: var(--primary); margin-bottom: 10px;">📂 Configuración de la Colección</h4>
                            
                            <?php if ($forzarRenombre): ?>
                                <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 10px;">
                                    Al agregar un segundo post, esto se convertirá en una carpeta. 
                                    <strong>Dale un nombre general a la carpeta</strong> (ej: en lugar de "Boceto 1", ponle "Personaje X").
                                </p>
                            <?php endif; ?>

                            <div class="form-group">
                                <label class="form-label">Título de la Carpeta</label>
                                <input type="text" name="titulo_miniproyecto" class="form-control" 
                                    value="<?php echo htmlspecialchars($tituloPadre); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Descripción de la Carpeta</label>
                                <textarea name="descripcion_miniproyecto" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar Post</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>