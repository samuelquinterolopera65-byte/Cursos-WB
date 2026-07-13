<?php
/* ── Mock events (sustituir por consulta BD real) ── */
$events = [
    ['id'=>1,'title'=>'Webinar: Inteligencia Artificial para Desarrolladores 2026','type'=>'Webinar','date'=>'2026-07-15','time'=>'04:00 PM','timezone'=>'COT','link'=>'https://zoom.us/j/example','speaker'=>'Dr. Alejandro Martínez','company'=>'Google DeepMind','img'=>'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=800&q=80','enrolled'=>247,'capacity'=>500,'featured'=>true,'color'=>'#1a73e8'],
    ['id'=>2,'title'=>'Taller Práctico: Clean Code en PHP 8','type'=>'Taller','date'=>'2026-07-20','time'=>'10:00 AM','timezone'=>'COT','link'=>'https://meet.google.com/example','speaker'=>'Ing. Samuel Reyes','company'=>'NorthStar LMS','img'=>'https://images.unsplash.com/photo-1587620962725-abab19836100?auto=format&fit=crop&w=800&q=80','enrolled'=>89,'capacity'=>120,'featured'=>false,'color'=>'#2e7d32'],
    ['id'=>3,'title'=>'Conferencia: El Futuro del Trabajo Remoto y la Educación Continua','type'=>'Conferencia','date'=>'2026-07-28','time'=>'02:00 PM','timezone'=>'COT','link'=>'#','speaker'=>'PhD. Camila Gómez','company'=>'Harvard Extension','img'=>'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=800&q=80','enrolled'=>312,'capacity'=>1000,'featured'=>false,'color'=>'#6d28d9'],
    ['id'=>4,'title'=>'Workshop: UX/UI Design para Plataformas Educativas','type'=>'Workshop','date'=>'2026-08-05','time'=>'09:00 AM','timezone'=>'COT','link'=>'#','speaker'=>'Dis. Laura Montoya','company'=>'Figma Inc.','img'=>'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=800&q=80','enrolled'=>56,'capacity'=>80,'featured'=>false,'color'=>'#db2777'],
    ['id'=>5,'title'=>'Seminario: Gestión de Proyectos con Metodologías Ágiles','type'=>'Seminario','date'=>'2026-08-12','time'=>'11:00 AM','timezone'=>'COT','link'=>'#','speaker'=>'PMI Certified - Andrés Castro','company'=>'PMI Colombia','img'=>'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80','enrolled'=>142,'capacity'=>250,'featured'=>false,'color'=>'#d97706'],
];
$types = ['Todos','Webinar','Taller','Conferencia','Workshop','Seminario'];
?>

