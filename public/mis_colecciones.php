<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Coleccion.php';

if ($_SESSION['rol_id'] != 2) {
    header('Location: dashboard_artista.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$modeloColeccion = new Coleccion();

$mostrarModalCrear = isset($_GET['crear']);

$colecciones = $modeloColeccion->obtenerPorUsuario($usuario_id);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Colecciones | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/colecciones.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-layout">
        <?php 
        $active_page = 'mis_colecciones';
        include 'includes/sidebar_cliente.php';
        ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-folder"></i> Mis Colecciones</h1>
                <button class="btn btn-primary" onclick="abrirModal()">
                    <i class="fas fa-plus"></i> Nueva Colección
                </button>
            </div>

            <?php if (empty($colecciones)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Aún no tienes colecciones</h3>
                    <p>Crea tu primera colección para guardar trabajos que te interesen</p>
                    <button class="btn btn-primary" onclick="abrirModal()" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Crear Colección
                    </button>
                </div>
            <?php else: ?>
                <div class="colecciones-grid">
                    <!-- Tarjeta crear -->
                    <div class="crear-coleccion-card" onclick="abrirModal()">
                        <i class="fas fa-plus"></i>
                        <span>Nueva Colección</span>
                    </div>

                    <?php foreach ($colecciones as $col): ?>
                        <div class="coleccion-card">
                            <a href="ver_coleccion.php?id=<?php echo $col['id']; ?>">
                                <div class="coleccion-preview">
                                    <?php 
                                    $previews = $modeloColeccion->obtenerPreviewPosts($col['id'], 4);
                                    for ($i = 0; $i < 4; $i++): 
                                    ?>
                                        <?php if (isset($previews[$i]) && !empty($previews[$i]['portada'])): ?>
                                            <img src="<?php echo htmlspecialchars($previews[$i]['portada']); ?>" 
                                                 alt="" class="preview-img">
                                        <?php else: ?>
                                            <div class="preview-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </a>
                            
                            <div class="coleccion-info">
                                <a href="ver_coleccion.php?id=<?php echo $col['id']; ?>" style="text-decoration: none; color: inherit;">
                                    <h3><?php echo htmlspecialchars($col['nombre']); ?></h3>
                                </a>
                                <div class="coleccion-meta">
                                    <span><?php echo $col['total_posts']; ?> post<?php echo $col['total_posts'] != 1 ? 's' : ''; ?></span>
                                    <div class="coleccion-actions">
                                        <button onclick="editarColeccion(<?php echo $col['id']; ?>, '<?php echo htmlspecialchars(addslashes($col['nombre'])); ?>', '<?php echo htmlspecialchars(addslashes($col['descripcion'] ?? '')); ?>')" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="delete-btn" onclick="eliminarColeccion(<?php echo $col['id']; ?>, '<?php echo htmlspecialchars(addslashes($col['nombre'])); ?>')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Crear/Editar Colección -->
    <div class="modal-backdrop" id="modalColeccion">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Nueva Colección</h2>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <form id="formColeccion" onsubmit="guardarColeccion(event)">
                <input type="hidden" id="coleccionId" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombreColeccion">Nombre *</label>
                        <input type="text" id="nombreColeccion" required 
                               placeholder="Ej: Favoritos, Inspiración, Referencias...">
                    </div>
                    <div class="form-group">
                        <label for="descripcionColeccion">Descripción (opcional)</label>
                        <textarea id="descripcionColeccion" 
                                  placeholder="Describe el propósito de esta colección..."></textarea>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        <?php if ($mostrarModalCrear): ?>
        document.addEventListener('DOMContentLoaded', () => abrirModal());
        <?php endif; ?>

        function abrirModal() {
            document.getElementById('coleccionId').value = '';
            document.getElementById('nombreColeccion').value = '';
            document.getElementById('descripcionColeccion').value = '';
            document.getElementById('modalTitulo').textContent = 'Nueva Colección';
            document.getElementById('modalColeccion').classList.add('active');
        }

        function editarColeccion(id, nombre, descripcion) {
            document.getElementById('coleccionId').value = id;
            document.getElementById('nombreColeccion').value = nombre;
            document.getElementById('descripcionColeccion').value = descripcion;
            document.getElementById('modalTitulo').textContent = 'Editar Colección';
            document.getElementById('modalColeccion').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('modalColeccion').classList.remove('active');
        }

        function guardarColeccion(e) {
            e.preventDefault();
            
            const id = document.getElementById('coleccionId').value;
            const nombre = document.getElementById('nombreColeccion').value;
            const descripcion = document.getElementById('descripcionColeccion').value;

            const action = id ? 'editar_coleccion' : 'crear_coleccion';
            
            fetch('procesador.php?action=' + action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `coleccion_id=${id}&nombre=${encodeURIComponent(nombre)}&descripcion=${encodeURIComponent(descripcion)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error al guardar');
                }
            });
        }

        function eliminarColeccion(id, nombre) {
            if (!confirm(`¿Eliminar la colección "${nombre}"?\n\nLos posts guardados no se eliminarán, solo la colección.`)) {
                return;
            }
            
            fetch('procesador.php?action=eliminar_coleccion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `coleccion_id=${id}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error al eliminar');
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') cerrarModal();
        });

        document.getElementById('modalColeccion').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });
    </script>
</body>
</html>
