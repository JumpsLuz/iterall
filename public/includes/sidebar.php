<?php
// Sidebar Navigation Component
// Usage: $active_page should be set before including this file
// Example: $active_page = 'dashboard'; then include 'includes/sidebar.php';

$sidebar_items = [
    'dashboard' => ['link' => 'dashboard_artista.php', 'label' => 'Mi Galería', 'icon' => 'fas fa-th-large'],
    'mis_proyectos' => ['link' => 'mis_proyectos.php', 'label' => 'Todos mis Proyectos', 'icon' => 'fas fa-list'],
    'explorar' => ['link' => 'explorar.php', 'label' => 'Explorar', 'icon' => 'fas fa-compass'],
    'crear_post' => ['link' => 'crear_post_rapido.php', 'label' => 'Post Rápido', 'icon' => 'fas fa-image'],
    'crear_mini' => ['link' => 'crear_miniproyecto.php', 'label' => 'Mini Proyecto', 'icon' => 'fas fa-folder-plus'],
    'crear_proyecto' => ['link' => 'crear_proyecto.php', 'label' => 'Proyecto Grande', 'icon' => 'fas fa-project-diagram'],
    'editar_perfil' => ['link' => 'editar_perfil.php', 'label' => 'Editar Perfil', 'icon' => 'fas fa-user'],
    'opciones' => ['link' => 'opciones.php', 'label' => 'Configuración', 'icon' => 'fas fa-cog'],
];

$active_page = $active_page ?? '';
?>

<button type="button" class="sidebar-toggle" aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <img src="https://res.cloudinary.com/dyqubcdf0/image/upload/v1768787599/ITERALL_aneaxn.svg" alt="ITERALL Logo">
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Mi Trabajo</div>
        <nav class="sidebar-nav">
            <a href="<?php echo $sidebar_items['dashboard']['link']; ?>" 
               <?php echo $active_page === 'dashboard' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_items['dashboard']['icon']; ?>"></i> 
                <?php echo $sidebar_items['dashboard']['label']; ?>
            </a>
            <a href="<?php echo $sidebar_items['mis_proyectos']['link']; ?>" 
               <?php echo $active_page === 'mis_proyectos' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_items['mis_proyectos']['icon']; ?>"></i> 
                <?php echo $sidebar_items['mis_proyectos']['label']; ?>
            </a>
            <a href="<?php echo $sidebar_items['explorar']['link']; ?>" 
               <?php echo $active_page === 'explorar' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_items['explorar']['icon']; ?>"></i> 
                <?php echo $sidebar_items['explorar']['label']; ?>
            </a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Crear Nuevo</div>
        <nav class="sidebar-nav">
            <a href="<?php echo $sidebar_items['crear_post']['link']; ?>" 
               <?php echo $active_page === 'crear_post' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_items['crear_post']['icon']; ?>"></i> 
                <?php echo $sidebar_items['crear_post']['label']; ?>
            </a>
            <a href="<?php echo $sidebar_items['crear_mini']['link']; ?>" 
               <?php echo $active_page === 'crear_mini' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_items['crear_mini']['icon']; ?>"></i> 
                <?php echo $sidebar_items['crear_mini']['label']; ?>
            </a>
            <a href="<?php echo $sidebar_items['crear_proyecto']['link']; ?>" 
               <?php echo $active_page === 'crear_proyecto' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_items['crear_proyecto']['icon']; ?>"></i> 
                <?php echo $sidebar_items['crear_proyecto']['label']; ?>
            </a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Cuenta</div>
        <nav class="sidebar-nav">
            <a href="<?php echo $sidebar_items['editar_perfil']['link']; ?>" 
               <?php echo $active_page === 'editar_perfil' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_items['editar_perfil']['icon']; ?>"></i> 
                <?php echo $sidebar_items['editar_perfil']['label']; ?>
            </a>
            <a href="<?php echo $sidebar_items['opciones']['link']; ?>" 
               <?php echo $active_page === 'opciones' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_items['opciones']['icon']; ?>"></i> 
                <?php echo $sidebar_items['opciones']['label']; ?>
            </a>
            <a href="procesador.php?action=logout">
                <i class="fas fa-sign-out-alt"></i> 
                Cerrar Sesión
            </a>
        </nav>
    </div>
</aside>

<script>
(function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !toggleBtn || !overlay) return;

    function setOpen(isOpen) {
        sidebar.classList.toggle('active', isOpen);
        document.body.classList.toggle('sidebar-open', isOpen);
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        overlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }

    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        setOpen(!sidebar.classList.contains('active'));
    });

    overlay.addEventListener('click', function() {
        setOpen(false);
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') setOpen(false);
    });

    sidebar.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link && window.matchMedia('(max-width: 768px)').matches) {
            setOpen(false);
        }
    });
})();
</script>
