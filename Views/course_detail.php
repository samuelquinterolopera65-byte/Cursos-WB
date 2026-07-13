<?php
$courseTitle = $course['nombre'] ?? 'Curso sin título';
$cover = $course['imagen'] ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80';
$descriptionLong = trim((string) ($course['descripcion_larga'] ?? ''));
$descriptionShort = trim((string) ($course['descripcion_corta'] ?? ''));
$description = $descriptionLong !== '' ? $descriptionLong : $descriptionShort;
$materials = array_filter(array_map('trim', preg_split('/\r\n|\n|\r/', (string) ($course['materiales'] ?? ''))));

// Curriculum Preview Mock Data (Syllabus)
$modules = [
    [
        'title' => 'Módulo 1 · Fundamentos y Conceptos Básicos',
        'lessons' => [
            ['title' => '1.1 Bienvenido al curso e introducción', 'type' => 'video', 'duration' => '12 min', 'preview' => true],
            ['title' => '1.2 Terminología clave y marco teórico', 'type' => 'document', 'duration' => '5 págs', 'preview' => true],
            ['title' => '1.3 Primeros pasos y configuración', 'type' => 'video', 'duration' => '22 min', 'preview' => false],
            ['title' => 'Evaluación de módulo 1', 'type' => 'quiz', 'duration' => '10 preguntas', 'preview' => false]
        ]
    ],
    [
        'title' => 'Módulo 2 · Desarrollo Práctico y Casos de Estudio',
        'lessons' => [
            ['title' => '2.1 Implementación guiada paso a paso', 'type' => 'video', 'duration' => '35 min', 'preview' => false],
            ['title' => '2.2 Resolución de errores comunes en producción', 'type' => 'video', 'duration' => '18 min', 'preview' => false],
            ['title' => '2.3 Proyecto práctico de módulo', 'type' => 'task', 'duration' => '1 entrega', 'preview' => false]
        ]
    ],
    [
        'title' => 'Módulo 3 · Optimización, Seguridad y Cierre',
        'lessons' => [
            ['title' => '3.1 Auditoría, rendimiento y buenas prácticas', 'type' => 'video', 'duration' => '25 min', 'preview' => false],
            ['title' => '3.2 Examen Final Teórico', 'type' => 'quiz', 'duration' => '25 preguntas', 'preview' => false],
            ['title' => '3.3 Entrega del Proyecto Final Académico', 'type' => 'task', 'duration' => '1 entrega', 'preview' => false]
        ]
    ]
];

// Instructor Mock Details (Professional Details)
$instName = $course['instructor'] ?? 'Instructor Especializado';
$instRole = 'Director de Ingeniería y Consultor Tecnológico';
$instSpecialty = $course['categoria_nombre'] ?? 'Tecnología e Información';
$instBio = 'Profesional con más de 12 años de experiencia liderando equipos de desarrollo de software y sistemas empresariales. Apasionado por la educación virtual y la enseñanza práctica orientada a proyectos.';
$instExp = 'Ha trabajado en proyectos internacionales y actualmente ejerce como consultor senior de arquitectura cloud.';

// Reviews Mock Data
$reviews = [
    ['name' => 'Adriana María Restrepo', 'rating' => 5, 'date' => 'Hace 2 semanas', 'comment' => 'El curso está excelentemente estructurado. El profesor responde todas las dudas en los foros y el material de descarga es muy completo.'],
    ['name' => 'Luis Fernando Gómez', 'rating' => 4, 'date' => 'Hace 1 mes', 'comment' => 'Muy buen contenido, explicaciones claras y ejemplos prácticos aplicados a la realidad corporativa. Me sirvió mucho el módulo 2.'],
    ['name' => 'Camila Andrea Torres', 'rating' => 5, 'date' => 'Hace 2 meses', 'comment' => 'Excelente pedagogía. El sistema de evaluación realmente pone a prueba lo aprendido. Ya obtuve mi certificado oficial.']
];

