<div class="admin-sidebar">
    <h5 class="fw-bold mb-3 pb-2 border-bottom text-secondary">Navegación</h5>
    <nav class="nav flex-column">
        <a class="admin-nav-link <?php echo $tab == 'dashboard' ? 'active' : ''; ?>" href="index.php?tab=dashboard">
            <i class="bi bi-speedometer2"></i> Inicio
        </a>
        <a class="admin-nav-link <?php echo $tab == 'cursos' ? 'active' : ''; ?>" href="index.php?tab=cursos">
            <i class="bi bi-journal-bookmark"></i> Cursos
        </a>
        <a class="admin-nav-link <?php echo $tab == 'usuarios' ? 'active' : ''; ?>" href="index.php?tab=usuarios">
            <i class="bi bi-people"></i> Usuarios
        </a>
        <a class="admin-nav-link <?php echo $tab == 'servicios' ? 'active' : ''; ?>" href="index.php?tab=servicios">
            <i class="bi bi-shield-check"></i> Roles y Permisos
        </a>
        <a class="admin-nav-link <?php echo $tab == 'inscripciones' ? 'active' : ''; ?>" href="index.php?tab=inscripciones">
            <i class="bi bi-person-check"></i> Inscripciones De Los Cursos
        </a>
        <a class="admin-nav-link <?php echo $tab == 'ajustes' ? 'active' : ''; ?>" href="index.php?tab=ajustes">
            <i class="bi bi-gear"></i> Configuración
        </a>
    </nav>
</div>
