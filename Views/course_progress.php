<?php
$courseTitle = $course['nombre'] ?? 'Curso sin título';
$courseId    = $course['id'] ?? 0;
$cover       = $course['imagen'] ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80';

/* ── Módulos estáticos de ejemplo (reemplazar con datos reales de BD) ── */
$modules = [
    [
        'id'       => 1,
        'title'    => 'Módulo 1 · Fundamentos y Marco Teórico',
        'progress' => 100,
        'locked'   => false,
        'lessons'  => [
            ['id' => 1, 'title' => 'Lección 1.1: Introducción general y objetivos',   'type' => 'video',    'duration' => '12 min',      'status' => 'Completado'],
            ['id' => 2, 'title' => 'Lección 1.2: Glosario de términos clave del sector','type' => 'document','duration' => '5 págs',      'status' => 'Completado'],
            ['id' => 3, 'title' => 'Lección 1.3: Ejercicio práctico de autodiagnóstico','type' => 'quiz',   'duration' => '10 preguntas','status' => 'Completado'],
        ]
    ],
    [
        'id'       => 2,
        'title'    => 'Módulo 2 · Desarrollo Práctico Guiado',
        'progress' => 65,
        'locked'   => false,
        'lessons'  => [
            ['id' => 4, 'title' => 'Lección 2.1: Desarrollo paso a paso en entorno local','type' => 'video',    'duration' => '35 min',     'status' => 'En progreso'],
            ['id' => 5, 'title' => 'Lección 2.2: Repaso de utilidades y librerías clave', 'type' => 'document','duration' => '8 págs',     'status' => 'Pendiente'],
            ['id' => 6, 'title' => 'Lección 2.3: Actividad práctica calificable',         'type' => 'task',   'duration' => '1 entrega',  'status' => 'Pendiente'],
        ]
    ],
    [
        'id'       => 3,
        'title'    => 'Módulo 3 · Evaluaciones y Certificación',
        'progress' => 0,
        'locked'   => true,
        'lessons'  => [
            ['id' => 7, 'title' => 'Lección 3.1: Pruebas de integración y seguridad básica','type' => 'video','duration' => '25 min',      'status' => 'Bloqueado'],
            ['id' => 8, 'title' => 'Lección 3.2: Examen Teórico Final de Competencias',     'type' => 'quiz', 'duration' => '25 preguntas','status' => 'Bloqueado'],
            ['id' => 9, 'title' => 'Lección 3.3: Emisión de Certificado con código QR',     'type' => 'task', 'duration' => '1 entrega',  'status' => 'Bloqueado'],
        ]
    ]
];

/* Calcula progreso total */
$totalLessons     = array_sum(array_column(array_column($modules, 'lessons'), null)) ? 0 : 0;
$completedLessons = 0;
$totalCount       = 0;
foreach ($modules as $m) {
    foreach ($m['lessons'] as $l) {
        $totalCount++;
        if ($l['status'] === 'Completado') $completedLessons++;
    }
}
$overallProgress = $totalCount > 0 ? round(($completedLessons / $totalCount) * 100) : 0;

/* Foro mock comments */
$forumComments = [
    ['author' => 'Adriana R.',  'avatar' => 'AR', 'color' => '#1a73e8', 'time' => 'hace 3 h',  'text' => 'Excelente explicación en la lección 1.2. ¿Alguien tiene el material en PDF?'],
    ['author' => 'Luis G.',     'avatar' => 'LG', 'color' => '#2e7d32', 'time' => 'hace 1 h',  'text' => 'Sí, el instructor lo subió en la sección de recursos. ¡Muy completo!'],
    ['author' => 'Camila T.',   'avatar' => 'CT', 'color' => '#6d28d9', 'time' => 'hace 30 min','text' => 'La lección 2.1 fue la mejor. El entorno de práctica me sirvió mucho.'],
];
?>

