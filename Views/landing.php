<!-- HERO PRINCIPAL -->
<section class="py-5 bg-white position-relative overflow-hidden" id="inicio">
    <!-- Efecto de fondo sutil -->
    <div class="position-absolute top-0 start-50 translate-middle-x w-100 h-100 opacity-5" style="background-image: radial-gradient(var(--primary-color) 1px, transparent 0); background-size: 24px 24px;"></div>
    
    <div class="container py-5 position-relative z-1">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <span class="badge rounded-pill bg-light text-primary fw-bold px-3 py-2 mb-3 shadow-sm border border-primary border-opacity-10">
                    <i class="bi bi-rocket-takeoff me-1"></i> Educación Corporativa de Siguiente Nivel
                </span>
                <h1 class="display-4 fw-bold mt-2 text-dark lh-sm">
                    Acelera el Futuro de tu <span class="text-primary">Aprendizaje</span> Profesional
                </h1>
                <p class="lead text-muted mt-3 mb-4 fs-5">
                    La plataforma LMS modular de clase empresarial diseñada para desarrollar competencias clave, certificar habilidades de forma oficial y medir el progreso en tiempo real.
                </p>
                
                <!-- Buscador de cursos integrado -->
                <form action="<?= url('index.php') ?>" method="GET" class="card p-2 border-0 shadow-lg rounded-pill mb-4 bg-light">
                    <input type="hidden" name="action" value="catalog">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="input-group bg-transparent border-0 flex-grow-1 ps-2">
                            <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-transparent border-0 text-dark ps-1" placeholder="¿Qué deseas aprender hoy? Ej: Programación, IA..." style="box-shadow: none;">
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 text-white fw-medium">Buscar</button>
                    </div>
                </form>
                
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-primary rounded-pill px-4 py-3 fw-semibold text-white shadow">
                        Explorar Cursos <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a href="<?= url('login.php') ?>" class="btn btn-outline-secondary rounded-pill px-4 py-3 fw-semibold hover-white">
                        Comenzar Ahora
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6 text-center">
                <div class="position-relative d-inline-block">
                    <!-- Decoración circular de fondo -->
                    <div class="position-absolute top-50 start-50 translate-middle rounded-circle bg-primary bg-opacity-10" style="width: 110%; height: 110%; filter: blur(50px); z-index: -1;"></div>
                    <img src="<?= asset('hero_lms.png') ?>" alt="Ilustración LMS Educativa" class="img-fluid rounded-4 shadow-sm" style="max-height: 480px; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECCIÓN DE INDICADORES / MÉTRICAS -->
<section class="py-4 border-top border-bottom bg-light">
    <div class="container">
        <div class="row g-4 text-center justify-content-center">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="p-3">
                    <h3 class="fw-extrabold text-primary mb-1">+150</h3>
                    <p class="text-muted small mb-0 fw-medium">Cursos Disponibles</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="p-3">
                    <h3 class="fw-extrabold text-dark mb-1">+25K</h3>
                    <p class="text-muted small mb-0 fw-medium">Estudiantes Activos</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="p-3">
                    <h3 class="fw-extrabold text-dark mb-1">+80</h3>
                    <p class="text-muted small mb-0 fw-medium">Profesores Expertos</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="p-3">
                    <h3 class="fw-extrabold text-dark mb-1">+12K</h3>
                    <p class="text-muted small mb-0 fw-medium">Certificados Emitidos</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="p-3">
                    <h3 class="fw-extrabold text-warning mb-1">4.9 / 5.0</h3>
                    <p class="text-muted small mb-0 fw-medium">Valoración Promedio</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECCIÓN DE CATEGORÍAS -->
