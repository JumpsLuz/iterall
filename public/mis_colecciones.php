<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Coleccion.php';

// Solo clientes/reclutadores pueden acceder
if ($_SESSION['rol_id'] != 2) {
    header('Location: dashboard_artista.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$modeloColeccion = new Coleccion();

// Manejar creación de colección (si viene por GET con crear=1)
$mostrarModalCrear = isset($_GET['crear']);

// Obtener colecciones del usuario
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #0a0a0a;
            color: var(--text-main);
        }
        
        .main-content {
            background: #0a0a0a;
        }
        
        .colecciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .coleccion-card {
            background: #141414;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid #222;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .coleccion-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
        }

        .coleccion-preview {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2px;
            aspect-ratio: 1;
            background: #0a0a0a;
        }

        .coleccion-preview .preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .coleccion-preview .preview-placeholder {
            background: #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .coleccion-info {
            padding: 20px;
        }

        .coleccion-info h3 {
            margin: 0 0 5px;
            font-size: 1.1rem;
        }

        .coleccion-meta {
            color: var(--text-muted);
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .coleccion-actions {
            display: flex;
            gap: 10px;
        }

        .coleccion-actions button {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 5px;
            transition: color 0.2s;
        }

        .coleccion-actions button:hover {
            color: var(--text-main);
        }

        .coleccion-actions .delete-btn:hover {
            color: var(--danger);
        }

        /* Tarjeta para crear nueva */
        .crear-coleccion-card {
            background: #0f0f0f;
            border: 2px dashed #333;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 200px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .crear-coleccion-card:hover {
            border-color: var(--primary);
            background: rgba(74, 144, 226, 0.05);
        }

        .crear-coleccion-card i {
            font-size: 2.5rem;
            color: #444;
            margin-bottom: 10px;
        }

        .crear-coleccion-card span {
            color: var(--text-muted);
        }

        /* Modal */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop.active {
            display: flex;
        }

        .modal-content {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            border: 1px solid #333;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.3rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: #666;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal-close:hover {
            color: var(--text-main);
        }

        .modal-body .form-group {
            margin-bottom: 20px;
        }

        .modal-body label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .modal-body input,
        .modal-body textarea {
            width: 100%;
            padding: 12px 15px;
            background: #0f0f0f;
            border: 1px solid #333;
            border-radius: 8px;
            color: var(--text-main);
            font-size: 1rem;
        }

        .modal-body textarea {
            resize: vertical;
            min-height: 80px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #444;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            color: var(--text-main);
            font-size: 1.2rem;
            margin-bottom: 8px;
            margin-bottom: 10px;
        }

        /* Page header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .colecciones-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
        // Auto-abrir modal si viene con ?crear=1
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

        // Cerrar modal con Escape o clic fuera
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') cerrarModal();
        });

        document.getElementById('modalColeccion').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });
    </script>
</body>
</html>