<!-- ─── HERO INMERSIVO DEL CURSO ─── -->
<section class="py-0">
    <div class="position-relative overflow-hidden" style="background: linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#1a237e 100%); min-height: 210px;">
        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10"
             style="background-image:radial-gradient(#60a5fa 1.5px,transparent 0);background-size:24px 24px;"></div>
        <div class="container py-5 position-relative">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:rgba(255,255,255,.4);--bs-breadcrumb-item-color:rgba(255,255,255,.6);">
                    <li class="breadcrumb-item"><a href="<?= url('index.php?action=learn') ?>" class="text-white-50 text-decoration-none small">Mi Aprendizaje</a></li>
                    <li class="breadcrumb-item active text-white-50 small" aria-current="page"><?= e($courseTitle) ?></li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-4">
                <div>
                    <span class="badge bg-primary rounded-pill px-3 py-1 mb-2 small">📚 En Progreso</span>
                    <h1 class="fw-bold text-white mb-1" style="font-size: clamp(1.3rem,3vw,1.85rem); line-height:1.3;"><?= e($courseTitle) ?></h1>
                    <p class="text-white-50 mb-0 small">Instructor: <strong class="text-white-75"><?= e($course['instructor'] ?? 'Especialista') ?></strong> · Nivel: <strong class="text-white-75"><?= e($course['nivel'] ?? 'Básico') ?></strong></p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= url('index.php?action=certificate&id=' . $courseId) ?>" class="btn btn-warning rounded-pill px-4 fw-bold shadow">
                        <i class="bi bi-patch-check me-1"></i>Mi Certificado
                    </a>
                    <a href="<?= url('index.php?action=learn') ?>" class="btn btn-outline-light rounded-pill px-4">
                        <i class="bi bi-chevron-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ─── BARRA DE PROGRESO GLOBAL FIJA ─── -->
<div class="border-bottom bg-white shadow-sm py-2 sticky-top" style="top: 68px; z-index: 100;">
    <div class="container d-flex align-items-center gap-3">
        <span class="small fw-bold text-dark text-nowrap">Progreso académico</span>
        <div class="progress flex-grow-1" style="height: 10px;">
            <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" style="width:<?= $overallProgress ?>%"></div>
        </div>
        <span class="fw-bold text-primary small text-nowrap"><?= $overallProgress ?>%</span>
        <span class="badge bg-light border text-muted small text-nowrap"><?= $completedLessons ?>/<?= $totalCount ?> lecciones</span>
    </div>
</div>

