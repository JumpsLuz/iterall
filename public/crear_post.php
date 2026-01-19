<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';
require_once '../app/Models/Miniproyecto.php';

if ($_SESSION['rol_id'] != 1) { header('Location: explorar.php'); exit(); }

$miniproyecto_id = $_GET['miniproyecto_id'] ?? null;
$proyecto_id = $_GET['proyecto_id'] ?? null; 

if (!$miniproyecto_id && !$proyecto_id) {
    die("<div class='container'><h3 style='color:red'>Error: Acceso no válido. Debes entrar desde un mini proyecto o proyecto.</h3></div>");
}

$cantidadPostsActuales = 0;
$forzarRenombre = false;

if ($miniproyecto_id) {
    $modeloMini = new Miniproyecto();
    $padre = $modeloMini->obtenerPorId($miniproyecto_id, $_SESSION['usuario_id']);
    
    if ($padre) {
        $nombrePadre = $padre['titulo'];
        $esColeccion = true;
        
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE miniproyecto_id = ?");
        $stmt->execute([$miniproyecto_id]);
        $cantidadPostsActuales = $stmt->fetchColumn();
        
        $forzarRenombre = ($esColeccion && $cantidadPostsActuales == 1);
    } else {
        die("<div class='container'><h3 style='color:red'>Error: Mini proyecto no encontrada.</h3></div>");
    }
} else if ($proyecto_id) {
    $modeloProyecto = new Proyecto();
    $padre = $modeloProyecto->obtenerPorId($proyecto_id, $_SESSION['usuario_id']);
    
    if ($padre) {
        $nombrePadre = $padre['titulo'];
        $esColeccion = false; 
    } else {
        die("<div class='container'><h3 style='color:red'>Error: Proyecto no encontrado.</h3></div>");
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
    <div class="app-layout">
        <?php $active_page = 'crear_post'; include 'includes/sidebar.php'; ?>

        <main class="main-content">
    <div class="container" style="max-width: 800px;">
        <div style="background: #f8f9fa; padding: 10px; border-bottom: 1px solid #ddd;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <img src="https://res.cloudinary.com/dyqubcdf0/image/upload/v1768787599/ITERALL_aneaxn.svg" alt="ITERALL Logo" style="height: 30px; width: auto;">
        </div>
    </div>    <div class="container" style="max-width: 700px;">
        
        <div class="navbar">
            <?php if ($miniproyecto_id): ?>
                <a href="ver_miniproyecto.php?id=<?php echo $miniproyecto_id; ?>" class="btn btn-secondary">← Cancelar</a>
            <?php else: ?>
                <a href="ver_proyecto.php?id=<?php echo $proyecto_id; ?>" class="btn btn-secondary">← Cancelar</a>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>Agregar trabajo a: "<?php echo htmlspecialchars($nombrePadre); ?>"</h2>
                
                <?php if ($esColeccion && $cantidadPostsActuales == 1): ?>
                    <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid var(--accent); padding: 15px; border-radius: var(--radius); margin: 15px 0;">
                        <h4 style="color: var(--accent); margin-bottom: 5px;">✨ ¡Estás creando una Colección!</h4>
                        <p style="font-size: 0.9rem; color: #ddd;">
                            Al agregar un segundo post, "<?php echo htmlspecialchars($nombrePadre); ?>" dejará de verse como un archivo único y se convertirá en un Mini Proyecto.
                            <br><strong>Tip:</strong> Aprovecha de actualizar la descripción del mini proyecto abajo.
                        </p>
                    </div>
                <?php endif; ?>

                <form id="formCrearPost" action="procesador.php?action=crear_post" method="POST" style="margin-top: 20px;">
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

                    <?php include 'includes/category_tags_selector.php'; ?>

                    <?php if ($miniproyecto_id): ?>
                        <hr style="border-color: #444; margin: 20px 0;">
                            <?php if ($forzarRenombre): ?>
                                <div style="background: #252525; padding: 15px; border-radius: var(--radius); border: 1px solid #444;">     
                                    <h4 style="color: var(--primary); margin-bottom: 10px;">📂 Configuración de la Colección</h4>
                                    <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 10px;">
                                        Al agregar un segundo post, esto se convertirá en un Mini Proyecto. 
                                        <strong>Dale un nombre general al mini proyecto</strong> (ej: en lugar de "Boceto 1", ponle "Personaje X").
                                    </p>

                                    <div class="form-group">
                                    <label class="form-label">Título del Mini Proyecto</label>
                                    <input type="text" name="titulo_miniproyecto" class="form-control" 
                                        value="<?php echo htmlspecialchars($nombrePadre); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Descripción del Mini Proyecto</label>
                                        <textarea name="descripcion_miniproyecto" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            <?php endif; ?>
                        
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Guardar Post</button>
                </form>
                
                <script>
                document.getElementById('formCrearPost').addEventListener('submit', function(e) {
                    const checkboxes = document.querySelectorAll('input[name="categorias[]"]');
                    const checkedOne = Array.from(checkboxes).some(cb => cb.checked);
                    
                    if (!checkedOne) {
                        e.preventDefault();
                        alert('Debes seleccionar al menos una categoría');
                        return false;
                    }
                });
                </script>
            </div>
        </div>
    </div>
        </main>
    </div>
</body>
</html>