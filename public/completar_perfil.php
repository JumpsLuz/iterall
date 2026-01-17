<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completa tu Perfil | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="profile-setup-container">
        <div class="profile-setup-card">
            <h2>¡Bienvenido a ITERALL! 🎨</h2>
            <p class="intro-text">
                Configura tu perfil para comenzar a mostrar tu trabajo al mundo. 
                Estos datos aparecerán en tu portafolio público.
            </p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    ⚠️ Hubo un error al guardar tu perfil. Intenta nuevamente.
                </div>
            <?php endif; ?>

            <form action="procesador.php?action=actualizar_perfil" method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <h3 class="form-section-title">Información Básica</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Nombre Artístico *</label>
                        <input type="text" name="nombre_artistico" class="form-control" 
                               placeholder="Ej: ArtByJuan" required maxlength="100">
                        <span class="form-hint">Este será tu nombre público en la plataforma</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Biografía</label>
                        <textarea name="biografia" class="form-control" rows="4" 
                                  placeholder="Cuéntanos sobre ti, tu estilo y tu experiencia..." maxlength="500"></textarea>
                        <span class="form-hint">Máximo 500 caracteres</span>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Imágenes de Perfil</h3>
                    <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 15px;">
                        Las imágenes son opcionales. Formatos: JPG, PNG, GIF (máx. 5MB cada una)
                    </p>

                    <div class="form-group">
                        <label class="form-label">Avatar</label>
                        <input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/gif">
                        <span class="form-hint">Recomendado: 200x200px o superior (cuadrado)</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Banner</label>
                        <input type="file" name="banner" class="form-control" accept="image/jpeg,image/png,image/gif">
                        <span class="form-hint">Recomendado: 1200x300px o similar (horizontal)</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.1rem;">
                    Guardar y Continuar
                </button>
            </form>

            <div class="auth-link" style="margin-top: 25px;">
                <a href="dashboard_artista.php">Saltar este paso por ahora</a>
            </div>
        </div>
    </div>
</body>
</html>