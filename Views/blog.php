<?php
/* ── Mock blog posts (sustituir por consulta BD real) ── */
$posts = [
    ['id'=>1,'title'=>'5 estrategias para aprender programación en 2026','category'=>'Tecnología','date'=>'10 Jul 2026','readTime'=>'6 min','img'=>'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=800&q=80','excerpt'=>'Descubre las metodologías más efectivas que los mejores profesionales del software utilizan para mantenerse actualizados en un entorno que cambia rápidamente.','author'=>'Ing. Carlos Mendoza','featured'=>true],
    ['id'=>2,'title'=>'Cómo construir un portafolio profesional que impresione','category'=>'Carrera','date'=>'8 Jul 2026','readTime'=>'4 min','img'=>'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=800&q=80','excerpt'=>'Tu portafolio es tu tarjeta de presentación en el mundo digital. Aprende qué proyectos incluir y cómo estructurarlo para cada área profesional.','author'=>'Dra. Laura Ríos','featured'=>false],
    ['id'=>3,'title'=>'Inteligencia Artificial aplicada al aprendizaje adaptativo','category'=>'IA & Educación','date'=>'5 Jul 2026','readTime'=>'8 min','img'=>'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?auto=format&fit=crop&w=800&q=80','excerpt'=>'La IA está transformando la educación. En este artículo analizamos cómo los sistemas LMS del futuro personalizarán la experiencia de cada estudiante.','author'=>'MSc. Andrés Torres','featured'=>false],
    ['id'=>4,'title'=>'Microaprendizaje: formación efectiva en sesiones cortas','category'=>'Pedagogía','date'=>'2 Jul 2026','readTime'=>'5 min','img'=>'https://images.unsplash.com/photo-1507878866276-a947ef722fee?auto=format&fit=crop&w=800&q=80','excerpt'=>'El microlearning se ha convertido en una de las tendencias más sólidas en capacitación corporativa. Te explicamos cómo aprovecharlo al máximo.','author'=>'PhD. Marcela Vanegas','featured'=>false],
    ['id'=>5,'title'=>'Design Thinking para resolver problemas en el aula virtual','category'=>'Diseño','date'=>'28 Jun 2026','readTime'=>'7 min','img'=>'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80','excerpt'=>'Aplicar el pensamiento de diseño a la educación en línea permite crear experiencias más empáticas, iterativas y centradas en el estudiante.','author'=>'Lic. Fernando Castro','featured'=>false],
];
$categories = ['Todos','Tecnología','Carrera','IA & Educación','Pedagogía','Diseño','Negocios'];
?>

