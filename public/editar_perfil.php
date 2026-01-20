<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Usuario.php';

$usuario_id = $_SESSION['usuario_id'];
$rol_id = $_SESSION['rol_id'];
$esCliente = ($rol_id == 2);

if ($esCliente) {
    header('Location: convertir_a_artista.php');
    exit();
}

$db = Database::getInstance();

$stmt = $db->prepare("SELECT * FROM perfiles WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

$redes = json_decode($perfil['redes_sociales_json'] ?? '{}', true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | ITERALL</title>
    <?php include 'includes/favicon.php'; ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-layout">
        <?php 
        $active_page = 'editar_perfil'; 
        if ($esCliente) {
            include 'includes/sidebar_cliente.php';
        } else {
            include 'includes/sidebar.php';
        }
        ?>

        <main class="main-content">
    <form action="procesador.php?action=actualizar_perfil" method="POST" enctype="multipart/form-data" id="formPerfil">
        
        <header class="profile-header">
            
            <div class="banner-container banner-edit-zone" onclick="document.getElementById('bannerInput').click()">
                <?php if (!empty($perfil['banner_url'])): ?>
                    <img src="<?php echo htmlspecialchars($perfil['banner_url']); ?>" 
                         class="banner-img" id="bannerPreview">
                <?php else: ?>
                    <div style="height: 200px; background: #333; display: flex; align-items: center; justify-content: center; color: #888;">
                        Sin banner - Click para subir
                    </div>
                <?php endif; ?>
            </div>
            <input type="file" id="bannerInput" name="banner" accept="image/*" style="display: none;">
            
            <div class="profile-info">
                
                <div class="avatar-edit-wrapper" onclick="document.getElementById('avatarInput').click()">
                    <?php if (!empty($perfil['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($perfil['avatar_url']); ?>" 
                             class="avatar-img" id="avatarPreview" alt="Avatar">
                    <?php else: ?>
                        <div class="avatar-placeholder" id="avatarPreview">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;">
                
                <p class="info-hint">Click en el avatar o banner para cambiarlos</p>
            </div>
        </header>

        <div class="container">
            
            <div class="navbar">
                <a href="<?php echo $esCliente ? 'explorar.php' : 'dashboard_artista.php'; ?>" class="btn btn-secondary">← Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> Hubo un error al actualizar. Intenta nuevamente.
                </div>
            <?php endif; ?>

            <div class="editable-section">
                <span class="edit-label"><i class="fas fa-edit"></i> INFORMACIÓN BÁSICA</span>
                
                <div class="form-group">
                    <label class="form-label">Nombre Artístico *</label>
                    <input type="text" name="nombre_artistico" class="form-control" 
                           value="<?php echo htmlspecialchars($perfil['nombre_artistico'] ?? ''); ?>" 
                           required maxlength="100" placeholder="Tu nombre público">
                </div>

                <div class="form-group">
                    <label class="form-label">Biografía</label>
                    <textarea name="biografia" class="form-control" rows="4" 
                              maxlength="500" placeholder="Cuéntanos sobre ti..."><?php echo htmlspecialchars($perfil['biografia'] ?? ''); ?></textarea>
                    <span class="form-hint">Máximo 500 caracteres</span>
                </div>
            </div>

            <div class="editable-section">
                <span class="edit-label"><i class="fas fa-globe"></i> REDES SOCIALES</span>
                <p class="info-hint" style="margin-bottom: 15px;">
                    Agrega tus perfiles. Se validará automáticamente el formato correcto.
                </p>
                
                <div id="redesSocialesContainer">
                    <?php
                    require_once '../app/Models/RedSocial.php';
                    $redesDisponibles = RedSocial::obtenerRedesSoportadas();
                    
                    if (!empty($redes) && is_array($redes)) {
                        foreach ($redes as $tipo => $url) {
                            if (!empty($url) && isset($redesDisponibles[$tipo])) {
                                $red = $redesDisponibles[$tipo];
                                echo '<div class="red-social-item" data-tipo="' . $tipo . '">';
                                echo '<div class="form-group">';
                                echo '<label class="form-label"><i class="' . $red['icono'] . '"></i> ' . $red['nombre'] . '</label>';
                                echo '<div style="display: flex; gap: 10px;">';
                                echo '<input type="text" name="redes[' . $tipo . ']" class="form-control red-input" ';
                                echo 'value="' . htmlspecialchars($url) . '" ';
                                echo 'placeholder="' . $red['placeholder'] . '" ';
                                echo 'data-patron="' . htmlspecialchars($red['patron']) . '" ';
                                echo 'data-ayuda="' . htmlspecialchars($red['ayuda']) . '">';
                                echo '<button type="button" class="btn btn-danger" onclick="eliminarRed(this)" style="padding: 0 15px;"><i class="fas fa-trash"></i></button>';
                                echo '</div>';
                                echo '<span class="error-msg" style="color: var(--danger); font-size: 0.85rem; display: none;"></span>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                </div>
                
                <button type="button" class="btn btn-secondary" onclick="agregarRed()" style="width: 100%; margin-top: 10px;">
                    + Agregar Red Social
                </button>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.1rem;">
                💾 Guardar Todos los Cambios
            </button>
        </div>
    </form>

    <script>
        // Obtener redes soportadas (datos del servidor)
        const redesDisponibles = <?php 
            require_once '../app/Models/RedSocial.php';
            $redesSoportadas = RedSocial::obtenerRedesSoportadas();
            echo json_encode($redesSoportadas);
        ?>;

        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('avatarPreview');
                    if (preview.tagName === 'IMG') {
                        preview.src = event.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.className = 'avatar-img';
                        img.id = 'avatarPreview';
                        preview.parentNode.replaceChild(img, preview);
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('bannerInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const bannerContainer = document.querySelector('.banner-container');
                    bannerContainer.innerHTML = `<img src="${event.target.result}" class="banner-img" id="bannerPreview">`;
                };
                reader.readAsDataURL(file);
            }
        });

        function agregarRed() {
            const container = document.getElementById('redesSocialesContainer');
            const redesUsadas = Array.from(container.querySelectorAll('.red-social-item')).map(el => el.dataset.tipo);
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

            container.appendChild(selector);
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
                               data-patron="${red.patron}"
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

        // Validación en tiempo real de redes sociales
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('red-input')) {
                validarRedSocial(e.target);
            }
        });

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('red-input')) {
                validarRedEnTiempoReal(e);
            }
        });

        document.addEventListener('blur', function(e) {
            if (e.target.classList.contains('red-input')) {
                validarRedSocial(e.target);
            }
        }, true);

        function validarRedSocial(input) {
            const errorMsg = input.closest('.form-group').querySelector('.error-msg');
            const url = input.value.trim();
            
            if (!url) {
                errorMsg.style.display = 'none';
                input.style.borderColor = '';
                return;
            }
            
            const tipo = input.closest('.red-social-item').dataset.tipo;
            const red = redesDisponibles[tipo];
            let urlValidar = url;
            
            // Si no tiene https://, agregarlo para validación
            if (!urlValidar.match(/^https?:\/\//)) {
                urlValidar = 'https://' + urlValidar;
            }
            
            const patron = new RegExp(red.patron.slice(1, -1)); // Remover las slashes
            
            if (patron.test(urlValidar)) {
                errorMsg.style.display = 'none';
                input.style.borderColor = '#10b981';
            } else {
                errorMsg.textContent = red.ayuda;
                errorMsg.style.display = 'block';
                input.style.borderColor = '#ef4444';
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

            const tipo = input.closest('.red-social-item').dataset.tipo;
            const red = redesDisponibles[tipo];
            let urlValidar = url;
            
            // Si no tiene https://, agregarlo para validación
            if (!urlValidar.match(/^https?:\/\//)) {
                urlValidar = 'https://' + urlValidar;
            }
            
            const patron = new RegExp(red.patron.slice(1, -1)); // Remover las slashes

            if (patron.test(urlValidar)) {
                input.style.borderColor = 'var(--success)';
                errorSpan.style.display = 'none';
            } else {
                input.style.borderColor = 'var(--danger)';
                errorSpan.textContent = red.ayuda;
                errorSpan.style.display = 'block';
            }
        }

        let formChanged = false;
        document.getElementById('formPerfil').addEventListener('change', () => formChanged = true);
        
        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        document.getElementById('formPerfil').addEventListener('submit', () => formChanged = false);
    </script>
        </main>
    </div>
</body>
</html>