<section class="py-5 bg-white" id="categorias">
    <div class="container py-4">
        <div class="row align-items-end mb-5">
            <div class="col-md-8 text-center text-md-start">
                <span class="text-primary fw-bold text-uppercase small tracking-wide">Rutas formativas</span>
                <h2 class="fw-bold mt-2 mb-0">Explora por Categorías Temáticas</h2>
                <p class="text-muted mt-2 mb-0">Encuentra exactamente lo que necesitas para tu crecimiento profesional y de tu equipo.</p>
            </div>
            <div class="col-md-4 text-center text-md-end mt-3 mt-md-0">
                <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-outline-primary rounded-pill px-4">Ver Catálogo Completo</a>
            </div>
        </div>
        
        <div class="row g-4">
            <?php 
            $catData = [
                ['name' => 'Programación', 'icon' => 'bi-code-slash', 'color' => '#1a73e8', 'count' => '42 cursos', 'desc' => 'Desarrollo web, frontend, backend, bases de datos y desarrollo móvil.'],
                ['name' => 'Diseño', 'icon' => 'bi-palette', 'color' => '#d97706', 'count' => '25 cursos', 'desc' => 'Diseño de interfaz de usuario UX/UI, diseño gráfico y de producto.'],
                ['name' => 'Administración', 'icon' => 'bi-briefcase', 'color' => '#059669', 'count' => '18 cursos', 'desc' => 'Gestión empresarial, liderazgo de equipos y dirección de operaciones.'],
                ['name' => 'Finanzas', 'icon' => 'bi-cash-coin', 'color' => '#0284c7', 'count' => '12 cursos', 'desc' => 'Contabilidad, planificación financiera corporativa e inversiones.'],
                ['name' => 'Marketing', 'icon' => 'bi-megaphone', 'color' => '#b91c1c', 'count' => '22 cursos', 'desc' => 'SEO, SEM, marketing digital, redes sociales y growth hacking.'],
                ['name' => 'Idiomas', 'icon' => 'bi-translate', 'color' => '#7c3aed', 'count' => '15 cursos', 'desc' => 'Inglés corporativo, técnico, habilidades de comunicación global.'],
                ['name' => 'Ofimática', 'icon' => 'bi-file-earmark-spreadsheet', 'color' => '#2563eb', 'count' => '10 cursos', 'desc' => 'Hojas de cálculo avanzadas, procesadores de texto y herramientas colaborativas.'],
                ['name' => 'Inteligencia Artificial', 'icon' => 'bi-cpu', 'color' => '#0d9488', 'count' => '16 cursos', 'desc' => 'Prompt engineering, machine learning y automatización con IA.'],
                ['name' => 'Ciberseguridad', 'icon' => 'bi-shield-lock', 'color' => '#dc2626', 'count' => '14 cursos', 'desc' => 'Seguridad informática corporativa, hacking ético y cumplimiento.'],
                ['name' => 'Recursos Humanos', 'icon' => 'bi-people', 'color' => '#4f46e5', 'count' => '11 cursos', 'desc' => 'Reclutamiento, retención del talento y clima organizacional.'],
            ];
            foreach ($catData as $cat):
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 card-interactive" style="transition: all 0.3s ease; border-top: 4px solid <?= $cat['color'] ?> !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 p-3 text-white d-inline-flex align-items-center justify-content-center" style="background-color: <?= $cat['color'] ?>; width: 48px; height: 48px;">
                            <i class="bi <?= $cat['icon'] ?> fs-5"></i>
                        </div>
                        <span class="badge bg-light text-muted rounded-pill fs-7"><?= $cat['count'] ?></span>
                    </div>
                    <h5 class="fw-bold text-dark mb-2"><?= e($cat['name']) ?></h5>
                    <p class="text-muted small mb-4 flex-grow-1"><?= e($cat['desc']) ?></p>
                    <a href="<?= url('index.php?action=catalog&categoria=' . urlencode($cat['name'])) ?>" class="btn btn-link p-0 text-decoration-none fw-semibold text-start" style="color: <?= $cat['color'] ?>;">
                        Explorar Categoría <i class="bi bi-chevron-right small"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECCIÓN DE CURSOS DESTACADOS -->