<section class="py-5 bg-light">
    <div class="container py-3">

        <!-- Header -->
        <div class="row align-items-center mb-5">
            <div class="col-md-8">
                <span class="badge bg-primary rounded-pill px-3 py-1 mb-2 small">📅 Agenda NorthStar</span>
                <h1 class="fw-bold text-dark mb-2">Eventos, Webinars y Talleres</h1>
                <p class="text-muted mb-0">Amplía tu red profesional y actualiza tus conocimientos con nuestros eventos en vivo.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-flex gap-2 flex-wrap justify-content-md-end">
                    <?php foreach ($types as $t): ?>
                    <button class="btn <?= $t === 'Todos' ? 'btn-primary text-white' : 'btn-outline-secondary' ?> rounded-pill btn-sm px-3 type-filter-btn" data-type="<?= $t ?>">
                        <?= $t ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Evento Destacado -->
        <?php $featured = reset(array_filter($events, fn($e) => $e['featured'])); ?>
        <?php if ($featured): 
            $fDate = new DateTime($featured['date']);
        ?>
        <div class="card border-0 rounded-4 overflow-hidden mb-5 shadow" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);">
            <div class="row g-0 align-items-center">
                <div class="col-md-5 position-relative">
                    <img src="<?= e($featured['img']) ?>" class="w-100 h-100" alt="<?= e($featured['title']) ?>" style="object-fit:cover;min-height:300px;opacity:.6;">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background:linear-gradient(90deg,transparent,#0f172a);"></div>
                </div>
                <div class="col-md-7 p-5 text-white">
                    <div class="d-flex gap-2 mb-3">
                        <span class="badge rounded-pill px-3 py-1 small fw-bold" style="background:<?= $featured['color'] ?>;"><?= e($featured['type']) ?></span>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1 small">⭐ Próximo Evento</span>
                    </div>
                    <h2 class="fw-bold mb-3" style="font-size:clamp(1.15rem,2.5vw,1.6rem);line-height:1.3;"><?= e($featured['title']) ?></h2>
                    <div class="d-flex flex-wrap gap-4 mb-4">
                        <div class="d-flex align-items-center gap-2 small opacity-80">
                            <i class="bi bi-calendar3 text-warning"></i>
                            <span><?= $fDate->format('d \d\e F \d\e Y') ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 small opacity-80">
                            <i class="bi bi-clock text-warning"></i>
                            <span><?= e($featured['time']) ?> (<?= e($featured['timezone']) ?>)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 small opacity-80">
                            <i class="bi bi-people text-warning"></i>
                            <span><?= $featured['enrolled'] ?>/<?= $featured['capacity'] ?> inscritos</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle bg-white bg-opacity-15 d-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:1.2rem;">🎤</div>
                        <div>
                            <div class="fw-bold small"><?= e($featured['speaker']) ?></div>
                            <div class="opacity-60" style="font-size:.75rem;"><?= e($featured['company']) ?></div>
                        </div>
                    </div>
                    <!-- Barra de cupos -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between small mb-1 opacity-75">
                            <span>Cupos disponibles</span>
                            <span><?= $featured['capacity'] - $featured['enrolled'] ?> restantes</span>
                        </div>
                        <div class="progress" style="height:6px;background:rgba(255,255,255,.2);">
                            <div class="progress-bar bg-warning" style="width:<?= round($featured['enrolled']/$featured['capacity']*100) ?>%"></div>
                        </div>
                    </div>
                    <a href="<?= e($featured['link']) ?>" target="_blank" class="btn btn-warning rounded-pill px-5 fw-bold text-dark shadow">
                        <i class="bi bi-calendar-plus me-2"></i>Reservar mi lugar
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grid de eventos -->
        <div class="row g-4" id="eventsGrid">
            <?php foreach (array_filter($events, fn($e) => !$e['featured']) as $ev):
                $evDate = new DateTime($ev['date']);
                $pct = round($ev['enrolled']/$ev['capacity']*100);
            ?>
            <div class="col-lg-4 col-md-6 event-card" data-type="<?= $ev['type'] ?>">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-interactive">
                    <div class="position-relative" style="height:180px;">
                        <img src="<?= e($ev['img']) ?>" class="w-100 h-100" alt="<?= e($ev['title']) ?>" style="object-fit:cover;">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,.6));"></div>
                        <span class="badge rounded-pill position-absolute top-0 end-0 m-3 px-3 fw-bold small" style="background:<?= $ev['color'] ?>;"><?= e($ev['type']) ?></span>
                        <!-- Fecha flotante -->
                        <div class="position-absolute bottom-0 start-0 m-3 text-white">
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="bi bi-calendar3"></i>
                                <span><?= $evDate->format('d M Y') ?> · <?= e($ev['time']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold text-dark mb-2" style="font-size:.95rem;line-height:1.4;"><?= e($ev['title']) ?></h5>
                        <div class="d-flex align-items-center gap-2 small text-muted mb-3">
                            <i class="bi bi-mic"></i>
                            <span><?= e($ev['speaker']) ?> · <?= e($ev['company']) ?></span>
                        </div>
                        <!-- Cupos -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span><?= $ev['enrolled'] ?> inscritos</span>
                                <span><?= $ev['capacity'] - $ev['enrolled'] ?> cupos libres</span>
                            </div>
                            <div class="progress" style="height:5px;">
                                <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $ev['color'] ?>;"></div>
                            </div>
                        </div>
                        <a href="<?= e($ev['link']) ?>" target="_blank" class="btn btn-outline-primary rounded-pill btn-sm fw-semibold mt-auto">
                            <i class="bi bi-calendar-plus me-1"></i>Inscribirme
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- CTA Newsletter -->
        <div class="mt-5 p-5 rounded-4 text-center text-white" style="background:linear-gradient(135deg,#1a73e8,#6d28d9);">
            <i class="bi bi-envelope-heart display-4 mb-3 d-block opacity-75"></i>
            <h4 class="fw-bold mb-2">¿No quieres perderte ningún evento?</h4>
            <p class="opacity-75 mb-4 small">Suscríbete a nuestro boletín y recibe alertas de nuevos webinars, talleres y conferencias.</p>
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="input-group shadow-sm">
                        <input type="email" class="form-control border-0 rounded-pill-start py-2" placeholder="Tu correo electrónico" style="border-radius:50px 0 0 50px;">
                        <button class="btn btn-warning fw-bold text-dark px-4" style="border-radius:0 50px 50px 0;">Suscribirme</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
document.querySelectorAll('.type-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.type-filter-btn').forEach(b => {
            b.className = 'btn btn-outline-secondary rounded-pill btn-sm px-3 type-filter-btn';
        });
        this.className = 'btn btn-primary text-white rounded-pill btn-sm px-3 type-filter-btn';
        const type = this.dataset.type;
        document.querySelectorAll('.event-card').forEach(card => {
            card.style.display = (type === 'Todos' || card.dataset.type === type) ? '' : 'none';
        });
    });
});
</script>

<style>
.card-interactive:hover { transform: translateY(-4px); box-shadow:0 12px 32px rgba(0,0,0,.1) !important; }
.card-interactive { transition: transform .3s ease, box-shadow .3s ease; }
</style>