<section class="py-5 bg-light">
    <div class="container py-3">

        <!-- Header -->
        <div class="row align-items-center mb-5">
            <div class="col-md-8">
                <span class="badge bg-primary rounded-pill px-3 py-1 mb-2 small">📰 Blog NorthStar</span>
                <h1 class="fw-bold text-dark mb-2">Noticias, Tutoriales y Tendencias</h1>
                <p class="text-muted mb-0">Artículos escritos por expertos para mantenerte actualizado en el mundo del aprendizaje y la tecnología.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="input-group">
                    <input type="text" id="blogSearch" class="form-control rounded-pill-start border-end-0" placeholder="Buscar artículos..." style="border-radius:50px 0 0 50px;">
                    <button class="btn btn-primary border-start-0" style="border-radius:0 50px 50px 0;"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </div>

        <!-- Filtros de categoría -->
        <div class="d-flex flex-wrap gap-2 mb-5" id="catFilters">
            <?php foreach ($categories as $cat): ?>
            <button class="btn <?= $cat === 'Todos' ? 'btn-primary text-white' : 'btn-outline-secondary' ?> rounded-pill btn-sm px-4 cat-filter-btn" data-cat="<?= $cat ?>">
                <?= $cat ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Artículo Destacado -->
        <?php $featured = array_filter($posts, fn($p) => $p['featured']); $f = reset($featured); ?>
        <?php if ($f): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 blog-card" data-category="<?= $f['category'] ?>">
            <div class="row g-0">
                <div class="col-md-6">
                    <img src="<?= e($f['img']) ?>" class="img-fluid h-100 w-100" alt="<?= e($f['title']) ?>" style="object-fit:cover;min-height:280px;">
                </div>
                <div class="col-md-6 p-5 d-flex flex-column justify-content-center">
                    <div class="d-flex gap-2 mb-3">
                        <span class="badge bg-warning text-dark rounded-pill px-3">⭐ Destacado</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3"><?= e($f['category']) ?></span>
                    </div>
                    <h2 class="fw-bold text-dark mb-3" style="font-size:1.45rem;line-height:1.35;"><?= e($f['title']) ?></h2>
                    <p class="text-muted small mb-4"><?= e($f['excerpt']) ?></p>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="small text-muted"><i class="bi bi-person me-1"></i><?= e($f['author']) ?> · <i class="bi bi-clock me-1 ms-2"></i><?= e($f['readTime']) ?></div>
                        <a href="#" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold text-white">Leer artículo <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grid de artículos -->
        <div class="row g-4" id="blogGrid">
            <?php foreach (array_filter($posts, fn($p) => !$p['featured']) as $post): ?>
            <div class="col-lg-4 col-md-6 blog-card" data-category="<?= $post['category'] ?>">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-interactive">
                    <div class="position-relative overflow-hidden" style="height:200px;">
                        <img src="<?= e($post['img']) ?>" class="w-100 h-100" alt="<?= e($post['title']) ?>" style="object-fit:cover;transition:transform .4s ease;">
                        <span class="badge bg-primary rounded-pill position-absolute top-0 start-0 m-3 px-3 small"><?= e($post['category']) ?></span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold text-dark mb-2" style="font-size:.97rem;line-height:1.4;"><?= e($post['title']) ?></h5>
                        <p class="text-muted small flex-grow-1 mb-3"><?= e($post['excerpt']) ?></p>
                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                            <div class="text-muted" style="font-size:.72rem;"><i class="bi bi-calendar3 me-1"></i><?= e($post['date']) ?> · <i class="bi bi-clock me-1 ms-1"></i><?= e($post['readTime']) ?></div>
                            <a href="#" class="btn btn-outline-primary rounded-pill btn-sm px-3 fw-semibold">Leer</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginación simple -->
        <div class="d-flex justify-content-center mt-5">
            <nav>
                <ul class="pagination gap-1">
                    <li class="page-item"><a class="page-link rounded-3 border-0" href="#"><i class="bi bi-chevron-left"></i></a></li>
                    <li class="page-item active"><a class="page-link rounded-3 border-0 bg-primary text-white" href="#">1</a></li>
                    <li class="page-item"><a class="page-link rounded-3 border-0" href="#">2</a></li>
                    <li class="page-item"><a class="page-link rounded-3 border-0" href="#">3</a></li>
                    <li class="page-item"><a class="page-link rounded-3 border-0" href="#"><i class="bi bi-chevron-right"></i></a></li>
                </ul>
            </nav>
        </div>

    </div>
</section>

<script>
/* Filtro por categoría */
document.querySelectorAll('.cat-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cat-filter-btn').forEach(b => {
            b.className = 'btn btn-outline-secondary rounded-pill btn-sm px-4 cat-filter-btn';
        });
        this.className = 'btn btn-primary text-white rounded-pill btn-sm px-4 cat-filter-btn';
        const cat = this.dataset.cat;
        document.querySelectorAll('.blog-card').forEach(card => {
            card.style.display = (cat === 'Todos' || card.dataset.category === cat) ? '' : 'none';
        });
    });
});

/* Búsqueda en tiempo real */
document.getElementById('blogSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#blogGrid .blog-card, .blog-card').forEach(card => {
        const title = card.querySelector('h2,h5')?.textContent.toLowerCase() || '';
        card.style.display = (!q || title.includes(q)) ? '' : 'none';
    });
});
</script>

<style>
.card-interactive:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.1) !important; }
.card-interactive img { transition: transform .4s ease; }
.card-interactive:hover img { transform: scale(1.04); }
</style>