// Related Courses Mock Data
$relatedCourses = [
    ['id' => 101, 'nombre' => 'Arquitectura Limpia en PHP 8', 'instructor' => 'Ing. Martin Fowler', 'precio' => 49.99, 'rating' => 4.8, 'imagen' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=400&q=80'],
    ['id' => 102, 'nombre' => 'Bases de Datos Relacionales Avanzadas', 'instructor' => 'Dra. Grace Hopper', 'precio' => 29.99, 'rating' => 4.7, 'imagen' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?auto=format&fit=crop&w=400&q=80']
];
?>

<!-- BANNER PRINCIPAL DEL CURSO -->
<section class="py-5 bg-dark text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;">
    <div class="position-absolute top-0 end-0 w-50 h-100 opacity-10" style="background-image: radial-gradient(var(--primary-color) 1.5px, transparent 0); background-size: 20px 20px;"></div>
    
    <div class="container py-4 position-relative z-1">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <!-- Breadcrumbs -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-white-50 small mb-3">
                        <li class="breadcrumb-item"><a href="<?= url('index.php?action=landing') ?>" class="text-white-50 text-decoration-none hover-white">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('index.php?action=catalog') ?>" class="text-white-50 text-decoration-none hover-white">Cursos</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Detalle</li>
                    </ol>
                </nav>
                
                <!-- Badges -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge rounded-pill bg-primary px-3 py-2 text-white shadow-sm"><?= e($course['categoria_nombre'] ?? 'General') ?></span>
                    <span class="badge rounded-pill bg-secondary px-3 py-2 text-dark shadow-sm"><?= e($course['nivel'] ?? 'Todos los niveles') ?></span>
                    <span class="badge rounded-pill bg-light bg-opacity-20 px-3 py-2 text-white border border-white border-opacity-10"><i class="bi bi-translate me-1"></i><?= e($course['idioma'] ?? 'Español') ?></span>
                </div>
                
                <!-- Título y Descripción -->
                <h1 class="display-6 fw-bold text-white mb-3"><?= e($courseTitle) ?></h1>
                <p class="text-white-50 fs-5 mb-4 max-width-700"><?= e($course['descripcion_corta']) ?></p>
                
                <!-- Indicadores de Calidad -->
                <div class="d-flex flex-wrap align-items-center gap-4 text-white-50 small">
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-star-fill text-warning fs-6"></i>
                        <span class="text-white fw-bold">4.8</span>
                        <span>(142 valoraciones)</span>
                    </div>
                    <div>
                        <i class="bi bi-people me-1.5"></i><span class="text-white fw-medium">854 estudiantes</span>
                    </div>
                    <div>
                        <i class="bi bi-calendar3 me-1.5"></i>Actualizado el <?= !empty($course['publicado_en']) ? date('d/m/Y', strtotime($course['publicado_en'])) : date('d/m/Y') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CUERPO PRINCIPAL DEL DETALLE (DOS COLUMNAS) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- COLUMNA IZQUIERDA: CONTENIDOS (8 COLS) -->
            <div class="col-lg-8">
                <!-- Tarjeta de Descripción Detallada -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
                    <h4 class="fw-bold text-dark mb-3"><i class="bi bi-journal-text text-primary me-2"></i>Descripción del Curso</h4>
                    <div class="text-muted lh-relaxed small-text-markdown">
                        <?= nl2br(e($description ?: 'Este programa académico está especialmente diseñado para capacitar a los alumnos en habilidades prácticas demandadas en el mercado corporativo. A través de lecciones guiadas por video, actividades teóricas y evaluaciones continuas, los estudiantes asimilarán el conocimiento paso a paso de forma autónoma.')) ?>
                    </div>
                    
                    <?php if (!empty($materials)): ?>
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-2.5">Recursos y Materiales Incluidos</h6>
                        <div class="row g-2">
                            <?php foreach ($materials as $material): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 small">
                                    <i class="bi bi-file-earmark-arrow-down text-primary fs-5"></i>
                                    <span class="text-muted"><?= e($material) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tarjeta del Contenido del Curso / Syllabus -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-list-task text-primary me-2"></i>Estructura Curricular</h4>
                        <span class="badge bg-light text-muted rounded-pill px-3 py-2 border">3 Módulos · 10 Lecciones</span>
                    </div>
                    <p class="text-muted small mb-4">Haz clic en los módulos para explorar las unidades de estudio. Las primeras lecciones cuentan con vista previa gratuita.</p>
                    
                    <!-- Acordeón de Lecciones -->
                    <div class="accordion accordion-flush border rounded-4 overflow-hidden" id="curriculumAccordion">
                        <?php foreach ($modules as $mIndex => $module): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="modHeading<?= $mIndex ?>">
                                <button class="accordion-button fw-bold text-dark py-3 <?= $mIndex > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#modCollapse<?= $mIndex ?>" aria-expanded="<?= $mIndex === 0 ? 'true' : 'false' ?>" aria-controls="modCollapse<?= $mIndex ?>">
                                    <div class="d-flex justify-content-between w-100 pe-3 flex-wrap gap-2">
                                        <span><?= e($module['title']) ?></span>
                                        <span class="badge bg-light text-muted rounded-pill fs-7 font-normal"><?= count($module['lessons']) ?> actividades</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="modCollapse<?= $mIndex ?>" class="accordion-collapse collapse <?= $mIndex === 0 ? 'show' : '' ?>" aria-labelledby="modHeading<?= $mIndex ?>" data-bs-parent="#curriculumAccordion">
                                <div class="accordion-body p-0">
                                    <ul class="list-group list-group-flush mb-0">
                                        <?php foreach ($module['lessons'] as $lIndex => $lesson): 
                                            // Icon matching
                                            $icon = 'bi-play-circle-fill text-muted';
                                            if ($lesson['type'] === 'document') $icon = 'bi-file-earmark-text text-muted';
                                            if ($lesson['type'] === 'quiz') $icon = 'bi-patch-question text-muted';
                                            if ($lesson['type'] === 'task') $icon = 'bi-clipboard-check text-muted';
                                        ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <i class="bi <?= $icon ?>"></i>
                                                <span class="text-dark small <?= (!$isEnrolled && !$lesson['preview']) ? 'text-muted' : '' ?>"><?= e($lesson['title']) ?></span>
                                                <?php if ($lesson['preview']): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill font-normal fs-8">Vista previa</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small" style="font-size: 0.75rem;"><?= $lesson['duration'] ?></span>
                                                <?php if (!$isEnrolled && !$lesson['preview']): ?>
                                                    <i class="bi bi-lock-fill text-muted" title="Bloqueado hasta inscribirse"></i>
                                                <?php elseif ($lesson['preview']): ?>
                                                    <a href="#" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;" onclick="alert('Reproduciendo vista previa gratuita...'); return false;"><i class="bi bi-play-fill"></i> Ver</a>
                                                <?php else: ?>
                                                    <i class="bi bi-unlock text-success" title="Desbloqueado"></i>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Tarjeta del Instructor -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
                    <h4 class="fw-bold text-dark mb-4"><i class="bi bi-person-badge text-primary me-2"></i>Sobre el Instructor</h4>
                    
                    <div class="row align-items-center g-4">
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center text-primary border" style="width: 100px; height: 100px;">
                                <i class="bi bi-person-bounding-box fs-1"></i>
                            </div>
                            <div class="mt-2 text-warning small">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                            <small class="text-muted">4.9 promedio</small>
                        </div>
                        
                        <div class="col-md-9">
                            <h5 class="fw-bold text-dark mb-1"><?= e($instName) ?></h5>
                            <p class="text-primary small mb-2 fw-medium"><?= e($instRole) ?> · <span class="text-muted"><?= e($instSpecialty) ?></span></p>
                            <p class="text-muted small mb-3"><?= e($instBio) ?></p>
                            <p class="text-muted small mb-3"><strong>Experiencia:</strong> <?= e($instExp) ?></p>
                            
                            <!-- Redes sociales mocked -->
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-light btn-sm rounded-circle text-muted" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                                <a href="#" class="btn btn-light btn-sm rounded-circle text-muted" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                                <a href="#" class="btn btn-light btn-sm rounded-circle text-muted" title="Sitio Web"><i class="bi bi-globe"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta de Reseñas / Comentarios -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-chat-heart text-primary me-2"></i>Reseñas de Alumnos</h4>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4 fw-bold text-dark">4.8</span>
                            <span class="text-warning small"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></span>
                            <span class="text-muted small">(142)</span>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-column gap-3.5 mb-4">
                        <?php foreach ($reviews as $rev): ?>
                        <div class="p-3 bg-light rounded-4">
                            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-white text-muted border d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="bi bi-person-fill small"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark small"><?= e($rev['name']) ?></h6>
                                        <span class="text-muted small" style="font-size: 0.72rem;"><?= $rev['date'] ?></span>
                                    </div>
                                </div>
                                <div class="text-warning small">
                                    <?php for ($i=0; $i<$rev['rating']; $i++): ?>
                                        <i class="bi bi-star-fill"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-muted mb-0 small lh-relaxed"><?= e($rev['comment']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button class="btn btn-outline-secondary rounded-pill px-4 btn-sm mx-auto d-block" onclick="alert('Mostrando listado de opiniones...');">Ver todas las reseñas</button>
                </div>
                
                <!-- Cursos Relacionados -->
                <div class="mb-4">
                    <h4 class="fw-bold text-dark mb-3"><i class="bi bi-diagram-2 text-primary me-2"></i>Cursos Relacionados</h4>
                    <div class="row g-3">
                        <?php foreach ($relatedCourses as $rel): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white card-interactive" style="transition: all 0.3s ease;">
                                <div class="row g-0 h-100">
                                    <div class="col-4">
                                        <img src="<?= e($rel['imagen']) ?>" class="img-fluid h-100 w-100" alt="<?= e($rel['nombre']) ?>" style="object-fit: cover; min-height: 120px;">
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body p-3 d-flex flex-column h-100 justify-content-between">
                                            <h6 class="fw-bold text-dark mb-1 text-truncate-2 small"><?= e($rel['nombre']) ?></h6>
                                            <span class="text-muted small" style="font-size: 0.72rem;">Prof. <?= e($rel['instructor']) ?></span>
                                            <div class="d-flex justify-content-between align-items-center mt-2.5">
                                                <div class="d-flex align-items-center text-warning small" style="font-size: 0.75rem;">
                                                    <i class="bi bi-star-fill me-1"></i><?= $rel['rating'] ?>
                                                </div>
                                                <span class="fw-bold text-primary small">$<?= number_format($rel['precio'], 2, ',', '.') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- COLUMNA DERECHA: COMPRA / DETALLES DE COMPRA (4 COLS) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white position-sticky" style="top: 100px;">
                    <img src="<?= e($cover) ?>" class="card-img-top" alt="<?= e($courseTitle) ?>" style="height: 180px; object-fit: cover;">
                    
                    <div class="card-body p-4">
                        <!-- Precio destacado -->
                        <div class="d-flex align-items-baseline mb-3 gap-2">
                            <span class="fs-2 fw-bold text-primary">
                                <?= ($course['gratuito'] == 1 || $course['precio'] == 0) ? 'Gratuito' : '$' . number_format((float)$course['precio'], 2, ',', '.') ?>
                            </span>
                            <?php if ($course['precio'] > 0): ?>
                                <span class="text-muted text-decoration-line-through small">$<?= number_format((float)$course['precio'] * 1.5, 2, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Formulario de inscripción y CTA -->
                        <div class="d-flex flex-column gap-2 mb-4">
                            <?php if (!empty($_SESSION['user_id'])): ?>
                                <?php if ($isEnrolled): ?>
                                    <a href="<?= url('index.php?action=course-progress&id=' . (int) $course['id']) ?>" class="btn btn-success rounded-pill w-100 text-white fw-bold py-2.5 shadow-sm">
                                        <i class="bi bi-play-circle me-1.5"></i> Continuar Aprendiendo
                                    </a>
                                <?php else: ?>
                                    <form method="post" action="<?= url('index.php?action=enroll') ?>" class="w-100">
                                        <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                                        <button type="submit" class="btn btn-primary rounded-pill w-100 text-white fw-bold py-2.5 shadow-sm">
                                            Inscribirse Ahora
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (!empty($_SESSION['user_role']) && in_array((int) $_SESSION['user_role'], [1, 2], true)): ?>
                                    <a href="<?= url('manage/crear_curso.php?id=' . (int) $course['id']) ?>" class="btn btn-outline-primary rounded-pill w-100 fw-semibold btn-sm py-2">
                                        Gestionar Curso
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?= url('login.php') ?>" class="btn btn-primary rounded-pill w-100 text-white fw-bold py-2.5 shadow-sm">
                                    Iniciar sesión para inscribirme
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Botones de Acción Secundaria (Favoritos y Compartir) -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <button class="btn btn-light rounded-pill w-100 py-2 small border text-dark" onclick="alert('Se ha guardado en favoritos temporales.');">
                                    <i class="bi bi-heart me-1.5 text-danger"></i> Favoritos
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-light rounded-pill w-100 py-2 small border text-dark" onclick="if(navigator.share){ navigator.share({title:'<?= e($courseTitle) ?>', url:window.location.href}); } else { alert('Enlace copiado al portapapeles: ' + window.location.href); }">
                                    <i class="bi bi-share me-1.5"></i> Compartir
                                </button>
                            </div>
                        </div>
                        
                        <!-- Características del Curso -->
                        <h6 class="fw-bold text-dark mb-3">Este curso incluye:</h6>
                        <ul class="list-unstyled small text-muted d-flex flex-column gap-2 mb-0">
                            <li><i class="bi bi-clock text-primary me-2"></i>Duración aproximada de <strong><?= e($course['duracion'] ?: '4 semanas') ?></strong></li>
                            <li><i class="bi bi-book text-primary me-2"></i>Estructura modular: <strong>3 módulos</strong> de formación</li>
                            <li><i class="bi bi-cloud-arrow-down text-primary me-2"></i>Acceso permanente a lecturas y archivos</li>
                            <li><i class="bi bi-phone text-primary me-2"></i>Visualización en PC, tablet o celular</li>
                            <li><i class="bi bi-patch-check text-primary me-2"></i>Certificado verificado con código QR</li>
                            <li><i class="bi bi-bar-chart text-primary me-2"></i>Nivel formativo: <strong><?= e($course['nivel'] ?? 'Básico') ?></strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