<!-- ─── CUERPO PRINCIPAL ─── -->
<section class="py-5 bg-light">
    <div class="container py-2">
        <div class="row g-4">

            <!-- ── COLUMNA IZQUIERDA: PLAYER + MÓDULOS (8 cols) ── -->
            <div class="col-lg-8">

                <!-- VIDEO PLAYER PRINCIPAL -->
                <div class="card border-0 shadow rounded-4 overflow-hidden mb-4">
                    <div class="position-relative bg-dark" id="playerWrapper" style="aspect-ratio:16/9; cursor:pointer;" onclick="togglePlay(this)">
                        <!-- Cover / Thumbnail -->
                        <img src="<?= e($cover) ?>" id="playerCover" class="w-100 h-100 position-absolute top-0 start-0" alt="<?= e($courseTitle) ?>" style="object-fit:cover; opacity:0.35;">
                        <!-- Play overlay -->
                        <div id="playOverlay" class="position-absolute top-50 start-50 translate-middle text-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-lg mb-3"
                                 style="width:80px;height:80px;background:rgba(26,115,232,.85);backdrop-filter:blur(6px);">
                                <i class="bi bi-play-fill text-white" style="font-size:2rem;margin-left:4px;"></i>
                            </div>
                            <h5 class="text-white fw-bold mb-1" id="playerTitle">Lección 2.1: Desarrollo paso a paso</h5>
                            <p class="text-white-50 small">Haz clic para reproducir · 35 min</p>
                        </div>
                        <!-- "Playing" state (hidden initially) -->
                        <div id="playingState" class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center">
                            <div class="text-center text-white">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p class="small text-white-50 mb-0">Cargando reproductor...</p>
                            </div>
                        </div>
                    </div>
                    <!-- Controles de navegación entre lecciones -->
                    <div class="p-3 bg-white border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <button class="btn btn-outline-secondary rounded-pill btn-sm px-3" onclick="alert('Lección anterior')">
                            <i class="bi bi-chevron-left me-1"></i>Anterior
                        </button>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-success rounded-pill btn-sm px-3" onclick="markDone(this)">
                                <i class="bi bi-check-circle me-1"></i>Marcar completada
                            </button>
                            <button class="btn btn-primary rounded-pill btn-sm px-3 text-white fw-semibold" onclick="alert('Siguiente lección')">
                                Siguiente <i class="bi bi-chevron-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MÓDULOS Y LECCIONES -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-4"><i class="bi bi-list-ul text-primary me-2"></i>Contenido del Curso</h5>
                        <div class="d-flex flex-column gap-3" id="curriculumList">
                            <?php foreach ($modules as $mi => $module): ?>
                            <div class="border rounded-4 overflow-hidden">
                                <!-- Módulo header clicable -->
                                <button class="w-100 text-start p-3 bg-light border-0 d-flex align-items-center justify-content-between"
                                        data-bs-toggle="collapse" data-bs-target="#mod<?= $mi ?>Content"
                                        aria-expanded="<?= $mi === 1 ? 'true' : 'false' ?>">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($module['locked']): ?>
                                            <i class="bi bi-lock-fill text-secondary fs-5"></i>
                                        <?php elseif ($module['progress'] >= 100): ?>
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        <?php else: ?>
                                            <i class="bi bi-play-circle-fill text-primary fs-5"></i>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark small"><?= e($module['title']) ?></div>
                                            <div class="text-muted" style="font-size:.72rem;"><?= count($module['lessons']) ?> lecciones · <?= $module['progress'] ?>% completado</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="progress d-none d-sm-flex" style="width:80px;height:6px;">
                                            <div class="progress-bar <?= $module['locked'] ? 'bg-secondary' : 'bg-success' ?>" style="width:<?= $module['progress'] ?>%"></div>
                                        </div>
                                        <i class="bi bi-chevron-down small text-muted"></i>
                                    </div>
                                </button>
                                <!-- Lecciones colapsables -->
                                <div class="collapse <?= $mi === 1 ? 'show' : '' ?>" id="mod<?= $mi ?>Content">
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($module['lessons'] as $li => $lesson):
                                            $lIcon  = match($lesson['type']) { 'video' => 'bi-play-btn-fill text-primary', 'quiz' => 'bi-patch-question-fill text-warning', 'task' => 'bi-clipboard-check-fill text-success', default => 'bi-file-earmark-text-fill text-info' };
                                            $sClass = match($lesson['status']) { 'Completado' => 'bg-success', 'En progreso' => 'bg-primary', 'Pendiente' => 'bg-secondary', default => 'bg-secondary opacity-50' };
                                            $canClick = !$module['locked'];
                                        ?>
                                        <li class="border-top">
                                            <button class="w-100 text-start px-4 py-3 border-0 bg-white d-flex align-items-center gap-3 lesson-btn <?= !$canClick ? 'opacity-50' : 'hover-lesson' ?>"
                                                    <?= $canClick ? "onclick=\"loadLesson('" . e($lesson['title']) . "','". $lesson['duration'] ."')\"" : 'disabled' ?>>
                                                <i class="bi <?= $lIcon ?> fs-5 flex-shrink-0"></i>
                                                <div class="flex-grow-1">
                                                    <div class="small text-dark fw-medium"><?= e($lesson['title']) ?></div>
                                                    <div class="text-muted" style="font-size:.7rem;"><i class="bi bi-clock me-1"></i><?= e($lesson['duration']) ?></div>
                                                </div>
                                                <span class="badge rounded-pill text-white <?= $sClass ?> px-2" style="font-size:.65rem;"><?= e($lesson['status']) ?></span>
                                                <?php if ($module['locked']): ?>
                                                    <i class="bi bi-lock text-secondary small"></i>
                                                <?php elseif ($lesson['status'] === 'Completado'): ?>
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-chevron-right small text-muted"></i>
                                                <?php endif; ?>
                                            </button>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- FORO INLINE -->
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-4"><i class="bi bi-chat-square-dots text-primary me-2"></i>Foro de Discusión</h5>
                        <div class="d-flex flex-column gap-3 mb-4" id="forumComments">
                            <?php foreach ($forumComments as $c): ?>
                            <div class="d-flex gap-3 align-items-start">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                     style="width:40px;height:40px;background:<?= $c['color'] ?>;font-size:.8rem;"><?= $c['avatar'] ?></div>
                                <div class="bg-light rounded-4 p-3 flex-grow-1">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold text-dark small"><?= e($c['author']) ?></span>
                                        <span class="text-muted" style="font-size:.7rem;"><?= e($c['time']) ?></span>
                                    </div>
                                    <p class="mb-0 small text-dark"><?= e($c['text']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Formulario de comentario -->
                        <div class="d-flex gap-3 align-items-end">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0 bg-primary"
                                 style="width:40px;height:40px;font-size:.8rem;">Tú</div>
                            <div class="flex-grow-1">
                                <textarea id="forumInput" class="form-control rounded-4 border" rows="2" placeholder="Escribe tu pregunta o comentario..."></textarea>
                                <button class="btn btn-primary rounded-pill btn-sm mt-2 px-4 text-white" onclick="postComment()">
                                    <i class="bi bi-send me-1"></i>Publicar comentario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /col-lg-8 -->

            <!-- ── COLUMNA DERECHA: SIDEBAR (4 cols) ── -->
            <div class="col-lg-4">

                <!-- Ficha del Curso -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
                    <img src="<?= e($cover) ?>" class="w-100" alt="" style="height:130px;object-fit:cover;">
                    <div class="p-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Ficha del Curso</h6>
                        <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-0">
                            <li class="d-flex justify-content-between">
                                <span>Instructor</span>
                                <strong class="text-dark"><?= e($course['instructor'] ?? 'Especialista') ?></strong>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span>Duración</span>
                                <strong class="text-dark"><?= e($course['duracion'] ?? 'Flexible') ?></strong>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span>Nivel</span>
                                <strong class="text-dark"><?= e($course['nivel'] ?? 'Básico') ?></strong>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span>Idioma</span>
                                <strong class="text-dark"><?= e($course['idioma'] ?? 'Español') ?></strong>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span>Certificado</span>
                                <strong class="text-success"><i class="bi bi-patch-check-fill me-1"></i>Incluido</strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Racha de Estudio -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="background:linear-gradient(135deg,#1a73e8,#6d28d9);">
                    <div class="card-body p-4 text-white text-center">
                        <i class="bi bi-fire display-5 mb-2 d-block" style="color:#fbbf24;"></i>
                        <h2 class="fw-bold mb-0">7 días</h2>
                        <p class="mb-3 opacity-75 small">Racha de estudio activa</p>
                        <div class="d-flex justify-content-center gap-1 mb-3">
                            <?php foreach(['L','M','X','J','V','S','D'] as $d): ?>
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                 style="width:30px;height:30px;background:rgba(255,255,255,.2);font-size:.65rem;"><?= $d ?></div>
                            <?php endforeach; ?>
                        </div>
                        <p class="mb-0 opacity-75" style="font-size:.75rem;">¡Sigue así! Cada día de estudio te acerca más a tu certificado.</p>
                    </div>
                </div>

                <!-- Insignias -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-trophy text-warning me-2"></i>Logros Obtenidos</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-3 p-2 bg-light rounded-3">
                                <span class="fs-3">🏅</span>
                                <div class="small">
                                    <div class="fw-bold text-dark">Módulo 1 Completado</div>
                                    <div class="text-muted" style="font-size:.72rem;">Obtenida el 10/07/2026</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 p-2 bg-light rounded-3">
                                <span class="fs-3">🔥</span>
                                <div class="small">
                                    <div class="fw-bold text-dark">Racha de 7 días</div>
                                    <div class="text-muted" style="font-size:.72rem;">Activa</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 p-2 bg-light rounded-3 opacity-50">
                                <span class="fs-3">🎓</span>
                                <div class="small">
                                    <div class="fw-bold text-dark">Certificado Final</div>
                                    <div class="text-muted" style="font-size:.72rem;">Completa el curso para desbloquear</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ayuda / Soporte -->
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-headset text-primary me-2"></i>¿Necesitas ayuda?</h6>
                        <p class="text-muted small mb-3">Nuestro equipo de soporte académico responde en menos de 24 horas.</p>
                        <div class="d-flex flex-column gap-2">
                            <a href="#forumComments" class="btn btn-outline-primary rounded-pill btn-sm fw-semibold">
                                <i class="bi bi-chat-dots me-1"></i>Foro del curso
                            </a>
                            <a href="mailto:soporte@northstarlms.com" class="btn btn-light rounded-pill btn-sm border text-dark fw-semibold">
                                <i class="bi bi-envelope me-1"></i>Soporte técnico
                            </a>
                        </div>
                    </div>
                </div>

            </div><!-- /col-lg-4 -->
        </div>
    </div>
</section>

<script>
/* ── Carga de lección en el reproductor ── */
function loadLesson(title, duration) {
    document.getElementById('playerTitle').textContent = title;
    document.getElementById('playerWrapper').querySelector('.text-white-50.small').textContent =
        'Haz clic para reproducir · ' + duration;
    document.getElementById('playOverlay').classList.remove('d-none');
    document.getElementById('playingState').classList.add('d-none');
    // scroll al player
    document.getElementById('playerWrapper').scrollIntoView({ behavior: 'smooth', block: 'start' });
    // pequeño offset para la sticky bar
    setTimeout(() => window.scrollBy(0, -120), 500);
}

/* ── Toggle reproducción (mock) ── */
function togglePlay(wrapper) {
    const overlay  = document.getElementById('playOverlay');
    const playing  = document.getElementById('playingState');
    overlay.classList.toggle('d-none');
    playing.classList.toggle('d-none');
    setTimeout(() => {
        playing.classList.add('d-none');
        overlay.classList.remove('d-none');
        overlay.querySelector('.bi-play-fill')?.classList.replace('bi-play-fill','bi-pause-fill');
    }, 2000);
}

/* ── Marcar lección como completada ── */
function markDone(btn) {
    btn.innerHTML = '<i class="bi bi-check-circle-fill me-1 text-success"></i>¡Completada!';
    btn.classList.replace('btn-outline-success','btn-success');
    btn.classList.add('text-white');
    btn.disabled = true;
}

/* ── Publicar comentario en el foro ── */
function postComment() {
    const input   = document.getElementById('forumInput');
    const text    = input.value.trim();
    if (!text) return;
    const forum   = document.getElementById('forumComments');
    const div     = document.createElement('div');
    div.className = 'd-flex gap-3 align-items-start';
    div.innerHTML = `
        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0 bg-primary"
             style="width:40px;height:40px;font-size:.8rem;">Tú</div>
        <div class="bg-light rounded-4 p-3 flex-grow-1">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-semibold text-dark small">Tú</span>
                <span class="text-muted" style="font-size:.7rem;">ahora mismo</span>
            </div>
            <p class="mb-0 small text-dark">${text.replace(/</g,'&lt;')}</p>
        </div>`;
    forum.appendChild(div);
    input.value = '';
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>

<style>
.hover-lesson:hover { background: #f0f6ff !important; }
.lesson-btn { transition: background .15s ease; }
</style>
