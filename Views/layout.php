<?php require_once dirname(__DIR__) . '/app/Config/app.php'; require_once dirname(__DIR__) . '/app/Helpers/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Northstar LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="<?= url('css/custom.css') ?>" rel="stylesheet">
</head>
<body>
<?php $currentAction = $_GET['action'] ?? 'landing'; ?>
<nav class="navbar navbar-expand-xl navbar-light premium-navbar sticky-top py-2" id="mainNavbar">
    <div class="container-fluid container-xl px-3 px-xl-4">
        <div class="d-flex align-items-center gap-2 gap-md-3 me-3 flex-shrink-0">
            <button class="navbar-toggler mobile-nav-toggle d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavPanel" aria-controls="mobileNavPanel" aria-label="Abrir menú">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand fw-bold d-flex align-items-center text-primary" href="<?= url() ?>">
                <div class="brand-mark">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <span class="brand-copy">
                    <span class="brand-name">Northstar</span>
                    <span class="brand-subtitle">LMS</span>
                </span>
            </a>
        </div>

        <div class="d-none d-xl-flex flex-grow-1 justify-content-center">
            <ul class="navbar-nav mega-nav-list align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Explorar</a>
                    <div class="dropdown-menu mega-menu shadow border-0 rounded-4 mt-2">
                        <div class="mega-menu-grid">
                            <div class="mega-menu-section">
                                <h6>Descubrir</h6>
                                <a href="<?= url('index.php?action=catalog') ?>">Cursos</a>
                                <a href="<?= url('index.php?action=catalog&categoria=Programación') ?>">Categorías</a>
                                <a href="<?= url('index.php?action=landing#instructores') ?>">Instructores</a>
                                <a href="<?= url('index.php?action=landing#certificaciones') ?>">Certificaciones</a>
                            </div>
                            <div class="mega-menu-section">
                                <h6>Destacados</h6>
                                <a href="<?= url('index.php?action=catalog') ?>">Cursos destacados</a>
                                <a href="<?= url('index.php?action=catalog') ?>">Nuevos cursos</a>
                                <a href="<?= url('index.php?action=catalog') ?>">Cursos populares</a>
                                <a href="<?= url('index.php?action=landing#planes') ?>">Rutas de aprendizaje</a>
                            </div>
                            <div class="mega-menu-section mega-menu-highlight">
                                <h6>Próximamente</h6>
                                <p>Una experiencia de exploración diseñada para crecer con nuevas líneas de negocio y nuevos programas académicos.</p>
                                <a href="<?= url('index.php?action=landing#planes') ?>" class="btn btn-primary btn-sm rounded-pill">Ver planes</a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Aprendizaje</a>
                    <div class="dropdown-menu mega-menu shadow border-0 rounded-4 mt-2">
                        <div class="mega-menu-grid single-row">
                            <div class="mega-menu-section">
                                <h6>Tu progreso</h6>
                                <a href="<?= url('index.php?action=learn') ?>">Continuar curso</a>
                                <a href="<?= url('index.php?action=learn') ?>">Mis cursos</a>
                                <a href="<?= url('index.php?action=dashboard') ?>">Historial</a>
                                <a href="<?= url('index.php?action=certificate&id=0') ?>">Certificados</a>
                            </div>
                            <div class="mega-menu-section">
                                <h6>Planificación</h6>
                                <a href="#">Calendario</a>
                                <a href="#">Evaluaciones</a>
                                <a href="#">Notas</a>
                                <a href="#">Progreso</a>
                            </div>
                            <div class="mega-menu-section mega-menu-highlight">
                                <h6>Enfoque</h6>
                                <p>Centraliza el recorrido formativo del usuario sin saturar el menú principal.</p>
                                <a href="<?= url('index.php?action=learn') ?>" class="btn btn-outline-primary btn-sm rounded-pill">Abrir aprendizaje</a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Recursos</a>
                    <div class="dropdown-menu mega-menu shadow border-0 rounded-4 mt-2">
                        <div class="mega-menu-grid single-row">
                            <div class="mega-menu-section">
                                <h6>Centro de ayuda</h6>
                                <a href="<?= url('index.php?action=landing#faq') ?>">Preguntas frecuentes</a>
                                <a href="<?= url('index.php?action=landing#contacto') ?>">Contacto</a>
                                <a href="#">Documentación</a>
                                <a href="#">Comunidad</a>
                            </div>
                            <div class="mega-menu-section">
                                <h6>Contenido</h6>
                                <a href="#">Blog</a>
                                <a href="#">Eventos</a>
                                <a href="#">Guías</a>
                                <a href="#">Recursos descargables</a>
                            </div>
                            <div class="mega-menu-section mega-menu-highlight">
                                <h6>Soporte</h6>
                                <p>Los recursos secundarios quedan agrupados para evitar saturar la navegación diaria.</p>
                                <a href="<?= url('index.php?action=landing#faq') ?>" class="btn btn-outline-primary btn-sm rounded-pill">Ver ayuda</a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Empresas</a>
                    <div class="dropdown-menu mega-menu shadow border-0 rounded-4 mt-2">
                        <div class="mega-menu-grid single-row">
                            <div class="mega-menu-section">
                                <h6>Escalabilidad</h6>
                                <a href="<?= url('index.php?action=landing#planes') ?>">Planes</a>
                                <a href="<?= url('index.php?action=landing#planes') ?>">Capacitación empresarial</a>
                                <a href="#">API</a>
                                <a href="#">Integraciones</a>
                            </div>
                            <div class="mega-menu-section">
                                <h6>Marketplace</h6>
                                <a href="#">Programas corporativos</a>
                                <a href="#">Equipos y roles</a>
                                <a href="#">Reportes</a>
                                <a href="#">Administración</a>
                            </div>
                            <div class="mega-menu-section mega-menu-highlight">
                                <h6>Diseñado para crecer</h6>
                                <p>Los nuevos módulos se incorporan aquí sin romper la jerarquía principal.</p>
                                <a href="<?= url('index.php?action=landing#planes') ?>" class="btn btn-primary btn-sm rounded-pill">Explorar empresas</a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Más</a>
                    <div class="dropdown-menu mega-menu shadow border-0 rounded-4 mt-2">
                        <div class="mega-menu-grid">
                            <div class="mega-menu-section">
                                <h6>Instituciones</h6>
                                <a href="#">Centros educativos</a>
                                <a href="#">Programas</a>
                                <a href="#">Facilitadores</a>
                                <a href="#">Escuelas</a>
                            </div>
                            <div class="mega-menu-section">
                                <h6>Operaciones</h6>
                                <a href="#">Inscripciones</a>
                                <a href="#">Analítica</a>
                                <a href="#">Seguridad</a>
                                <a href="#">Configuración</a>
                            </div>
                            <div class="mega-menu-section mega-menu-highlight">
                                <h6>Escalable</h6>
                                <p>Este bloque sirve de contenedor futuro para módulos adicionales sin tocar la estructura base.</p>
                                <a href="#" class="btn btn-outline-primary btn-sm rounded-pill">Ver más</a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
            <div class="dropdown">
                <button class="quick-action-btn action-search-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Buscar">
                    <i class="bi bi-search"></i>
                    <span class="quick-action-label d-none d-xl-inline">Buscar</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end search-dropdown shadow border-0 rounded-4 p-3 mt-2">
                    <form class="search-form-compact" action="<?= url('index.php') ?>" method="GET">
                        <input type="hidden" name="action" value="catalog">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="form-control" placeholder="Buscar cursos, programas, instructores..." value="<?= e($_GET['search'] ?? '') ?>">
                    </form>
                    <div class="search-hints">
                        <span>Explorar ahora</span>
                        <a href="<?= url('index.php?action=catalog&categoria=Programación') ?>">Programación</a>
                        <a href="<?= url('index.php?action=catalog') ?>">Certificaciones</a>
                        <a href="<?= url('index.php?action=landing#faq') ?>">Ayuda</a>
                    </div>
                </div>
            </div>

            <?php if (!empty($_SESSION['user_id'])): 
                $navName = $_SESSION['user_name'] ?? 'Usuario';
                $navInitials = strtoupper(mb_substr($navName, 0, 1)) . (str_contains($navName, ' ') ? strtoupper(mb_substr(strrchr($navName,' '),1,1)) : '');
            ?>
                <div class="quick-action-group">
                    <a class="quick-action-btn" href="<?= url('index.php?action=learn') ?>" title="Aprendizaje">
                        <i class="bi bi-journal-bookmark"></i>
                        <span class="quick-action-label d-none d-xl-inline">Aprendizaje</span>
                    </a>
                    <a class="quick-action-btn position-relative" href="<?= url('index.php?action=dashboard') ?>" title="Alertas">
                        <i class="bi bi-bell"></i>
                        <span class="quick-action-label d-none d-xl-inline">Alertas</span>
                        <span class="quick-badge">3</span>
                    </a>
                </div>

                <div class="dropdown profile-shell">
                    <button class="btn p-0 border-0 d-flex align-items-center gap-2 profile-trigger" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="profile-avatar"><?= e($navInitials ?: 'U') ?></div>
                        <div class="profile-meta d-none d-xl-flex">
                            <span class="profile-name"><?= e($navName) ?></span>
                            <span class="profile-role">Estudiante</span>
                        </div>
                        <i class="bi bi-chevron-down text-muted d-none d-xl-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 py-2 mt-2" style="min-width: 240px;">
                        <li class="px-3 pb-2 mb-1 border-bottom">
                            <div class="small fw-bold text-dark"><?= e($navName) ?></div>
                            <div class="text-muted" style="font-size:.72rem;"><?= e($_SESSION['user_email'] ?? 'Mi cuenta') ?></div>
                        </li>
                        <li><a class="dropdown-item small py-2" href="<?= url('index.php?action=dashboard') ?>"><i class="bi bi-speedometer2 me-2 text-primary"></i>Mi Panel</a></li>
                        <li><a class="dropdown-item small py-2" href="<?= url('index.php?action=learn') ?>"><i class="bi bi-journal-bookmark me-2 text-primary"></i>Mis cursos</a></li>
                        <li><a class="dropdown-item small py-2" href="<?= url('index.php?action=certificate&id=0') ?>"><i class="bi bi-patch-check me-2 text-success"></i>Mis certificados</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <?php if (!empty($_SESSION['user_role']) && in_array((int) $_SESSION['user_role'], [1, 2], true)): ?>
                        <li><a class="dropdown-item small py-2" href="<?= url('manage/index.php') ?>"><i class="bi bi-shield-check me-2 text-primary"></i>Panel de gestión</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item small py-2 text-danger" href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a class="btn btn-outline-primary rounded-pill px-3 fw-semibold d-none d-sm-inline-flex" href="<?= url('index.php?action=landing#planes') ?>">Planes</a>
                <a class="btn btn-primary rounded-pill px-3 px-sm-4 fw-semibold text-white shadow-sm d-inline-flex align-items-center gap-2" href="<?= url('login.php') ?>">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Iniciar sesión</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNavPanel" aria-labelledby="mobileNavPanelLabel">
    <div class="offcanvas-header border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="brand-mark">
                <i class="bi bi-journal-bookmark-fill"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">Northstar LMS</div>
                <div class="small text-muted">Centro de aprendizaje</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="navbar-nav gap-2">
            <li class="nav-item"><a class="nav-link px-3 rounded-3 fw-semibold" href="<?= url('index.php?action=landing') ?>">Inicio</a></li>
            <li class="nav-item"><a class="nav-link px-3 rounded-3 fw-semibold" href="<?= url('index.php?action=catalog') ?>">Explorar Cursos</a></li>
            <li class="nav-item"><a class="nav-link px-3 rounded-3 fw-semibold" href="<?= url('index.php?action=landing#categorias') ?>">Categorías</a></li>
            <li class="nav-item"><a class="nav-link px-3 rounded-3 fw-semibold" href="<?= url('index.php?action=landing#instructores') ?>">Instructores</a></li>
            <li class="nav-item"><a class="nav-link px-3 rounded-3 fw-semibold" href="<?= url('index.php?action=landing#certificaciones') ?>">Certificaciones</a></li>
            <li class="nav-item"><a class="nav-link px-3 rounded-3 fw-semibold" href="<?= url('index.php?action=landing#planes') ?>">Planes</a></li>
            <li class="nav-item"><a class="nav-link px-3 rounded-3 fw-semibold" href="<?= url('index.php?action=landing#faq') ?>">Ayuda</a></li>
            <li class="nav-item"><a class="nav-link px-3 rounded-3 fw-semibold" href="<?= url('index.php?action=landing#contacto') ?>">Contacto</a></li>
            <li class="nav-item mt-3">
                <a class="btn btn-primary rounded-pill px-4 fw-semibold text-white shadow-sm w-100" href="<?= url('login.php') ?>">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
                </a>
            </li>
        </ul>
    </div>
