<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';

if ($_SESSION['rol_id'] != 1) { header('Location: explorar.php'); exit(); }

$modeloProyecto = new Proyecto();
$categorias = $modeloProyecto->obtenerCategorias();
$estados = $modeloProyecto->obtenerEstados();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Proyecto | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container" style="max-width: 800px;">
        
        <div class="navbar">
            <a href="mis_proyectos.php" class="btn btn-secondary">← Cancelar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div style="border-left: 4px solid var(--primary); padding-left: 15px; margin-bottom: 20px;">
                    <h2 style="margin:0;">Crear Proyecto Principal</h2>
                    <p class="text-muted">Utiliza esto para trabajos a gran escala que contendrán múltiples mini proyectos (Ej: Desarrollo de Videojuego, Cómic Completo).</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> Error al crear el proyecto. Verifica los campos requeridos.
                    </div>
                <?php endif; ?>

                <form action="procesador.php?action=crear_proyecto" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label class="form-label">Título del Proyecto *</label>
                        <input type="text" name="titulo" class="form-control" required placeholder="Ej: Proyecto Titán">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Categoría General *</label>
                            <select name="categoria_id" class="form-control" required>
                                <option value="">Selecciona...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre_categoria']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Estado Inicial *</label>
                            <select name="estado_id" class="form-control" required>
                                <?php foreach ($estados as $est): ?>
                                    <option value="<?php echo $est['id']; ?>"><?php echo htmlspecialchars($est['nombre_estado']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción General</label>
                        <textarea name="descripcion" class="form-control" rows="4" placeholder="¿De qué trata este proyecto?"></textarea>
                    </div>

                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                        <h3 style="margin-bottom: 20px;">🖼️ Imágenes del Proyecto</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Portada/Avatar (400x400px)</label>
                                <div class="image-preview" id="avatarPreview" onclick="document.getElementById('avatarInput').click()">
                                    <div class="placeholder-text">
                                        <p style="font-size: 2rem;"><i class="fas fa-palette"></i></p>
                                        <p>Click para seleccionar</p>
                                        <p class="text-muted" style="font-size: 0.85rem;">Opcional | Máx. 5MB</p>
                                    </div>
                                    <img id="avatarImg" alt="Vista previa portada">
                                </div>
                                <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Banner (1500x500px)</label>
                                <div class="image-preview" id="bannerPreview" onclick="document.getElementById('bannerInput').click()">
                                    <div class="placeholder-text">
                                        <p style="font-size: 2rem;"><i class="fas fa-image"></i></p>
                                        <p>Click para seleccionar</p>
                                        <p class="text-muted" style="font-size: 0.85rem;">Opcional | Máx. 5MB</p>
                                    </div>
                                    <img id="bannerImg" alt="Vista previa banner">
                                </div>
                                <input type="file" id="bannerInput" name="banner" accept="image/*" style="display: none;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="es_publico" value="1">
                            <span>
                                <strong>Hacer público inmediatamente</strong>
                                <br><small class="text-muted">Si no lo marcas, solo tú podrás verlo.</small>
                            </span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1.1rem;">Crear Proyecto</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('avatarPreview');
                    const img = document.getElementById('avatarImg');
                    img.src = event.target.result;
                    preview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('bannerInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('bannerPreview');
                    const img = document.getElementById('bannerImg');
                    img.src = event.target.result;
                    preview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>