<section class="py-5 bg-light" id="cursos">
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-5">
            <div>
                <span class="text-primary fw-bold text-uppercase small tracking-wide">Cursos Premium</span>
                <h2 class="fw-bold mt-2 mb-0">Programas Académicos Destacados</h2>
                <p class="text-muted mt-2 mb-0">Cursos estructurados para la alta empleabilidad de la mano de expertos internacionales.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-primary rounded-pill px-4 text-white shadow-sm">Ver Catálogo Completo</a>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if (empty($courses)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No hay cursos destacados disponibles en este momento. Visita el catálogo completo.</p>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $index => $course): 
                    $level = $course['nivel'] ?? 'Todos los niveles';
                    $duration = $course['duracion'] ?? 'Flexible';
                    $category = $course['categoria_nombre'] ?? 'General';
                    $cover = $course['imagen'] ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80';
                    $instructor = $course['instructor'] ?? 'Profesor Asignado';
                    $price = $course['precio'] ?? 0;
                    $isFree = $course['gratuito'] ?? 0;
                    
                    // Mock ratings dynamically based on index to look professional and authentic
                    $rating = 4.5 + ($index % 5) * 0.1;
                    $reviewsCount = 20 + ($index * 13);
                    $studentsCount = 120 + ($index * 58);
                    
                    // States/badges
                    $badgeText = ($index == 0) ? 'Destacado' : (($index == 1) ? 'Popular' : 'Nuevo');
                    $badgeBg = ($index == 0) ? 'bg-primary' : (($index == 1) ? 'bg-warning text-dark' : 'bg-success');
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-interactive" style="transition: all 0.3s ease;">
                        <div class="position-relative">
                            <img src="<?= e($cover) ?>" class="card-img-top" alt="<?= e($course['nombre']) ?>" style="height: 200px; object-fit: cover;">
                            <span class="badge position-absolute top-0 start-0 m-3 <?= $badgeBg ?> text-white rounded-pill px-3 py-2 shadow-sm"><?= $badgeText ?></span>
                            <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white px-2 py-1 m-3 rounded-2 small">
                                <i class="bi bi-clock me-1"></i><?= e($duration) ?>
                            </div>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge rounded-pill bg-light text-primary border px-2.5 py-1.5 fs-7 fw-bold"><?= e($category) ?></span>
                                <small class="text-muted"><i class="bi bi-bar-chart me-1"></i><?= e($level) ?></small>
                            </div>
                            <h5 class="card-title fw-bold text-dark mb-2 lh-base"><?= e($course['nombre']) ?></h5>
                            <p class="text-muted small mb-3 text-truncate-2"><?= e($course['descripcion_corta']) ?></p>
                            
                            <!-- Instructor and rating -->
                            <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-3 mt-auto">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="small">
                                    <div class="fw-semibold text-dark"><?= e($instructor) ?></div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">Docente Principal</div>
                                </div>
                            </div>
                            
                            <!-- Rating and Price Row -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <span class="fw-bold text-dark"><?= number_format($rating, 1) ?></span>
                                    <span class="text-muted small">(<?= $reviewsCount ?>)</span>
                                </div>
                                <div class="fw-bold fs-5 text-primary">
                                    <?= ($isFree || $price == 0) ? 'Gratuito' : '$' . number_format($price, 2, ',', '.') ?>
                                </div>
                            </div>
                            
                            <!-- Action buttons -->
                            <div class="row g-2">
                                <div class="col-9">
                                    <a href="<?= url('index.php?action=course&id=' . (int) $course['id']) ?>" class="btn btn-primary rounded-pill w-100 text-white fw-semibold py-2">
                                        Ver Curso
                                    </a>
                                </div>
                                <div class="col-3">
                                    <button class="btn btn-light rounded-circle w-100 py-2" title="Agregar a Favoritos" onclick="alert('Se ha guardado en favoritos temporales.');">
                                        <i class="bi bi-heart text-danger"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- SECCIÓN DE BENEFICIOS / DIFERENCIADORES -->
