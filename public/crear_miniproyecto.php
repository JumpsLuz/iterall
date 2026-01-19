<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';

if ($_SESSION['rol_id'] != 1) { header('Location: explorar.php'); exit(); }

$proyecto_id = $_GET['proyecto_id'] ?? null; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Mini Proyecto | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-layout">
        <?php $active_page = 'crear_mini'; include 'includes/sidebar.php'; ?>

        <main class="main-content">
    <div class="container" style="max-width: 600px;">
        <div class="navbar">
            <a href="dashboard_artista.php" class="btn btn-secondary">← Cancelar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>📂 Crear Nuevo Mini Proyecto</h2>
                <p class="text-muted">Un mini proyecto te permite agrupar múltiples posts (bocetos, referencias, finales) bajo un mismo nombre.</p>

                <form action="procesador.php?action=crear_miniproyecto" method="POST">
                    
                    <?php if ($proyecto_id): ?>
                        <input type="hidden" name="proyecto_id" value="<?php echo htmlspecialchars($proyecto_id); ?>">
                        <div class="badge badge-category" style="margin-bottom:15px;">↳ Dentro de Proyecto Grande</div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Nombre del Mini Proyecto *</label>
                        <input type="text" name="titulo" class="form-control" required placeholder="Ej: Diseño de Personaje - Guerrero">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="¿Qué contendrá esta mini proyecto?"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Crear Mini Proyecto Vacío</button>
                </form>
            </div>
        </div>
    </div>
        </main>
    </div>
</body>
</html>