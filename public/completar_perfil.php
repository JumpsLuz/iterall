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

                    <div class="form-group">
                        <label class="form-label">Avatar (400x400px)</label>
                        <div class="image-preview" id="avatarPreview" onclick="document.getElementById('avatarInput').click()">
                            <div class="placeholder-text">
                                <p style="font-size: 2rem;">👤</p>
                                <p>Click para seleccionar imagen</p>
                                <p class="text-muted" style="font-size: 0.85rem;">JPG, PNG, GIF, WEBP | Máx. 5MB</p>
                            </div>
                            <img id="avatarImg" alt="Vista previa avatar">
                        </div>
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Banner (1500x500px)</label>
                        <div class="image-preview" id="bannerPreview" onclick="document.getElementById('bannerInput').click()">
                            <div class="placeholder-text">
                                <p style="font-size: 2rem;">🖼️</p>
                                <p>Click para seleccionar imagen</p>
                                <p class="text-muted" style="font-size: 0.85rem;">JPG, PNG, GIF, WEBP | Máx. 5MB</p>
                            </div>
                            <img id="bannerImg" alt="Vista previa banner">
                        </div>
                        <input type="file" id="bannerInput" name="banner" accept="image/*" style="display: none;">
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Redes Sociales</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Instagram</label>
                        <input type="text" name="instagram" class="form-control" placeholder="@tuusuario">
                    </div>

                    <div class="form-group">
                        <label class="form-label">ArtStation</label>
                        <input type="url" name="artstation" class="form-control" placeholder="https://www.artstation.com/tuusuario">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Twitter/X</label>
                        <input type="text" name="twitter" class="form-control" placeholder="@tuusuario">
                    </div>
                    
                    <!-- Tengo que arreglarlo pa q se puedan poner los enlaces y ya -->

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