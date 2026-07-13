<?php
// Filtrado activo en PHP basado en parámetros GET
$filteredCourses = $courses;

// 1. Filtrar por Nombre / Búsqueda
if (!empty($_GET['search'])) {
    $search = mb_strtolower(trim($_GET['search']), 'UTF-8');
    $filteredCourses = array_filter($filteredCourses, function($c) use ($search) {
        return mb_strpos(mb_strtolower($c['nombre'] ?? '', 'UTF-8'), $search) !== false ||
               mb_strpos(mb_strtolower($c['descripcion_corta'] ?? '', 'UTF-8'), $search) !== false ||
               mb_strpos(mb_strtolower($c['descripcion_larga'] ?? '', 'UTF-8'), $search) !== false;
    });
}

// 2. Filtrar por Categoría
if (!empty($_GET['categoria'])) {
    $cat = mb_strtolower(trim($_GET['categoria']), 'UTF-8');
    $filteredCourses = array_filter($filteredCourses, function($c) use ($cat) {
        return mb_strtolower($c['categoria_nombre'] ?? '', 'UTF-8') === $cat;
    });
}

// 3. Filtrar por Instructor
if (!empty($_GET['instructor'])) {
    $inst = mb_strtolower(trim($_GET['instructor']), 'UTF-8');
    $filteredCourses = array_filter($filteredCourses, function($c) use ($inst) {
        return mb_strpos(mb_strtolower($c['instructor'] ?? '', 'UTF-8'), $inst) !== false;
    });
}

// 4. Filtrar por Nivel
if (!empty($_GET['nivel'])) {
    $nivel = mb_strtolower(trim($_GET['nivel']), 'UTF-8');
    if ($nivel !== 'todos') {
        $filteredCourses = array_filter($filteredCourses, function($c) use ($nivel) {
            return mb_strtolower($c['nivel'] ?? '', 'UTF-8') === $nivel;
        });
    }
}

// 5. Filtrar por Precio
if (!empty($_GET['precio'])) {
    $precio = mb_strtolower(trim($_GET['precio']), 'UTF-8');
    if ($precio === 'gratuito') {
        $filteredCourses = array_filter($filteredCourses, function($c) {
            return (int)($c['gratuito'] ?? 0) === 1 || (float)($c['precio'] ?? 0) == 0;
        });
    } elseif ($precio === 'de-pago') {
        $filteredCourses = array_filter($filteredCourses, function($c) {
            return (int)($c['gratuito'] ?? 0) === 0 && (float)($c['precio'] ?? 0) > 0;
        });
    }
}

// 6. Filtrar por Idioma
if (!empty($_GET['idioma'])) {
    $idioma = mb_strtolower(trim($_GET['idioma']), 'UTF-8');
    if ($idioma !== 'todos') {
        $filteredCourses = array_filter($filteredCourses, function($c) use ($idioma) {
            return mb_strpos(mb_strtolower($c['idioma'] ?? '', 'UTF-8'), $idioma) !== false;
        });
    }
}

// 7. Filtrar por Certificado
if (isset($_GET['certificado']) && $_GET['certificado'] === '1') {
    $filteredCourses = array_filter($filteredCourses, function($c) {
        return (int)($c['certificado'] ?? 0) === 1;
    });
}

// 8. Criterios de Ordenación
$sortBy = $_GET['sort'] ?? 'recientes';
$order = $_GET['order'] ?? 'desc';

usort($filteredCourses, function($a, $b) use ($sortBy, $order) {
    if ($sortBy === 'populares') {
        $valA = $a['id'] * 17 + 23; 
        $valB = $b['id'] * 17 + 23;
    } elseif ($sortBy === 'calificados') {
        $valA = 4.5 + ($a['id'] % 5) * 0.1;
        $valB = 4.5 + ($b['id'] % 5) * 0.1;
    } else { // 'recientes' o fecha de creación
        $valA = strtotime($a['creado_en'] ?? '1970-01-01');
        $valB = strtotime($b['creado_en'] ?? '1970-01-01');
    }

    if ($valA == $valB) return 0;
    
    if ($order === 'asc') {
        return ($valA < $valB) ? -1 : 1;
    } else {
        return ($valA > $valB) ? -1 : 1;
    }
});