</div>

<main class="min-vh-100">
    <?= $content ?>
</main>

<footer class="bg-dark text-white pt-5 mt-5 pb-4 border-top border-secondary border-opacity-10" id="contacto">
    <div class="container py-4">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <a class="navbar-brand fw-bold d-flex align-items-center text-primary mb-3" href="<?= url() ?>">
                    <i class="bi bi-journal-bookmark-fill me-2 fs-3"></i>
                    <span class="text-white">Northstar<span class="text-primary">LMS</span></span>
                </a>
                <p class="text-white-50 small mb-4">Una plataforma educativa de nivel empresarial para gestionar el conocimiento, desarrollar el talento y certificar competencias con estándares internacionales.</p>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="fw-bold mb-3 text-uppercase small text-primary">Plataforma</h6>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2"><a href="<?= url('index.php?action=catalog') ?>" class="text-white-50 text-decoration-none hover-white">Explorar Cursos</a></li>
                    <li class="mb-2"><a href="<?= url('index.php?action=landing#planes') ?>" class="text-white-50 text-decoration-none hover-white">Planes & Precios</a></li>
                    <li class="mb-2"><a href="<?= url('index.php?action=landing#certificaciones') ?>" class="text-white-50 text-decoration-none hover-white">Certificaciones</a></li>
                    <li class="mb-2"><a href="<?= url('index.php?action=landing#faq') ?>" class="text-white-50 text-decoration-none hover-white">Ayuda & FAQ</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="fw-bold mb-3 text-uppercase small text-primary">Categorías</h6>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2"><a href="<?= url('index.php?action=catalog&categoria=Programación') ?>" class="text-white-50 text-decoration-none hover-white">Programación</a></li>
                    <li class="mb-2"><a href="<?= url('index.php?action=catalog&categoria=Diseño') ?>" class="text-white-50 text-decoration-none hover-white">Diseño UX/UI</a></li>
                    <li class="mb-2"><a href="<?= url('index.php?action=catalog&categoria=Marketing') ?>" class="text-white-50 text-decoration-none hover-white">Marketing</a></li>
                    <li class="mb-2"><a href="<?= url('index.php?action=catalog&categoria=Finanzas') ?>" class="text-white-50 text-decoration-none hover-white">Finanzas & Negocios</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h6 class="fw-bold mb-3 text-uppercase small text-primary">Contacto y Soporte</h6>
                <p class="text-white-50 small mb-3">¿Tienes preguntas o deseas una demo personalizada para tu organización?</p>
                <div class="small text-white-50 mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-envelope me-2 text-primary"></i>
                        <span>soporte@northstarlms.com</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-telephone me-2 text-primary"></i>
                        <span>+1 (555) 234-5678</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-geo-alt me-2 text-primary"></i>
                        <span>Sillicon Valley, CA & Latam</span>
                    </div>
                </div>
            </div>
        </div>
        
        <hr class="border-secondary my-4 opacity-20">
        
        <div class="row align-items-center small text-white-50">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                &copy; <?= date('Y') ?> Northstar LMS. Todos los derechos reservados.
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="text-white-50 text-decoration-none me-3 hover-white">Políticas de Privacidad</a>
                <a href="#" class="text-white-50 text-decoration-none me-3 hover-white">Términos de Servicio</a>
                <a href="#" class="text-white-50 text-decoration-none hover-white">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const navbar = document.getElementById('mainNavbar');
        if (navbar) {
            const onScroll = function () {
                navbar.classList.toggle('is-scrolled', window.scrollY > 8);
            };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        }

        const searchInput = document.getElementById('smartSearch');
        const suggestionsBox = document.getElementById('searchSuggestions');
        if (searchInput && suggestionsBox) {
            const suggestions = [
                'Diseño UX/UI',
                'Programación Backend',
                'Análisis de Datos',
                'Certificación Agile',
                'Liderazgo y Gestión',
                'Marketing Digital',
                'Estrategia de Contenidos'
            ];

            const renderSuggestions = function (value) {
                const query = value.trim().toLowerCase();
                if (!query) {
                    suggestionsBox.classList.remove('active');
                    suggestionsBox.innerHTML = '';
                    return;
                }

                const matches = suggestions.filter(function (item) {
                    return item.toLowerCase().includes(query);
                });

                if (!matches.length) {
                    suggestionsBox.classList.remove('active');
                    suggestionsBox.innerHTML = '';
                    return;
                }

                suggestionsBox.innerHTML = matches.map(function (item) {
                    return '<button type="button" class="search-suggestion" data-value="' + item + '">' + item + '</button>';
                }).join('');
                suggestionsBox.classList.add('active');
            };

            searchInput.addEventListener('input', function () {
                renderSuggestions(this.value);
            });

            suggestionsBox.addEventListener('click', function (event) {
                const button = event.target.closest('.search-suggestion');
                if (!button) return;
                searchInput.value = button.getAttribute('data-value');
                suggestionsBox.classList.remove('active');
                searchInput.closest('form').submit();
            });

            document.addEventListener('click', function (event) {
                if (!searchInput.contains(event.target) && !suggestionsBox.contains(event.target)) {
                    suggestionsBox.classList.remove('active');
                }
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
