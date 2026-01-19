<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';

if ($_SESSION['rol_id'] != 1) { header('Location: explorar.php'); exit(); }

$modeloProyecto = new Proyecto();
$categorias = $modeloProyecto->obtenerCategorias();
$proyecto_id = $_GET['proyecto_id'] ?? null; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Post Rápido | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-layout">
        <?php $active_page = 'crear_post'; include 'includes/sidebar.php'; ?>

        <main class="main-content">
    <div class="container" style="max-width: 600px;">
        
        <div class="navbar">
            <a href="dashboard_artista.php" class="btn btn-secondary">← Cancelar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 style="margin-bottom: 5px;"><i class="fas fa-rocket"></i> Publicar Nueva Obra</h2>
                <p class="text-muted" style="margin-bottom: 20px;">
                    Esto creará una entrada individual. Si luego añades más archivos, se convertirá automáticamente en un Mini Proyecto.
                </p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="badge badge-status" style="background: rgba(239,68,68,0.2); color: var(--danger); display:block; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle"></i> Error: Completa todos los campos obligatorios.
                    </div>
                <?php endif; ?>

                <form id="formCrearPostRapido" action="procesador.php?action=crear_post_rapido" method="POST">
                    
                    <?php if($proyecto_id): ?>
                        <input type="hidden" name="proyecto_id" value="<?php echo htmlspecialchars($proyecto_id); ?>">
                        <div class="badge badge-category" style="margin-bottom: 15px;">
                            ↳ Agregando dentro de un Proyecto Grande
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Título de la Obra *</label>
                        <input type="text" name="titulo" class="form-control" placeholder="Ej: Boceto Personaje Principal" required>
                    </div>

                    <?php include 'includes/category_tags_selector.php'; ?>

                    <div class="form-group">
                        <label class="form-label">Descripción Inicial (Opcional)</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Notas sobre esta pieza..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Publicar Ahora</button>
                </form>
                
                <script>
                document.getElementById('formCrearPostRapido').addEventListener('submit', function(e) {
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