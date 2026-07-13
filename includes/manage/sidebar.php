<?php
// Obtener nombre y rol del usuario en sesión
$session_name  = $_SESSION['user_name']  ?? 'Administrador';
$session_role  = $_SESSION['user_role']  ?? 1;
$session_email = $_SESSION['user_email'] ?? '';
$role_label    = match((int)$session_role) {
    1 => ['label' => 'Super Admin', 'color' => 'danger'],
    2 => ['label' => 'Instructor',  'color' => 'primary'],
    default => ['label' => 'Usuario', 'color' => 'secondary'],
};
$initials = strtoupper(mb_substr($session_name, 0, 1));
?>
<div class="admin-sidebar rounded-4 border bg-white shadow-sm p-0 overflow-hidden">

    <!-- Usuario en sesión -->
    <div class="p-3 border-bottom" style="background: linear-gradient(135deg, rgba(26,115,232,0.07) 0%, rgba(46,125,50,0.05) 100%);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                 style="width:42px; height:42px; min-width:42px; background: var(--primary-color); font-size: 1rem;">
                <?php echo $initials; ?>
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-dark small text-truncate" style="max-width:140px;"><?php echo htmlspecialchars($session_name); ?></div>
                <span class="badge rounded-pill bg-<?php echo $role_label['color']; ?> bg-opacity-10 text-<?php echo $role_label['color']; ?> px-2" style="font-size:0.7rem;">
                    <?php echo $role_label['label']; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Navegación -->
    <div class="p-3">
        <div class="small text-uppercase fw-bold text-muted mb-2 px-2" style="letter-spacing:.06em; font-size:.68rem;">Menú principal</div>
        <nav class="nav flex-column gap-1">
            <a class="admin-nav-link <?php echo $tab == 'dashboard' ? 'active' : ''; ?>" href="index.php?tab=dashboard">
                <i class="bi bi-speedometer2"></i> Inicio
            </a>
            <a class="admin-nav-link <?php echo $tab == 'cursos' ? 'active' : ''; ?>" href="index.php?tab=cursos">
                <i class="bi bi-journal-bookmark"></i> Cursos
            </a>
            <a class="admin-nav-link <?php echo $tab == 'categorias' ? 'active' : ''; ?>" href="index.php?tab=categorias">
                <i class="bi bi-tags"></i> Categorías
            </a>
            <a class="admin-nav-link <?php echo $tab == 'usuarios' ? 'active' : ''; ?>" href="index.php?tab=usuarios">
                <i class="bi bi-people"></i> Usuarios
            </a>
            <a class="admin-nav-link <?php echo $tab == 'servicios' ? 'active' : ''; ?>" href="index.php?tab=servicios">
                <i class="bi bi-shield-check"></i> Roles y permisos
            </a>
            <a class="admin-nav-link <?php echo $tab == 'inscripciones' ? 'active' : ''; ?>" href="index.php?tab=inscripciones">
                <i class="bi bi-person-check"></i> Inscripciones
            </a>
            <a class="admin-nav-link <?php echo $tab == 'multimedia' ? 'active' : ''; ?>" href="index.php?tab=multimedia">
                <i class="bi bi-images"></i> Biblioteca Medios
            </a>
            <a class="admin-nav-link <?php echo $tab == 'blog' ? 'active' : ''; ?>" href="index.php?tab=blog">
                <i class="bi bi-file-earmark-post"></i> Artículos y Blog
            </a>
            <a class="admin-nav-link <?php echo $tab == 'eventos' ? 'active' : ''; ?>" href="index.php?tab=eventos">
                <i class="bi bi-calendar-event"></i> Eventos & Webinars
            </a>
            <a class="admin-nav-link <?php echo $tab == 'auditoria' ? 'active' : ''; ?>" href="index.php?tab=auditoria">
                <i class="bi bi-shield-shaded"></i> Auditoría y Logs
            </a>
            <a class="admin-nav-link <?php echo $tab == 'ajustes' ? 'active' : ''; ?>" href="index.php?tab=ajustes">
                <i class="bi bi-gear"></i> Configuración
            </a>
        </nav>

        <hr class="my-3">

        <div class="small text-uppercase fw-bold text-muted mb-2 px-2" style="letter-spacing:.06em; font-size:.68rem;">Acciones rápidas</div>
        <nav class="nav flex-column gap-1">
            <a class="admin-nav-link" href="crear_curso.php">
                <i class="bi bi-plus-circle text-success"></i> Nuevo curso
            </a>
            <a class="admin-nav-link" href="../index.php" target="_blank">
                <i class="bi bi-globe2 text-info"></i> Ver sitio público
            </a>
        </nav>

        <hr class="my-3">

        <!-- Indicador de plataforma -->
        <div class="rounded-3 p-2 text-center" style="background: var(--primary-light);">
            <div class="small fw-bold text-primary mb-1">
                <i class="bi bi-journal-bookmark-fill me-1"></i>NorthstarLMS
            </div>
            <div class="text-muted" style="font-size:0.68rem;">Panel de Administración v2.0</div>
        </div>
    </div>
</div>
