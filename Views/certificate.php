<?php
$courseTitle  = $course['nombre'] ?? 'Curso sin título';
$courseId     = $course['id'] ?? 0;
$issueDate    = date('d \d\e F \d\e Y');
$verifyCode   = strtoupper(substr(md5($courseId . ($studentName ?? '') . date('Y')), 0, 12));
$certNum      = 'CERT-' . date('Y') . '-' . str_pad($courseId, 5, '0', STR_PAD_LEFT);
?>

<section class="py-5 bg-light">
    <div class="container py-3">
        <!-- Header actions -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= url('index.php?action=learn') ?>" class="text-decoration-none">Mi Aprendizaje</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('index.php?action=course-progress&id=' . $courseId) ?>" class="text-decoration-none">Progreso</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Certificado</li>
                    </ol>
                </nav>
                <h1 class="fw-bold text-dark mb-1">Mi Certificado Oficial</h1>
                <p class="text-muted small mb-0">Descarga o comparte tu certificado de finalización.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary rounded-pill px-4 fw-semibold shadow" onclick="window.print()">
                    <i class="bi bi-download me-2"></i>Descargar PDF
                </button>
                <button class="btn btn-outline-secondary rounded-pill px-4" onclick="shareCert()">
                    <i class="bi bi-share me-2"></i>Compartir
                </button>
            </div>
        </div>

        <!-- CERTIFICADO -->
        <div class="row justify-content-center">
            <div class="col-xl-10" id="certContainer">
                <div id="certificate"
                     class="shadow-lg rounded-4 overflow-hidden border"
                     style="background:#fff; border-color:#c9a227 !important; border-width: 3px !important;">

                    <!-- Franja superior decorativa -->
                    <div style="height:12px; background:linear-gradient(90deg,#1a237e,#1a73e8,#0d9488,#1a237e); background-size:400% 100%; animation:gradAnim 6s linear infinite;"></div>

                    <div class="p-5 p-md-5">
                        <!-- Logo y título institución -->
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center gap-2 mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white fw-black"
                                     style="width:52px;height:52px;font-size:1.4rem;">N</div>
                                <span class="fw-black text-dark" style="font-size:1.5rem;letter-spacing:-0.5px;">NorthStar <span class="text-primary">LMS</span></span>
                            </div>
                            <p class="text-muted small mb-0">Plataforma de Educación Corporativa · northstarlms.com</p>
                        </div>

                        <!-- Divisor decorativo -->
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center gap-3">
                                <div style="width:60px;height:2px;background:linear-gradient(90deg,transparent,#c9a227);"></div>
                                <i class="bi bi-award-fill text-warning fs-3"></i>
                                <div style="width:60px;height:2px;background:linear-gradient(270deg,transparent,#c9a227);"></div>
                            </div>
                        </div>

                        <!-- Texto del certificado -->
                        <div class="text-center mb-5">
                            <h2 class="text-uppercase letter-spacing-wide mb-1" style="font-size:1rem;color:#8b6914;letter-spacing:4px;font-weight:600;">Certificado de Finalización</h2>
                            <p class="text-muted small mb-4">Se certifica que el participante</p>

                            <h1 class="fw-black mb-2" style="font-size:clamp(1.8rem,5vw,2.8rem);color:#1a237e;font-family:'Playfair Display',Georgia,serif;">
                                <?= e($studentName ?? 'Nombre del Estudiante') ?>
                            </h1>
                            <div style="height:3px;width:200px;background:linear-gradient(90deg,#c9a227,#f4d03f,#c9a227);border-radius:3px;margin:0 auto 1.5rem;"></div>

                            <p class="text-muted mb-2" style="font-size:1rem;">ha completado satisfactoriamente el curso de</p>
                            <h3 class="fw-bold text-dark mb-4" style="font-size:clamp(1.2rem,3vw,1.7rem);line-height:1.3;">
                                "<?= e($courseTitle) ?>"
                            </h3>

                            <!-- Detalles del curso -->
                            <div class="row g-3 justify-content-center mb-4">
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4" style="background:#f8f9fa;border:1px solid #e9ecef;">
                                        <div class="fw-bold text-primary small"><?= e($course['nivel'] ?? 'Avanzado') ?></div>
                                        <div class="text-muted" style="font-size:.72rem;">Nivel</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4" style="background:#f8f9fa;border:1px solid #e9ecef;">
                                        <div class="fw-bold text-primary small"><?= e($course['duracion'] ?? '40 horas') ?></div>
                                        <div class="text-muted" style="font-size:.72rem;">Duración</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4" style="background:#f8f9fa;border:1px solid #e9ecef;">
                                        <div class="fw-bold text-primary small"><?= e($course['instructor'] ?? 'Especialista') ?></div>
                                        <div class="text-muted" style="font-size:.72rem;">Instructor</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4" style="background:#f8f9fa;border:1px solid #e9ecef;">
                                        <div class="fw-bold text-primary small"><?= $issueDate ?></div>
                                        <div class="text-muted" style="font-size:.72rem;">Fecha de emisión</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Firmas y sellos -->
                        <div class="row align-items-end justify-content-between g-4 border-top pt-4">
                            <div class="col-md-4 text-center">
                                <div class="mb-1" style="font-style:italic;font-size:1.4rem;color:#1a237e;font-family:'Playfair Display',Georgia,serif;">
                                    Dr. Samuel López
                                </div>
                                <div style="height:1px;background:#333;margin:0 auto 6px;width:80%;"></div>
                                <div class="small text-muted fw-medium">Director Académico</div>
                                <div class="text-muted" style="font-size:.72rem;">NorthStar LMS</div>
                            </div>
                            <div class="col-md-4 text-center">
                                <!-- Sello QR simulado -->
                                <div class="d-inline-block p-3 rounded-3 border border-2" style="border-color:#1a237e !important;">
                                    <div class="d-grid mb-1" style="grid-template-columns:repeat(7,10px);gap:2px;">
                                        <?php for ($r = 0; $r < 7; $r++): for ($c = 0; $c < 7; $c++): ?>
                                        <div style="width:10px;height:10px;background:<?= (($r < 2 && $c < 2) || ($r < 2 && $c > 4) || ($r > 4 && $c < 2) || rand(0,1)) ? '#1a237e' : 'transparent' ?>;border-radius:1px;"></div>
                                        <?php endfor; endfor; ?>
                                    </div>
                                    <div class="text-muted text-center mt-1" style="font-size:.6rem;">Verificar</div>
                                </div>
                                <div class="small text-muted mt-2">Código: <strong class="text-dark"><?= $verifyCode ?></strong></div>
                                <div class="text-muted" style="font-size:.65rem;"><?= $certNum ?></div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="mb-1" style="font-style:italic;font-size:1.4rem;color:#1a237e;font-family:'Playfair Display',Georgia,serif;">
                                    <?= e($course['instructor'] ?? 'Instructor Especializado') ?>
                                </div>
                                <div style="height:1px;background:#333;margin:0 auto 6px;width:80%;"></div>
                                <div class="small text-muted fw-medium">Instructor del Curso</div>
                                <div class="text-muted" style="font-size:.72rem;"><?= e($course['categoria_nombre'] ?? 'Especialización') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Franja inferior decorativa -->
                    <div style="height:8px;background:linear-gradient(90deg,#c9a227,#f4d03f,#c9a227);"></div>
                </div><!-- /certificate -->
            </div>
        </div><!-- /row -->

        <!-- Acciones post-certificado -->
        <div class="row justify-content-center mt-5">
            <div class="col-xl-10">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100">
                            <i class="bi bi-linkedin display-5 text-primary mb-3"></i>
                            <h6 class="fw-bold text-dark">Compartir en LinkedIn</h6>
                            <p class="text-muted small mb-3">Agrega este certificado a tu perfil profesional y aumenta tu visibilidad.</p>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode('https://northstarlms.com/certificate/' . $verifyCode) ?>"
                               target="_blank" class="btn btn-outline-primary rounded-pill btn-sm px-4 fw-semibold">
                                Compartir ahora
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100">
                            <i class="bi bi-check2-circle display-5 text-success mb-3"></i>
                            <h6 class="fw-bold text-dark">Verificar Autenticidad</h6>
                            <p class="text-muted small mb-3">Cualquier empleador puede verificar la autenticidad de este certificado en nuestro portal.</p>
                            <a href="#" class="btn btn-outline-success rounded-pill btn-sm px-4 fw-semibold">
                                Portal de verificación
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center h-100">
                            <i class="bi bi-mortarboard display-5 text-warning mb-3"></i>
                            <h6 class="fw-bold text-dark">Seguir Aprendiendo</h6>
                            <p class="text-muted small mb-3">Explora cursos relacionados y continúa expandiendo tus habilidades profesionales.</p>
                            <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-outline-warning rounded-pill btn-sm px-4 fw-semibold text-dark">
                                Ver catálogo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
function shareCert() {
    if (navigator.share) {
        navigator.share({
            title: 'Mi Certificado — <?= e($courseTitle) ?>',
            text: 'Acabo de completar el curso "<?= e($courseTitle) ?>" en NorthStar LMS.',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('¡Enlace copiado al portapapeles!');
        });
    }
}
</script>

<style>
@keyframes gradAnim {
    0%   { background-position: 0% 50%; }
    100% { background-position: 400% 50%; }
}
@media print {
    .container > .d-flex,
    .row.mt-5 { display: none !important; }
    #certificate { box-shadow: none !important; border: 2px solid #c9a227 !important; }
    body { background: white !important; }
}
</style>
