<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Post.php';

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Versión | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 800px;">
        
        <div class="navbar">
            <a href="ver_post.php?id=<?php echo $postId; ?>" class="btn btn-secondary">← Volver al Post</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>Nueva Versión de: "<?php echo htmlspecialchars($post['titulo']); ?>"</h2>
                <p class="text-muted" style="margin-top: 10px;">
                    Sube hasta 20 imágenes para documentar esta iteración. La primera imagen será la miniatura principal.
                </p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error" style="margin-top: 20px;">
                        <?php 
                        switch($_GET['error']) {
                            case 'sin_imagenes':
                                echo '<i class="fas fa-exclamation-triangle"></i> Debes subir al menos una imagen';
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

                <form action="procesador.php?action=crear_iteracion" method="POST" enctype="multipart/form-data" id="formIteracion">
                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">

                    <div class="form-group">
                        <label class="form-label">Imágenes *</label>
                        <div class="upload-zone" id="uploadZone">
                            <p style="font-size: 2rem; margin-bottom: 10px;">📤</p>
                            <p><strong>Click para seleccionar imágenes</strong></p>
                            <p class="text-muted" style="font-size: 0.85rem;">o arrastra y suelta aquí</p>
                            <p class="text-muted" style="font-size: 0.75rem; margin-top: 10px;">
                                Máximo 20 imágenes | 5MB por archivo | JPG, PNG, GIF, WEBP
                            </p>
                        </div>
                        <input type="file" name="imagenes[]" id="inputImagenes" multiple accept="image/*" style="display: none;" required>
                    </div>

                    <div id="previewContainer" class="preview-container" style="display: none;"></div>

                    <div class="form-group">
                        <label class="form-label">Notas de Cambio</label>
                        <textarea name="notas_cambio" class="form-control" rows="4" 
                                  placeholder="¿Qué cambiaste en esta versión? (Ej: Ajusté los colores, mejoré la iluminación...)"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiempo Dedicado (minutos)</label>
                        <input type="number" name="tiempo_dedicado_min" class="form-control" 
                               placeholder="Ej: 120" min="1" step="1">
                        <span class="form-hint">Opcional: Tiempo aproximado que te tomó esta iteración</span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 20px;" id="btnSubmit" disabled>
                        Guardar Nueva Versión
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const uploadZone = document.getElementById('uploadZone');
        const inputImagenes = document.getElementById('inputImagenes');
        const previewContainer = document.getElementById('previewContainer');
        const btnSubmit = document.getElementById('btnSubmit');
        let selectedFiles = [];

        uploadZone.addEventListener('click', () => inputImagenes.click());

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            agregarArchivos(files);
        });

        inputImagenes.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            agregarArchivos(files);
        });

        function agregarArchivos(newFiles) {
            const espacioDisponible = 20 - selectedFiles.length;
            const filesToAdd = newFiles.slice(0, espacioDisponible);

            selectedFiles = [...selectedFiles, ...filesToAdd];
            actualizarPrevisualizacion();
            actualizarBotonSubmit();
        }

        function actualizarPrevisualizacion() {
            previewContainer.innerHTML = '';
            
            if (selectedFiles.length === 0) {
                previewContainer.style.display = 'none';
                return;
            }

            previewContainer.style.display = 'grid';

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="remove-btn" onclick="eliminarImagen(${index})">×</button>
                        ${index === 0 ? '<div style="position: absolute; bottom: 5px; left: 5px; background: var(--accent); color: black; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">PRINCIPAL</div>' : ''}
                    `;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        function eliminarImagen(index) {
            selectedFiles.splice(index, 1);
            actualizarPrevisualizacion();
            actualizarBotonSubmit();
            actualizarInputFile();
        }

        function actualizarBotonSubmit() {
            btnSubmit.disabled = selectedFiles.length === 0;
        }

        function actualizarInputFile() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            inputImagenes.files = dataTransfer.files;
        }

        document.getElementById('formIteracion').addEventListener('submit', function(e) {
            if (selectedFiles.length === 0) {
                e.preventDefault();
                alert('Debes seleccionar al menos una imagen');
            }
        });
    </script>
</body>
</html>