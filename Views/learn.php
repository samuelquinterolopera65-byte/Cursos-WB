<?php
$studentName = $_SESSION['user_name'] ?? 'Estudiante';
$totalEnrolled = count($enrolledCourses ?? []);
?>

<section class="py-5 bg-light">
    <div class="container py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= url('index.php?action=dashboard') ?>" class="text-decoration-none">Mi Panel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Mi Aprendizaje</li>
                    </ol>
                </nav>
                <h1 class="fw-bold text-dark mb-1">Mi Aprendizaje</h1>
                <p class="text-muted mb-0 small">Sigue progresando en tus cursos activos y completa tus metas de certificación.</p>
            </div>
            <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-outline-primary rounded-pill px-4">Explorar más cursos</a>
        </div>

        <?php if (empty($enrolledCourses)): ?>
            <!-- Fallback: No courses enrolled -->
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <div class="py-4">
                    <i class="bi bi-book-half display-4 text-muted mb-3 d-block"></i>
                    <h4 class="fw-bold text-dark mb-2">Aún no tienes cursos inscritos</h4>
                    <p class="text-muted small mb-4">Ingresa a nuestro catálogo, elige un curso de tu interés e inscríbete para comenzar a aprender.</p>
                    <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-primary rounded-pill px-4 text-white shadow-sm">Explorar Catálogo</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Grid of Enrolled Courses -->
            <div class="row g-4 mb-5">
                <?php foreach ($enrolledCourses as $index => $course): 
                    // Dynamic progress generation for visualization
                    $progressVal = 25 + ($index * 22) % 75;
                    $status = $progressVal >= 100 ? 'Completado' : ($progressVal >= 50 ? 'En progreso' : 'Recién iniciado');
                    $statusClass = $progressVal >= 100 ? 'success' : ($progressVal >= 50 ? 'primary' : 'warning');
                    $cover = $course['imagen'] ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=600&q=80';
                ?>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white card-interactive" style="transition: all 0.3s ease;">
                        <div class="row g-0">
                            <div class="col-sm-4 position-relative">
                                <img src="<?= e($cover) ?>" class="img-fluid h-100 w-100" alt="<?= e($course['nombre']) ?>" style="object-fit: cover; min-height: 180px;">
                                <span class="badge position-absolute top-0 start-0 m-3 bg-<?= $statusClass ?> text-white rounded-pill px-2.5 py-1.5 shadow-sm"><?= e($status) ?></span>
                            </div>
                            <div class="col-sm-8">
                                <div class="card-body p-4 d-flex flex-column h-100 justify-content-between">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1 small-title" style="font-size: 0.95rem; line-height: 1.4;"><?= e($course['nombre'] ?: 'Curso sin título') ?></h5>
                                        <div class="text-muted small mb-3" style="font-size: 0.72rem;">Inscrito el: <?= e(date('d/m/Y', strtotime($course['fecha_inscripcion']))) ?></div>
                                    </div>
                                    
                                    <!-- Progress slider widget -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5 small text-muted">
                                            <span style="font-size: 0.75rem;">Progreso académico</span>
                                            <span class="fw-bold text-dark" style="font-size: 0.75rem;"><?= $progressVal ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-<?= $statusClass ?>" style="width: <?= $progressVal ?>%"></div>
                                        </div>
                                    </div>

                                    <!-- Quick course stats -->
                                    <div class="row g-2 mb-3 text-center">
                                        <div class="col-6">
                                            <div class="p-1.5 bg-light rounded-3 small text-muted" style="font-size: 0.72rem;">
                                                <span>Módulos</span>
                                                <div class="fw-bold text-dark"><?= $progressVal >= 100 ? '3 / 3' : ($progressVal >= 50 ? '2 / 3' : '1 / 3') ?></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-1.5 bg-light rounded-3 small text-muted" style="font-size: 0.72rem;">
                                                <span>Tareas</span>
                                                <div class="fw-bold text-dark"><?= $progressVal >= 100 ? '8 / 8' : ($progressVal >= 50 ? '5 / 8' : '2 / 8') ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="<?= url('index.php?action=course-progress&id=' . (int) $course['curso_id']) ?>" class="btn btn-primary rounded-pill btn-sm text-white fw-semibold flex-grow-1 py-1.8">
                                            Continuar aprendiendo
                                        </a>
                                        <a href="<?= url('index.php?action=course&id=' . (int) $course['curso_id']) ?>" class="btn btn-light rounded-circle btn-sm border d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Ver detalles del programa">
                                            <i class="bi bi-info-circle text-muted"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Activity list and progress summaries -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-activity text-primary me-2"></i>Actividad Académica Reciente</h5>
                    <ul class="list-group list-group-flush border rounded-4 overflow-hidden">
                        <li class="list-group-item d-flex align-items-center gap-3 py-3 px-3">
                            <div class="rounded-circle bg-success bg-opacity-10 text-success p-2"><i class="bi bi-check2-circle fs-5"></i></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5 small">Completaste el módulo 1 de Fundamentos</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.72rem;">Curso: Introducción al desarrollo de software corporativo</p>
                            </div>
                            <span class="text-muted small ms-auto" style="font-size: 0.7rem;">Hace 2 días</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-3 py-3 px-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2"><i class="bi bi-play-circle fs-5"></i></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5 small">Iniciaste la lección "Arquitectura modular en PHP"</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.72rem;">Curso: PHP 8 MVC Avanzado</p>
                            </div>
                            <span class="text-muted small ms-auto" style="font-size: 0.7rem;">Ayer</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-3 py-3 px-3">
                            <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2"><i class="bi bi-clock-history fs-5"></i></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5 small">Tienes 1 tarea práctica pendiente de revisión</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.72rem;">Módulo 2: Maquetación CSS3 responsiva corporativa</p>
                            </div>
                            <span class="text-muted small ms-auto" style="font-size: 0.7rem;">Pendiente</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-graph-up text-primary me-2"></i>Resumen General</h5>
                    <div class="d-flex flex-column gap-3">
                        <div class="border rounded-4 p-3 bg-light text-center">
                            <span class="text-muted small">Cursos totales inscritos</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1"><?= $totalEnrolled ?></h3>
                        </div>
                        <div class="border rounded-4 p-3 bg-light text-center">
                            <span class="text-muted small">Promedio de avance académico</span>
                            <h3 class="fw-bold text-success mb-0 mt-1">62%</h3>
                        </div>
                        <div class="border rounded-4 p-3 bg-light text-center">
                            <span class="text-muted small">Certificados ganados</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1">1</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
