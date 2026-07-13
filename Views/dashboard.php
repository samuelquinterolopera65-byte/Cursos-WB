<?php
$isLoggedIn = !empty($_SESSION['user_id']);
$studentName = $_SESSION['user_name'] ?? 'Estudiante';
$enrolledCount = count($enrolledCourses);
?>

<section class="py-5 bg-light">
    <div class="container py-2">
        <?php if ($isLoggedIn): ?>
            <!-- ============================================================== -->
            <!-- VISTA DEL ESTUDIANTE AUTENTICADO -->
            <!-- ============================================================== -->
            
            <!-- Banner de Bienvenida -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 h-100 w-25 opacity-10 d-none d-md-block" style="background-image: radial-gradient(var(--primary-color) 2px, transparent 0); background-size: 16px 16px;"></div>
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <span class="badge rounded-pill bg-light text-primary fw-bold px-3 py-1.5 mb-2.5 shadow-sm border"><i class="bi bi-clock-history me-1"></i> Racha de estudio activa</span>
                        <h2 class="fw-bold text-dark mb-2">¡Hola de nuevo, <?= e($studentName) ?>! 👋</h2>
                        <p class="text-muted mb-0 small">Qué bueno verte de regreso. Tienes actividades pendientes y nuevas recomendaciones personalizadas esperándote.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-primary rounded-pill px-4 text-white shadow-sm">Explorar Catálogo</a>
                    </div>
                </div>
            </div>
            
            <!-- Fila de Estadísticas de Progreso -->
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-row align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-mortarboard-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-medium">Mis cursos inscritos</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $enrolledCount ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-row align-items-center gap-3">
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-medium">Promedio de progreso</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $enrolledCount > 0 ? '68%' : '0%' ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-row align-items-center gap-3">
                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-award-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-medium">Certificados obtenidos</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $enrolledCount > 0 ? '1' : '0' ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Columna Principal: Cursos Inscritos (8 COLS) -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3.5">
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-bookmark-star text-primary me-2"></i>Mis Programas de Formación</h5>
                            <span class="badge rounded-pill bg-light text-primary border px-2.5 py-1.5 fw-bold"><?= $enrolledCount ?> Activos</span>
                        </div>
                        
                        <?php if ($enrolledCount > 0): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($enrolledCourses as $course): 
                                    $cover = $course['imagen'] ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=400&q=80';
                                    $mockProgress = rand(30, 85);
                                ?>
                                <div class="border rounded-4 p-3 bg-light">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-3">
                                            <img src="<?= e($cover) ?>" class="img-fluid rounded-3" alt="<?= e($course['nombre']) ?>" style="height: 80px; width: 100%; object-fit: cover;">
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-bold text-dark mb-1 small"><?= e($course['nombre'] ?: 'Curso sin título') ?></h6>
                                            <div class="d-flex align-items-center gap-2 mb-2 text-muted small" style="font-size: 0.75rem;">
                                                <span>Inscrito: <?= e(date('d/m/Y', strtotime($course['fecha_inscripcion']))) ?></span>
                                                <span>·</span>
                                                <span class="text-primary fw-medium">En progreso</span>
                                            </div>
                                            <!-- Barra de progreso -->
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" style="width: <?= $mockProgress ?>%"></div>
                                                </div>
                                                <span class="small fw-bold text-dark" style="font-size: 0.72rem;"><?= $mockProgress ?>%</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-md-end">
                                            <a href="<?= url('index.php?action=course-progress&id=' . (int) $course['curso_id']) ?>" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 text-white fw-semibold">
                                                Continuar <i class="bi bi-arrow-right small ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-5 text-center bg-light rounded-4">
                                <i class="bi bi-journal-x fs-1 text-muted mb-2"></i>
                                <h6 class="fw-bold text-dark">Aún no estás inscrito en ningún curso</h6>
                                <p class="text-muted small mb-4">Revisa nuestro catálogo de cursos para empezar hoy mismo.</p>
                                <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-primary btn-sm rounded-pill px-4 text-white shadow-sm">Explorar Catálogo</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Recomendados para el Estudiante -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h5 class="fw-bold text-dark mb-3.5"><i class="bi bi-rocket-takeoff text-primary me-2"></i>Recomendado para ti</h5>
                        <div class="row g-3">
                            <?php 
                            $recommends = array_slice($courses, 0, 2);
                            foreach ($recommends as $rec):
                                $cover = $rec['imagen'] ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=400&q=80';
                            ?>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 bg-light h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <img src="<?= e($cover) ?>" class="img-fluid rounded-3 mb-2" alt="<?= e($rec['nombre']) ?>" style="height: 100px; width: 100%; object-fit: cover;">
                                        <h6 class="fw-bold text-dark mb-1 text-truncate small"><?= e($rec['nombre']) ?></h6>
                                        <p class="text-muted small mb-3 text-truncate-2" style="font-size: 0.72rem;"><?= e($rec['descripcion_corta']) ?></p>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary small">$<?= number_format($rec['precio'], 2, ',', '.') ?></span>
                                        <a href="<?= url('index.php?action=course&id=' . (int) $rec['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 font-semibold" style="font-size: 0.75rem;">Ver curso</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Columna Sidebar: Calendario y Notificaciones (4 COLS) -->
                <div class="col-lg-4">
                    <!-- Widget Calendario -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold text-dark mb-3.5"><i class="bi bi-calendar-event text-primary me-2"></i>Próximos Eventos</h5>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-start gap-2.5">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-3 text-center p-2" style="min-width: 48px;">
                                    <div class="fw-bold lh-1 mb-0.5" style="font-size: 1.1rem;">15</div>
                                    <div class="text-uppercase small-text-date" style="font-size: 0.65rem;">Jul</div>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0.5 small">Sesión de Mentoría Q&A</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.72rem;"><i class="bi bi-clock me-1"></i>04:00 PM (Zoom en vivo)</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2.5">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-3 text-center p-2" style="min-width: 48px;">
                                    <div class="fw-bold lh-1 mb-0.5" style="font-size: 1.1rem;">20</div>
                                    <div class="text-uppercase small-text-date" style="font-size: 0.65rem;">Jul</div>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0.5 small">Entrega Proyecto Final</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.72rem;"><i class="bi bi-clock me-1"></i>11:59 PM (Evaluación Módulo 2)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Widget Notificaciones -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h5 class="fw-bold text-dark mb-3.5"><i class="bi bi-bell text-primary me-2"></i>Notificaciones</h5>
                        <div class="d-flex flex-column gap-3.5">
                            <div class="d-flex align-items-start gap-2 border-bottom pb-2">
                                <i class="bi bi-check-circle text-success mt-0.5"></i>
                                <div class="small">
                                    <div class="text-dark fw-semibold" style="font-size: 0.8rem;">Tu proyecto ha sido calificado</div>
                                    <p class="text-muted mb-0" style="font-size: 0.72rem;">Módulo 1: Fundamentos. Obtuviste una calificación de 95%.</p>
                                    <span class="text-muted font-normal" style="font-size: 0.65rem;">Hace 4 horas</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-info-circle text-primary mt-0.5"></i>
                                <div class="small">
                                    <div class="text-dark fw-semibold" style="font-size: 0.8rem;">Nuevo material disponible</div>
                                    <p class="text-muted mb-0" style="font-size: 0.72rem;">Se han subido lecturas complementarias al curso de PHP 8.</p>
                                    <span class="text-muted font-normal" style="font-size: 0.65rem;">Ayer</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- ============================================================== -->
            <!-- VISTA DEL VISITANTE / USUARIO GENERAL NO LOGUEADO -->
            <!-- ============================================================== -->
            <div class="card border-0 shadow-sm rounded-4 p-5 mb-4 bg-white text-center">
                <div class="max-width-600 mx-auto py-3">
                    <i class="bi bi-shield-lock-fill text-primary display-4 mb-3"></i>
                    <h2 class="fw-bold text-dark mb-3">Acceso al Panel de Estudiante</h2>
                    <p class="text-muted mb-4 fs-6">Debes iniciar sesión con tu cuenta académica para acceder a tu historial, progreso, foros y certificados oficiales del LMS.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?= url('login.php') ?>" class="btn btn-primary rounded-pill px-4 text-white shadow-sm fw-semibold">Iniciar Sesión</a>
                        <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">Ver Catálogo</a>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                        <h4 class="fw-extrabold text-primary mb-1"><?= (int) $stats['cursos_activos'] ?></h4>
                        <p class="text-muted mb-0 small fw-medium">Cursos Activos en Sistema</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                        <h4 class="fw-extrabold text-dark mb-1"><?= (int) $stats['estudiantes_inscritos'] ?></h4>
                        <p class="text-muted mb-0 small fw-medium">Estudiantes Registrados</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                        <h4 class="fw-extrabold text-dark mb-1"><?= (int) $stats['horas_impartidas'] ?> horas</h4>
                        <p class="text-muted mb-0 small fw-medium">Contenido Formativo Impartido</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
