<?php 
require_once '../app/Config/auth_check.php';
require_once '../app/Models/RedSocial.php'; 

$redesDisponibles = RedSocial::obtenerRedesSoportadas(); 
$esUpgrade = isset($_GET['upgrade']) && $_GET['upgrade'] == 1;
$esCliente = ($_SESSION['rol_id'] == 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esUpgrade ? 'Configurar Perfil de Artista' : 'Completa tu Perfil'; ?> | ITERALL</title>
    <?php include 'includes/favicon.php'; ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-layout">
        <?php 
        if ($esCliente) {
            include 'includes/sidebar_cliente.php';
        } else {
            include 'includes/sidebar.php';
        }
        ?>

        <main class="main-content">
    <div class="profile-setup-container">
        <div class="profile-setup-card">
            <?php if ($esUpgrade): ?>
                <h2><i class="fas fa-rocket"></i> ¡Bienvenido Artista!</h2>
                <p class="intro-text">
                    Configura tu perfil de artista para comenzar a mostrar tu trabajo al mundo.
                    Una vez guardado, tu cuenta será actualizada automáticamente.
                </p>
            <?php else: ?>
                <h2>¡Bienvenido a ITERALL! <i class="fas fa-palette"></i></h2>
                <p class="intro-text">
                    Configura tu perfil para comenzar a mostrar tu trabajo al mundo. 
                    Estos datos aparecerán en tu portafolio público.
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> Hubo un error al guardar tu perfil. Intenta nuevamente.
                </div>
            <?php endif; ?>

            <form action="procesador.php?action=actualizar_perfil<?php echo $esUpgrade ? '&upgrade=1' : ''; ?>" method="POST" enctype="multipart/form-data" id="formCompletarPerfil">
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
                                <p style="font-size: 2rem;"><i class="fas fa-user"></i></p>
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
                    <p class="form-hint" style="margin-bottom: 15px;">
                        Agrega tus perfiles profesionales. Puedes agregar más de uno.
                    </p>
                    
                    <div id="redesSocialesContainer">
                    </div>
                    
                    <button type="button" class="btn btn-secondary" onclick="agregarRed()" 
                            style="width: 100%; margin-top: 10px;">
                        + Agregar Red Social
                    </button>
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
        const redesDisponibles = <?php echo json_encode($redesDisponibles); ?>;

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

        function obtenerRedesYaAgregadas() {
            const items = document.querySelectorAll('.red-social-item');
            return Array.from(items).map(item => item.dataset.tipo);
        }

        function agregarRed() {
            const redesUsadas = obtenerRedesYaAgregadas();
            const redesDisponiblesParaAgregar = Object.keys(redesDisponibles).filter(
                tipo => !redesUsadas.includes(tipo)
            );

            if (redesDisponiblesParaAgregar.length === 0) {
                alert('Ya has agregado todas las redes sociales disponibles.');
                return;
            }

            let opciones = '<option value="">-- Selecciona una red social --</option>';
            redesDisponiblesParaAgregar.forEach(tipo => {
                const red = redesDisponibles[tipo];
                opciones += `<option value="${tipo}"><i class="${red.icono}"></i> ${red.nombre}</option>`;
            });

            const selector = document.createElement('div');
            selector.className = 'form-group';
            selector.style.background = 'rgba(59, 130, 246, 0.1)';
            selector.style.padding = '15px';
            selector.style.borderRadius = 'var(--radius)';
            selector.style.marginBottom = '10px';
            selector.innerHTML = `
                <label class="form-label">Selecciona la red social</label>
                <select class="form-control selector-red" onchange="confirmarRedSeleccionada(this)">
                    ${opciones}
                </select>
                <button type="button" class="btn btn-secondary" onclick="cancelarAgregarRed(this)" 
                        style="width: 100%; margin-top: 10px;">Cancelar</button>
            `;

            document.getElementById('redesSocialesContainer').appendChild(selector);
        }

        function confirmarRedSeleccionada(select) {
            const tipo = select.value;
            if (!tipo) return;

            const red = redesDisponibles[tipo];
            const container = select.closest('.form-group');

            const nuevoItem = document.createElement('div');
            nuevoItem.className = 'red-social-item';
            nuevoItem.dataset.tipo = tipo;
            nuevoItem.innerHTML = `
                <div class="form-group">
                    <label class="form-label"><i class="${red.icono}"></i> ${red.nombre}</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="redes[${tipo}]" class="form-control red-input" 
                               placeholder="${red.placeholder}" 
                               data-tipo="${tipo}"
                               data-ayuda="${red.ayuda}">
                        <button type="button" class="btn btn-danger" onclick="eliminarRed(this)" 
                                style="padding: 0 15px;"><i class="fas fa-trash"></i></button>
                    </div>
                    <span class="form-hint">${red.ayuda}</span>
                    <span class="error-msg" style="color: var(--danger); font-size: 0.85rem; display: none;"></span>
                </div>
            `;

            container.replaceWith(nuevoItem);

            const input = nuevoItem.querySelector('.red-input');
            input.addEventListener('input', validarRedEnTiempoReal);
            input.focus();
        }

        function cancelarAgregarRed(button) {
            button.closest('.form-group').remove();
        }

        function eliminarRed(button) {
            if (confirm('¿Eliminar esta red social?')) {
                button.closest('.red-social-item').remove();
            }
        }

        function validarRedEnTiempoReal(e) {
            const input = e.target;
            const url = input.value.trim();
            const errorSpan = input.closest('.form-group').querySelector('.error-msg');

            if (!url) {
                errorSpan.style.display = 'none';
                input.style.borderColor = '';
                return;
            }

            const tipo = input.dataset.tipo || input.closest('.red-social-item').dataset.tipo;
            const red = redesDisponibles[tipo];
            let urlValidar = url;
            
            // Si no tiene https://, agregarlo para validación
            if (!urlValidar.match(/^https?:\/\//)) {
                urlValidar = 'https://' + urlValidar;
            }
            
            // Remove the / delimiters from PHP regex pattern
            const patron = new RegExp(red.patron.slice(1, -1));

            if (patron.test(urlValidar)) {
                input.style.borderColor = 'var(--success)';
                errorSpan.style.display = 'none';
            } else {
                input.style.borderColor = 'var(--danger)';
                errorSpan.textContent = 'X ' + red.ayuda;
                errorSpan.style.display = 'block';
            }
        }
    </script>
        </main>
    </div>
</body>
</html>