<section class="py-5 bg-white" id="beneficios">
    <div class="container py-4">
        <div class="text-center mb-5 max-width-600 mx-auto">
            <span class="text-primary fw-bold text-uppercase small tracking-wide">¿Por qué elegirnos?</span>
            <h2 class="fw-bold mt-2">La Experiencia Educativa Definitiva</h2>
            <p class="text-muted">Desarrollamos una metodología optimizada para la asimilación del conocimiento y el éxito laboral.</p>
        </div>
        
        <div class="row g-4">
            <?php 
            $beneficios = [
                ['title' => 'Aprende a tu ritmo', 'icon' => 'bi-hourglass-split', 'desc' => 'Sin presiones ni horarios fijos. Accede al contenido las 24 horas del día, los 7 días de la semana.'],
                ['title' => 'Acceso multiplataforma', 'icon' => 'bi-laptop', 'desc' => 'Diseño completamente responsivo optimizado para PC, tablet y smartphone de manera fluida.'],
                ['title' => 'Profesores especializados', 'icon' => 'bi-person-badge', 'desc' => 'Profesionales activos y expertos de la industria que enseñan basándose en la experiencia real.'],
                ['title' => 'Material descargable', 'icon' => 'bi-cloud-download', 'desc' => 'Descarga guías, lecturas de apoyo, plantillas y códigos fuente para tu estudio sin conexión.'],
                ['title' => 'Evaluaciones y tareas', 'icon' => 'bi-journal-check', 'desc' => 'Pon a prueba tus conocimientos con cuestionarios interactivos y proyectos prácticos calificados.'],
                ['title' => 'Certificados con QR', 'icon' => 'bi-qr-code', 'desc' => 'Validez internacional instantánea mediante códigos de verificación QR y firma electrónica segura.'],
                ['title' => 'Soporte y comunidad', 'icon' => 'bi-chat-left-text', 'desc' => 'Accede a foros de discusión y resuelve dudas directamente con los instructores y tus compañeros.'],
                ['title' => 'Actualizaciones permanentes', 'icon' => 'bi-arrow-repeat', 'desc' => 'El contenido de los programas se renueva constantemente de acuerdo con los cambios tecnológicos.'],
            ];
            foreach ($beneficios as $b):
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 p-4 rounded-4 shadow-sm bg-light text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 56px; height: 56px;">
                        <i class="bi <?= $b['icon'] ?> fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2"><?= e($b['title']) ?></h5>
                    <p class="text-muted small mb-0"><?= e($b['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECCIÓN DE CERTIFICADOS -->
<section class="py-5 bg-light" id="certificaciones">
    <div class="container py-4">
        <div class="row align-items-center gy-5">
            <div class="col-lg-5">
                <span class="text-primary fw-bold text-uppercase small tracking-wide">Certificación con Validez</span>
                <h2 class="fw-bold mt-2 text-dark">Impulsa tu Perfil con Certificados Verificables</h2>
                <p class="text-muted mt-3 mb-4">
                    Al completar de forma satisfactoria las actividades, proyectos y evaluaciones del curso, recibirás automáticamente un certificado oficial digital.
                </p>
                
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 mt-1">
                        <i class="bi bi-shield-check fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Verificación por Código QR</h6>
                        <p class="text-muted small mb-0">Cualquier reclutador o empresa puede escanear el código único para validar la autenticidad del documento.</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 mt-1">
                        <i class="bi bi-patch-check fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Firma Electrónica Incorporada</h6>
                        <p class="text-muted small mb-0">Avalado y firmado digitalmente por los instructores líderes y la dirección académica del LMS.</p>
                    </div>
                </div>
                
                <button class="btn btn-primary rounded-pill px-4 text-white fw-semibold" onclick="alert('Ejemplo de validación: Certificado ID #NW-9883-X');">
                    Validar un Certificado
                </button>
            </div>
            
            <div class="col-lg-7 text-center">
                <!-- Modelo de Certificado Visual Premium -->
                <div class="card border border-light p-4 rounded-4 shadow-lg bg-white mx-auto position-relative" style="max-width: 600px; border-width: 8px !important;">
                    <div class="border border-secondary border-opacity-10 p-4 rounded-3 text-center position-relative">
                        <!-- Logos decorativos en esquinas -->
                        <div class="position-absolute top-0 start-0 m-2 opacity-50"><i class="bi bi-gem"></i></div>
                        <div class="position-absolute top-0 end-0 m-2 opacity-50"><i class="bi bi-award"></i></div>
                        
                        <!-- Header -->
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                            <i class="bi bi-journal-bookmark-fill text-primary fs-3"></i>
                            <span class="fw-bold fs-5 text-dark">Northstar <span class="text-primary">Academy</span></span>
                        </div>
                        
                        <div class="text-uppercase tracking-wider small text-muted mb-2" style="letter-spacing: 0.15em;">Certificado de Finalización</div>
                        <p class="text-muted small mb-4">Otorgado oficialmente a:</p>
                        
                        <h3 class="fw-bold text-dark font-serif border-bottom pb-2 max-width-400 mx-auto mb-3" style="font-family: Georgia, serif;">Sofía Valentina Mendoza</h3>
                        
                        <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">
                            Por haber aprobado satisfactoriamente todos los requisitos académicos y de evaluación del programa de nivel profesional:
                        </p>
                        
                        <h5 class="fw-bold text-primary mb-4">Arquitectura de Software y Patrones de Diseño</h5>
                        
                        <!-- Footer del certificado -->
                        <div class="row align-items-end mt-4 pt-3 border-top border-light gy-3 text-start">
                            <div class="col-sm-4 text-center">
                                <div class="font-signature text-muted mb-1" style="font-family: 'Brush Script MT', cursive, serif; font-size: 1.3rem;">Alejandro R.</div>
                                <div class="border-top pt-1 small text-muted" style="font-size: 0.7rem;">
                                    <strong>Alejandro Ruiz</strong><br>Instructor Principal
                                </div>
                            </div>
                            <div class="col-sm-4 text-center">
                                <div class="font-signature text-muted mb-1" style="font-family: 'Brush Script MT', cursive, serif; font-size: 1.3rem;">Elena Cast.</div>
                                <div class="border-top pt-1 small text-muted" style="font-size: 0.7rem;">
                                    <strong>Dr. Elena Castelar</strong><br>Directora Académica
                                </div>
                            </div>
                            <div class="col-sm-4 text-center d-flex flex-column align-items-center">
                                <!-- QR Mock -->
                                <div class="bg-light p-1 border rounded mb-1" style="width: 50px; height: 50px;">
                                    <i class="bi bi-qr-code fs-1 d-block lh-1 text-dark" style="font-size: 2.5rem !important;"></i>
                                </div>
                                <span class="text-muted" style="font-size: 0.65rem;">ID: NS-2026-8947</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECCIÓN DE PLANES / PRECIOS -->
<section class="py-5 bg-white" id="planes">
    <div class="container py-4">
        <div class="text-center mb-5 max-width-600 mx-auto">
            <span class="text-primary fw-bold text-uppercase small tracking-wide">Planes SaaS</span>
            <h2 class="fw-bold mt-2">Membresías Adaptadas a tu Ritmo</h2>
            <p class="text-muted">Elige el plan ideal para expandir tus conocimientos y acelerar tu carrera laboral.</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <!-- Plan Basic -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border p-4 rounded-4 shadow-sm bg-white">
                    <div class="mb-4">
                        <span class="badge bg-light text-secondary rounded-pill px-3 py-2 mb-2">Básico</span>
                        <h4 class="fw-bold text-dark mt-2 mb-1">Acceso Individual</h4>
                        <p class="text-muted small">Ideal para estudiantes independientes.</p>
                        <div class="d-flex align-items-baseline mt-3">
                            <span class="fs-2 fw-bold text-dark">$19</span>
                            <span class="text-muted ms-1">/ mes</span>
                        </div>
                    </div>
                    <ul class="list-unstyled small text-muted flex-grow-1 mb-4">
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Acceso a 40 cursos seleccionados</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Contenido grabado y lecturas</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Soporte comunitario en foros</li>
                        <li class="mb-2.5 text-decoration-line-through text-muted opacity-50"><i class="bi bi-x text-danger me-2"></i>Certificados verificables oficiales</li>
                        <li class="mb-2.5 text-decoration-line-through text-muted opacity-50"><i class="bi bi-x text-danger me-2"></i>Clases en vivo y mentorías</li>
                    </ul>
                    <a href="<?= url('login.php') ?>" class="btn btn-outline-primary rounded-pill w-100 fw-semibold py-2.5">
                        Empezar Gratis
                    </a>
                </div>
            </div>
            
            <!-- Plan Pro -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border border-primary p-4 rounded-4 shadow bg-white position-relative">
                    <span class="badge bg-primary text-white position-absolute top-0 end-0 m-4 rounded-pill px-3 py-1.5 small shadow-sm">Recomendado</span>
                    <div class="mb-4">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 mb-2">Profesional</span>
                        <h4 class="fw-bold text-dark mt-2 mb-1">Pase Platino</h4>
                        <p class="text-muted small">Para profesionales altamente enfocados.</p>
                        <div class="d-flex align-items-baseline mt-3">
                            <span class="fs-2 fw-bold text-dark">$39</span>
                            <span class="text-muted ms-1">/ mes</span>
                        </div>
                    </div>
                    <ul class="list-unstyled small text-muted flex-grow-1 mb-4">
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Acceso ilimitado a +150 cursos</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Certificados con QR y firma digital</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Materiales y códigos descargables</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Soporte prioritario del instructor</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Exámenes e insignias de perfil</li>
                    </ul>
                    <a href="<?= url('login.php') ?>" class="btn btn-primary text-white rounded-pill w-100 fw-semibold py-2.5 shadow-sm">
                        Suscribirse Ahora
                    </a>
                </div>
            </div>
            
            <!-- Plan Enterprise -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border p-4 rounded-4 shadow-sm bg-white">
                    <div class="mb-4">
                        <span class="badge bg-light text-dark rounded-pill px-3 py-2 mb-2">Empresas</span>
                        <h4 class="fw-bold text-dark mt-2 mb-1">Corporate Suite</h4>
                        <p class="text-muted small">Para equipos de trabajo y organizaciones.</p>
                        <div class="d-flex align-items-baseline mt-3">
                            <span class="fs-4 fw-bold text-dark">Consulte tarifas</span>
                            <span class="text-muted ms-1"> / anual</span>
                        </div>
                    </div>
                    <ul class="list-unstyled small text-muted flex-grow-1 mb-4">
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Cuentas ilimitadas para empleados</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Panel administrativo de progreso corporativo</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Cursos y contenidos personalizados</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Integración API con HR software</li>
                        <li class="mb-2.5"><i class="bi bi-check2 text-primary me-2"></i>Soporte técnico y account manager dedicado</li>
                    </ul>
                    <a href="mailto:soporte@northstarlms.com" class="btn btn-outline-primary rounded-pill w-100 fw-semibold py-2.5">
                        Contactar Ventas
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECCIÓN DE TESTIMONIOS -->
<section class="py-5 bg-light" id="testimonios">
    <div class="container py-4">
        <div class="text-center mb-5 max-width-600 mx-auto">
            <span class="text-primary fw-bold text-uppercase small tracking-wide">Testimonios de Clientes</span>
            <h2 class="fw-bold mt-2">Lo que dicen nuestros estudiantes</h2>
            <p class="text-muted">Cientos de profesionales y equipos de recursos humanos han transformado sus habilidades con nosotros.</p>
        </div>
        
        <!-- Carrusel de Testimoniales -->
        <div id="testimoniosCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators mb-0">
                <button type="button" data-bs-target="#testimoniosCarousel" data-bs-slide-to="0" class="active bg-primary" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#testimoniosCarousel" data-bs-slide-to="1" class="bg-primary" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#testimoniosCarousel" data-bs-slide-to="2" class="bg-primary" aria-label="Slide 3"></button>
            </div>
            
            <div class="carousel-inner pb-5">
                <div class="carousel-item active">
                    <div class="card border-0 shadow-sm p-4 rounded-4 max-width-700 mx-auto text-center bg-white">
                        <div class="d-flex justify-content-center text-warning mb-3">
                            <i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="text-muted fs-5 italic px-lg-5 mb-4">
                            "Gracias a los cursos de Programación y Arquitectura logré aprobar mi entrevista técnica para Senior Frontend Engineer. El sistema modular es una maravilla y la verificación QR del certificado agilizó el proceso."
                        </p>
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-fill fs-4"></i>
                            </div>
                            <div class="text-start">
                                <h6 class="fw-bold mb-0 text-dark">Carlos Mario Restrepo</h6>
                                <span class="text-muted small">MercadoLibre · Software Developer</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <div class="card border-0 shadow-sm p-4 rounded-4 max-width-700 mx-auto text-center bg-white">
                        <div class="d-flex justify-content-center text-warning mb-3">
                            <i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="text-muted fs-5 italic px-lg-5 mb-4">
                            "Capacitamos a todo nuestro equipo de diseño UX en Northstar LMS. La flexibilidad horaria nos permitió avanzar sin descuidar las entregas del sprint. Altamente recomendado para empresas que escalan."
                        </p>
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-fill fs-4"></i>
                            </div>
                            <div class="text-start">
                                <h6 class="fw-bold mb-0 text-dark">Juliana Vanessa Gaviria</h6>
                                <span class="text-muted small">Rappi · UX/UI Director</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <div class="card border-0 shadow-sm p-4 rounded-4 max-width-700 mx-auto text-center bg-white">
                        <div class="d-flex justify-content-center text-warning mb-3">
                            <i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-half"></i>
                        </div>
                        <p class="text-muted fs-5 italic px-lg-5 mb-4">
                            "La calidad educativa de las lecciones y exámenes prácticos supera con creces lo que he visto en otras plataformas genéricas. Aprendí Prompt Engineering e Inteligencia Artificial aplicada en días."
                        </p>
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-fill fs-4"></i>
                            </div>
                            <div class="text-start">
                                <h6 class="fw-bold mb-0 text-dark">Sebastian Gomez</h6>
                                <span class="text-muted small">Bancolombia · Business Intelligence Analyst</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#testimoniosCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon rounded-circle p-3 bg-dark opacity-50" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimoniosCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon rounded-circle p-3 bg-dark opacity-50" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<!-- SECCIÓN DE PREGUNTAS FRECUENTES (FAQ) -->
<section class="py-5 bg-white" id="faq">
    <div class="container py-4">
        <div class="text-center mb-5 max-width-600 mx-auto">
            <span class="text-primary fw-bold text-uppercase small tracking-wide">Resolviendo Dudas</span>
            <h2 class="fw-bold mt-2">Preguntas Frecuentes</h2>
            <p class="text-muted">¿Tienes alguna duda sobre la plataforma? Aquí te respondemos las más comunes.</p>
        </div>
        
        <div class="accordion accordion-flush max-width-800 mx-auto shadow-sm border rounded-4 overflow-hidden" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button fw-semibold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        ¿Cómo funciona la inscripción a los cursos?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small lh-relaxed">
                        Solo debes registrarte o iniciar sesión en la plataforma, navegar por nuestro catálogo y seleccionar el curso que te interesa. En la página de detalles, haz clic en el botón "Inscribirse" para agregarlo automáticamente a tu espacio personal ("Mi aprendizaje"). Si es un curso pago, requerirá la suscripción activa.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed fw-semibold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        ¿Cuándo y cómo obtengo mi certificado?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small lh-relaxed">
                        El certificado se genera de manera automática una vez marques como completadas todas las lecciones y apruebes la evaluación final de cada uno de los módulos del programa de formación con una nota mínima del 80%. Podrás descargarlo en PDF desde tu dashboard personal.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed fw-semibold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        ¿Los cursos tienen horarios fijos o son grabados?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small lh-relaxed">
                        La mayoría de nuestros cursos combinan clases grabadas de alta definición, lecturas y materiales interactivos que puedes consumir a tu ritmo, lo que te permite estudiar en tus tiempos libres. Algunos programas avanzados incluyen mentorías grupales programadas que se anuncian con anticipación.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button collapsed fw-semibold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        ¿Cómo se validan los certificados ante reclutadores?
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small lh-relaxed">
                        Cada certificado digital incluye un código de identificación único y un código QR impreso. Los reclutadores o empleadores pueden escanear el QR o ingresar el ID en nuestra sección pública de validación para verificar en tiempo real que el estudiante efectivamente cursó y aprobó el programa con nosotros.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFive">
                    <button class="accordion-button collapsed fw-semibold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                        ¿Hay reembolsos o cancelaciones de los planes?
                    </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted small lh-relaxed">
                        Sí, puedes cancelar tu membresía mensual Pro en cualquier momento desde tu panel de usuario sin cargos adicionales. Seguirás teniendo acceso a los cursos hasta que finalice el periodo de facturación actual. Ofrecemos una garantía de satisfacción de 7 días.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
