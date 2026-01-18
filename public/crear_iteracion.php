<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Post.php';
require_once '../app/Models/Iteracion.php';

if ($_SESSION['rol_id'] != 1) {
    header('Location: explorar.php');
    exit();
}

if (!isset($_GET['post_id'])) {
    header('Location: dashboard_artista.php');
    exit();
}

$postId = $_GET['post_id'];
$usuarioId = $_SESSION['usuario_id'];

$modeloPost = new Post();
$post = $modeloPost->obtenerPorId($postId, $usuarioId);

if (!$post) {
    header('Location: dashboard_artista.php?error=post_no_encontrado');
    exit();
}

$modeloIteracion = new Iteracion();
$iteraciones = $modeloIteracion->obtenerPorPost($postId);
$totalImagenes = 0;
foreach ($iteraciones as $iter) {
    $totalImagenes += count($iter['imagenes']);
}

$espacioDisponible = 50 - $totalImagenes;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Iteración | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .limit-warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid var(--accent);
            border-radius: var(--radius);
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .limit-warning h4 {
            color: var(--accent);
            margin-bottom: 10px;
        }
        
        .preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
            max-width: 100%;
        }
        
        .preview-item {
            position: relative;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--bg-hover);
            aspect-ratio: 1;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .preview-item:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .preview-item.principal {
            border-color: var(--accent);
            border-width: 3px;
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.3);
        }
        
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .preview-item .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            line-height: 1;
            z-index: 10;
        }
        
        .preview-item .remove-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }
        
        .star-principal {
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(0, 0, 0, 0.7);
            color: #ffd700;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            z-index: 10;
            pointer-events: none;
        }
        
        .principal-badge {
            position: absolute;
            bottom: 8px;
            left: 8px;
            background: var(--accent);
            color: black;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
            z-index: 10;
            pointer-events: none;
        }
        
        @media (max-width: 768px) {
            .preview-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
        
        @media (max-width: 480px) {
            .preview-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 800px;">
        
        <div class="navbar">
            <a href="ver_post.php?id=<?php echo $postId; ?>" class="btn btn-secondary">← Volver al Post</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>Nueva Iteración de: "<?php echo htmlspecialchars($post['titulo']); ?>"</h2>
                <p class="text-muted" style="margin-top: 10px;">
                    Espacio disponible: <strong><?php echo $espacioDisponible; ?> imágenes</strong> de 50 totales.
                </p>

                <?php if ($totalImagenes >= 40): ?>
                    <div class="limit-warning">
                        <h4><i class="fas fa-exclamation-triangle"></i> 
                            <?php if ($espacioDisponible == 0): ?>
                                Límite alcanzado
                            <?php else: ?>
                                Cerca del límite
                            <?php endif; ?>
                        </h4>
                        <p>
                            <?php if ($espacioDisponible == 0): ?>
                                Has alcanzado el límite de 50 imágenes para este post. 
                                Elimina algunas iteraciones antiguas si deseas agregar nuevas.
                            <?php else: ?>
                                Solo puedes subir <?php echo $espacioDisponible; ?> imagen(es) más. 
                                El límite total es de 50 imágenes por post.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error" style="margin-top: 20px;">
                        <?php 
                        switch($_GET['error']) {
                            case 'sin_imagenes':
                                echo '<i class="fas fa-exclamation-triangle"></i> Debes subir al menos una imagen';
                                break;
                            case 'limite_excedido':
                                echo '<i class="fas fa-exclamation-triangle"></i> Excedes el límite de imágenes permitidas';
                                break;
                            case 'error_crear_iteracion':
                                echo '<i class="fas fa-exclamation-triangle"></i> Error al crear la iteración. Intenta nuevamente';
                                break;
                            default:
                                echo '<i class="fas fa-exclamation-triangle"></i> Error desconocido';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($espacioDisponible > 0): ?>
                <form action="procesador.php?action=crear_iteracion" method="POST" enctype="multipart/form-data" id="formIteracion">
                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                    <input type="hidden" name="espacio_disponible" value="<?php echo $espacioDisponible; ?>">

                    <div class="form-group">
                        <label class="form-label">Imágenes * (máximo <?php echo min(20, $espacioDisponible); ?> por iteración)</label>
                        <p class="form-hint" style="margin-bottom: 10px;">
                            <i class="fas fa-info-circle"></i> Click en una imagen para marcarla como principal. La primera imagen es principal por defecto.
                        </p>
                        <div class="upload-zone" id="uploadZone">
                            <p style="font-size: 2rem; margin-bottom: 10px;">📤</p>
                            <p><strong>Click para seleccionar imágenes</strong></p>
                            <p class="text-muted" style="font-size: 0.85rem;">o arrastra y suelta aquí</p>
                            <p class="text-muted" style="font-size: 0.75rem; margin-top: 10px;">
                                Máximo <?php echo min(20, $espacioDisponible); ?> imágenes | 5MB por archivo | JPG, PNG, GIF, WEBP
                            </p>
                        </div>
                        <input type="file" name="imagenes[]" id="inputImagenes" multiple accept="image/*" style="display: none;" required>
                        <input type="hidden" name="imagen_principal_index" id="imagenPrincipalIndex" value="0">
                    </div>

                    <div id="previewContainer" style="display: none;"></div>

                    <div class="form-group">
                        <label class="form-label">Notas de Cambio</label>
                        <textarea name="notas_cambio" class="form-control" rows="4" 
                                  placeholder="¿Qué cambiaste en esta iteración? (Ej: Ajusté los colores, mejoré la iluminación...)"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiempo Dedicado (minutos)</label>
                        <input type="number" name="tiempo_dedicado_min" class="form-control" 
                               placeholder="Ej: 120" min="1" step="1">
                        <span class="form-hint">Opcional: Tiempo aproximado que te tomó esta iteración</span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 20px;" id="btnSubmit" disabled>
                        Guardar Nueva Iteración
                    </button>
                </form>
                <?php else: ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> No puedes agregar más imágenes a este post. 
                        Límite de 50 imágenes alcanzado.
                    </div>
                    <a href="ver_post.php?id=<?php echo $postId; ?>" class="btn btn-secondary" style="width: 100%; margin-top: 20px;">
                        Volver al Post
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const uploadZone = document.getElementById('uploadZone');
        const inputImagenes = document.getElementById('inputImagenes');
        const previewContainer = document.getElementById('previewContainer');
        const btnSubmit = document.getElementById('btnSubmit');
        const imagenPrincipalIndex = document.getElementById('imagenPrincipalIndex');
        const maxImagenes = <?php echo min(20, $espacioDisponible); ?>;
        let selectedFiles = [];
        let principalIndex = 0;

        uploadZone?.addEventListener('click', () => inputImagenes?.click());

        uploadZone?.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone?.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone?.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            agregarArchivos(files);
        });

        inputImagenes?.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            agregarArchivos(files);
        });

        function agregarArchivos(newFiles) {
            const espacioDisponible = maxImagenes - selectedFiles.length;
            
            if (newFiles.length > espacioDisponible) {
                alert(`Solo puedes agregar ${espacioDisponible} imagen(es) más. Límite: ${maxImagenes} imágenes por iteración.`);
                newFiles = newFiles.slice(0, espacioDisponible);
            }

            selectedFiles = [...selectedFiles, ...newFiles];
            actualizarPrevisualizacion();
            actualizarBotonSubmit();
        }

        function actualizarPrevisualizacion() {
            if (!previewContainer) return;
            
            previewContainer.innerHTML = '';
            
            if (selectedFiles.length === 0) {
                previewContainer.style.display = 'none';
                return;
            }

            previewContainer.style.display = 'grid';
            previewContainer.className = 'preview-container';

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item' + (index === principalIndex ? ' principal' : '');
                    div.onclick = () => marcarComoPrincipal(index);
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        ${index === principalIndex ? '<div class="star-principal">★</div>' : ''}
                        ${index === principalIndex ? '<div class="principal-badge">PRINCIPAL</div>' : ''}
                        <button type="button" class="remove-btn" onclick="event.stopPropagation(); eliminarImagen(${index})">×</button>
                    `;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        function marcarComoPrincipal(index) {
            if (principalIndex !== index) {
                principalIndex = index;
                imagenPrincipalIndex.value = index;
                actualizarPrevisualizacion();
            }
        }

        function eliminarImagen(index) {
            selectedFiles.splice(index, 1);
            
            if (principalIndex === index) {
                principalIndex = 0;
            } else if (principalIndex > index) {
                principalIndex--;
            }
            
            imagenPrincipalIndex.value = principalIndex;
            actualizarPrevisualizacion();
            actualizarBotonSubmit();
            actualizarInputFile();
        }

        function actualizarBotonSubmit() {
            if (btnSubmit) {
                btnSubmit.disabled = selectedFiles.length === 0;
            }
        }

        function actualizarInputFile() {
            if (!inputImagenes) return;
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            inputImagenes.files = dataTransfer.files;
        }

        document.getElementById('formIteracion')?.addEventListener('submit', function(e) {
            if (selectedFiles.length === 0) {
                e.preventDefault();
                alert('Debes seleccionar al menos una imagen');
            }
            if (selectedFiles.length > maxImagenes) {
                e.preventDefault();
                alert(`Máximo ${maxImagenes} imágenes permitidas`);
            }
        });
    </script>
</body>
</html>