// Contar cursos resultantes
$totalResultados = count($filteredCourses);
?>

<section class="py-5 bg-light">
    <div class="container py-4">
        <!-- Breadcrumb e Introducción -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?= url('index.php?action=landing') ?>" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Catálogo de Cursos</li>
            </ol>
        </nav>
        
        <div class="row align-items-center mb-4 g-3">
            <div class="col-md-8">
                <h1 class="fw-bold text-dark mb-1">Catálogo de Cursos</h1>
                <p class="text-muted mb-0">Explora programas académicos, especializaciones y cursos prácticos con soporte profesional.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <!-- Botón móvil para mostrar filtros -->
                <button class="btn btn-outline-primary rounded-pill d-lg-none w-100" type="button" data-bs-toggle="collapse" data-bs-target="#filtersSidebar" aria-expanded="false" aria-controls="filtersSidebar">
                    <i class="bi bi-funnel me-1"></i> Mostrar / Ocultar Filtros
                </button>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- COLUMNA 1: FILTROS LATERALES (3 COLS) -->
            <div class="col-lg-3">
                <div class="collapse d-lg-block" id="filtersSidebar">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white position-sticky" style="top: 100px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-funnel-fill text-primary me-2"></i>Filtros</h5>
                            <a href="<?= url('index.php?action=catalog') ?>" class="text-decoration-none small text-muted hover-primary">Limpiar</a>
                        </div>
                        
                        <form action="<?= url('index.php') ?>" method="GET" class="d-flex flex-column gap-3.5">
                            <input type="hidden" name="action" value="catalog">
                            
                            <!-- Búsqueda por Nombre -->
                            <div>
                                <label class="form-label small fw-bold text-dark mb-1.5">Buscar por nombre</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Ej: Programación..." value="<?= e($_GET['search'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <!-- Categorías -->
                            <div>
                                <label class="form-label small fw-bold text-dark mb-1.5">Categoría</label>
                                <select name="categoria" class="form-select form-select-sm">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <?php 
                                        $selected = (!empty($_GET['categoria']) && strtolower(trim($_GET['categoria'])) === strtolower(trim($cat['nombre']))) ? 'selected' : '';
                                        ?>
                                        <option value="<?= e($cat['nombre']) ?>" <?= $selected ?>><?= e($cat['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Instructor -->
                            <div>
                                <label class="form-label small fw-bold text-dark mb-1.5">Instructor</label>
                                <input type="text" name="instructor" class="form-control form-control-sm" placeholder="Ej: Ruiz..." value="<?= e($_GET['instructor'] ?? '') ?>">
                            </div>
                            
                            <!-- Nivel -->
                            <div>
                                <label class="form-label small fw-bold text-dark mb-1.5">Nivel acadámico</label>
                                <select name="nivel" class="form-select form-select-sm">
                                    <option value="todos">Todos los niveles</option>
                                    <option value="básico" <?= (!empty($_GET['nivel']) && $_GET['nivel'] === 'básico') ? 'selected' : '' ?>>Básico</option>
                                    <option value="intermedio" <?= (!empty($_GET['nivel']) && $_GET['nivel'] === 'intermedio') ? 'selected' : '' ?>>Intermedio</option>
                                    <option value="avanzado" <?= (!empty($_GET['nivel']) && $_GET['nivel'] === 'avanzado') ? 'selected' : '' ?>>Avanzado</option>
                                </select>
                            </div>
                            
                            <!-- Rango de Precio -->
                            <div>
                                <label class="form-label small fw-bold text-dark mb-1.5">Precio</label>
                                <select name="precio" class="form-select form-select-sm">
                                    <option value="">Todos los precios</option>
                                    <option value="gratuito" <?= (!empty($_GET['precio']) && $_GET['precio'] === 'gratuito') ? 'selected' : '' ?>>Cursos Gratuitos</option>
                                    <option value="de-pago" <?= (!empty($_GET['precio']) && $_GET['precio'] === 'de-pago') ? 'selected' : '' ?>>Cursos de Pago</option>
                                </select>
                            </div>
                            
                            <!-- Idioma -->
                            <div>
                                <label class="form-label small fw-bold text-dark mb-1.5">Idioma</label>
                                <select name="idioma" class="form-select form-select-sm">
                                    <option value="todos">Todos</option>
                                    <option value="Español" <?= (!empty($_GET['idioma']) && $_GET['idioma'] === 'Español') ? 'selected' : '' ?>>Español</option>
                                    <option value="Inglés" <?= (!empty($_GET['idioma']) && $_GET['idioma'] === 'Inglés') ? 'selected' : '' ?>>Inglés</option>
                                </select>
                            </div>
                            
                            <!-- Ordenación por Criterio -->
                            <div>
                                <label class="form-label small fw-bold text-dark mb-1.5">Ordenar por</label>
                                <select name="sort" class="form-select form-select-sm">
                                    <option value="recientes" <?= ($sortBy === 'recientes') ? 'selected' : '' ?>>Más recientes</option>
                                    <option value="populares" <?= ($sortBy === 'populares') ? 'selected' : '' ?>>Más populares</option>
                                    <option value="calificados" <?= ($sortBy === 'calificados') ? 'selected' : '' ?>>Mejor calificados</option>
                                </select>
                            </div>
                            
                            <!-- Dirección del Orden -->
                            <div>
                                <label class="form-label small fw-bold text-dark mb-1.5">Dirección</label>
                                <select name="order" class="form-select form-select-sm">
                                    <option value="desc" <?= ($order === 'desc') ? 'selected' : '' ?>>Descendente</option>
                                    <option value="asc" <?= ($order === 'asc') ? 'selected' : '' ?>>Ascendente</option>
                                </select>
                            </div>
                            
                            <!-- Checkbox Certificado -->
                            <div class="form-check my-1">
                                <input class="form-check-input" type="checkbox" name="certificado" value="1" id="certificadoCheck" <?= (isset($_GET['certificado']) && $_GET['certificado'] === '1') ? 'checked' : '' ?>>
                                <label class="form-check-label small text-muted" for="certificadoCheck">
                                    Con Certificado Oficial
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary rounded-pill w-100 text-white fw-semibold btn-sm py-2 mt-2 shadow-sm">
                                Aplicar Filtros
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- COLUMNA 2: GRID DE RESULTADOS (9 COLS) -->
            <div class="col-lg-9">
                <!-- Indicador de resultados -->
                <div class="d-flex justify-content-between align-items-center mb-3 text-muted small bg-white p-3 rounded-4 shadow-sm">
                    <div>
                        Se encontraron <span class="fw-bold text-dark"><?= $totalResultados ?></span> cursos disponibles
                    </div>
                    <div class="d-none d-sm-block">
                        Mostrando en formato SaaS empresarial
                    </div>
                </div>
                
                <!-- Grid de Tarjetas -->
                <div class="row g-4">
                    <?php if ($totalResultados === 0): ?>
                        <div class="col-12 py-5 text-center">
                            <div class="p-5 bg-white rounded-4 shadow-sm max-width-500 mx-auto">
                                <i class="bi bi-search-heart fs-1 text-muted mb-3"></i>
                                <h4 class="fw-bold text-dark mb-2">No se encontraron cursos</h4>
                                <p class="text-muted small">Intenta modificando los criterios de búsqueda o limpia los filtros para ver todos los cursos disponibles.</p>
                                <a href="<?= url('index.php?action=catalog') ?>" class="btn btn-primary rounded-pill px-4 text-white shadow-sm mt-3">Ver Todos los Cursos</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($filteredCourses as $index => $course): 
                            $level = $course['nivel'] ?? 'Básico';
                            $duration = $course['duracion'] ?? 'Flexible';
                            $category = $course['categoria_nombre'] ?? 'Sin categoría';
                            $cover = $course['imagen'] ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80';
                            $instructor = $course['instructor'] ?? 'Instructor disponible';
                            $price = $course['precio'] ?? 0;
                            $isFree = $course['gratuito'] ?? 0;
                            $lastUpdate = !empty($course['publicado_en'] ?? $course['creado_en']) ? date('d/m/Y', strtotime($course['publicado_en'] ?? $course['creado_en'])) : 'Flexible';
                            
                            // Mock progress if user is enrolled
                            $isEnrolled = false;
                            $progressVal = 0;
                            
                            // Simulación de valoraciones y métricas
                            $rating = 4.5 + ($course['id'] % 5) * 0.1;
                            $reviewsCount = 15 + ($course['id'] * 11);
                            $studentsCount = 90 + ($course['id'] * 41);
                            
                            // Estados
                            $badgeText = ($course['id'] % 3 == 0) ? 'Nuevo' : (($course['id'] % 3 == 1) ? 'Popular' : 'Destacado');
                            $badgeBg = ($badgeText == 'Nuevo') ? 'bg-success' : (($badgeText == 'Popular') ? 'bg-warning text-dark' : 'bg-primary');
                        ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-interactive" style="transition: all 0.3s ease;">
                                <div class="position-relative">
                                    <img src="<?= e($cover) ?>" class="card-img-top" alt="<?= e($course['nombre']) ?>" style="height: 180px; object-fit: cover;">
                                    <span class="badge position-absolute top-0 start-0 m-3 <?= $badgeBg ?> text-white rounded-pill px-2.5 py-1.5 shadow-sm"><?= $badgeText ?></span>
                                    <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white px-2 py-1 m-3 rounded-2 small">
                                        <i class="bi bi-clock me-1"></i><?= e($duration) ?>
                                    </div>
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                                        <span class="badge rounded-pill bg-light text-primary border px-2.5 py-1.5 fs-7 fw-bold"><?= e($category) ?></span>
                                        <small class="text-muted"><i class="bi bi-bar-chart me-1"></i><?= e($level) ?></small>
                                    </div>
                                    
                                    <h5 class="card-title fw-bold text-dark mb-2 lh-base fs-6"><?= e($course['nombre']) ?></h5>
                                    <p class="text-muted small mb-3 text-truncate-2"><?= e($course['descripcion_corta']) ?></p>
                                    
                                    <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-3 mt-auto">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted" style="width: 28px; height: 28px; font-size: 0.8rem;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div class="small">
                                            <div class="fw-semibold text-dark" style="font-size: 0.82rem;"><?= e($instructor) ?></div>
                                            <div class="text-muted small" style="font-size: 0.7rem;">Docente</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Valoración y Estudiantes -->
                                    <div class="d-flex justify-content-between align-items-center mb-2 text-muted small" style="font-size: 0.8rem;">
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <span class="fw-bold text-dark"><?= number_format($rating, 1) ?></span>
                                            <span>(<?= $reviewsCount ?>)</span>
                                        </div>
                                        <div>
                                            <i class="bi bi-people me-1"></i><?= $studentsCount ?> alumnos
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted small" style="font-size: 0.72rem;">Act: <?= e($lastUpdate) ?></span>
                                        <span class="fw-bold fs-5 text-primary">
                                            <?= ($isFree || $price == 0) ? 'Gratuito' : '$' . number_format($price, 2, ',', '.') ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Botones de Acción -->
                                    <div class="row g-2">
                                        <div class="col-9">
                                            <a href="<?= url('index.php?action=course&id=' . (int) $course['id']) ?>" class="btn btn-primary rounded-pill w-100 text-white fw-semibold btn-sm py-2">
                                                Ver Curso
                                            </a>
                                        </div>
                                        <div class="col-3 text-center">
                                            <button class="btn btn-light rounded-circle w-100 py-1.5 btn-sm border-0" title="Agregar a Favoritos" onclick="alert('Se ha guardado en favoritos temporales.');">
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
        </div>
    </div>
</section>
