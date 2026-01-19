<?php
$sidebar_cliente_items = [
    'explorar' => ['link' => 'explorar.php', 'label' => 'Explorar', 'icon' => 'fas fa-compass'],
    'mis_colecciones' => ['link' => 'mis_colecciones.php', 'label' => 'Mis Colecciones', 'icon' => 'fas fa-folder-open'],
    'editar_perfil' => ['link' => 'editar_perfil.php', 'label' => 'Mi Perfil', 'icon' => 'fas fa-user'],
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
        <a href="explorar.php">
            <img src="https://res.cloudinary.com/dyqubcdf0/image/upload/v1768787599/ITERALL_aneaxn.svg" alt="ITERALL Logo">
        </a>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Descubrir</div>
        <nav class="sidebar-nav">
            <a href="<?php echo $sidebar_cliente_items['explorar']['link']; ?>" 
               <?php echo $active_page === 'explorar' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_cliente_items['explorar']['icon']; ?>"></i> 
                <?php echo $sidebar_cliente_items['explorar']['label']; ?>
            </a>
            <a href="<?php echo $sidebar_cliente_items['mis_colecciones']['link']; ?>" 
               <?php echo $active_page === 'mis_colecciones' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_cliente_items['mis_colecciones']['icon']; ?>"></i> 
                <?php echo $sidebar_cliente_items['mis_colecciones']['label']; ?>
            </a>
        </nav>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-title">Cuenta</div>
        <nav class="sidebar-nav">
            <a href="<?php echo $sidebar_cliente_items['editar_perfil']['link']; ?>" 
               <?php echo $active_page === 'editar_perfil' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_cliente_items['editar_perfil']['icon']; ?>"></i> 
                <?php echo $sidebar_cliente_items['editar_perfil']['label']; ?>
            </a>
            <a href="<?php echo $sidebar_cliente_items['opciones']['link']; ?>" 
               <?php echo $active_page === 'opciones' ? 'class="active"' : ''; ?>>
                <i class="<?php echo $sidebar_cliente_items['opciones']['icon']; ?>"></i> 
                <?php echo $sidebar_cliente_items['opciones']['label']; ?>
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
