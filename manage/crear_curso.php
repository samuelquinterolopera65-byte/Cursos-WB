<?php
require_once '../config/db.php';
require_once '../app/Config/database.php';
require_once '../models/Curso.php';
require_once '../models/Usuario.php';

$is_wizard = true;
session_start();

// Check authorization for admins and course creators
if (!isset($_SESSION['user_role']) || !in_array((int) $_SESSION['user_role'], [1, 2], true)) {
    header("Location: ../login.php");
    exit;
}

$cursoModel = new Curso($conn);
$usuarioModel = new Usuario($conn);

$categorias = $conn->query("SELECT * FROM lc_categorias WHERE estado = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

try {
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS video_intro_url VARCHAR(255) NULL");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS modalidad VARCHAR(40) DEFAULT 'Online'");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS publicado_en TIMESTAMP NULL");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS destacado TINYINT(1) DEFAULT 0");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS mostrar_en_portada TINYINT(1) DEFAULT 1");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS permite_comentarios TINYINT(1) DEFAULT 1");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS modulos_previstos TEXT NULL");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS criterios_evaluacion TEXT NULL");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS recursos TEXT NULL");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS meta_titulo VARCHAR(160) NULL");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS meta_descripcion VARCHAR(255) NULL");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS acceso_restringido TINYINT(1) DEFAULT 0");
    $conn->exec("ALTER TABLE lc_cursos ADD COLUMN IF NOT EXISTS codigo_acceso VARCHAR(80) NULL");
} catch (PDOException $e) {
    // continuar sin detener el flujo si el motor no soporta el alter
}

// Verify specific permission
if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')) {
    header("Location: index.php?tab=cursos&error=" . urlencode("No tienes permiso para crear cursos."));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $descripcion_corta = trim($_POST['descripcion_corta'] ?? '');
    $descripcion_larga = trim($_POST['descripcion_larga'] ?? $descripcion);
    $codigo = trim($_POST['codigo'] ?? '');
    $imagen = '';
    $materiales = trim($_POST['materiales']);
    $categoria = trim($_POST['categoria'] ?? '');
    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
    $instructor = trim($_POST['instructor'] ?? '');
    $duracion = trim($_POST['duracion'] ?? '');
    $horas = !empty($_POST['horas']) ? intval($_POST['horas']) : 0;
    $nivel = trim($_POST['nivel'] ?? 'Básico');
    $idioma = trim($_POST['idioma'] ?? 'Español');
    $modalidad = trim($_POST['modalidad'] ?? 'Online');
    $video_intro_url = trim($_POST['video_intro_url'] ?? '');
    $precio = !empty($_POST['precio']) ? floatval($_POST['precio']) : 0.00;
    $gratuito = isset($_POST['gratuito']) ? 1 : 0;
    $certificado = isset($_POST['certificado']) ? 1 : 0;
    $objetivos_raw = $_POST['objetivos'] ?? [];
    $objetivos = is_array($objetivos_raw)
        ? implode(PHP_EOL, array_filter(array_map('trim', $objetivos_raw), fn($value) => $value !== ''))
        : trim($objetivos_raw);
    $competencias = trim($_POST['competencias'] ?? '');
    $requisitos = trim($_POST['requisitos'] ?? '');
    $bibliografia = trim($_POST['bibliografia'] ?? '');
    $etiquetas = trim($_POST['etiquetas'] ?? '');
    $modulos_previstos_raw = $_POST['modulos_previstos'] ?? [];
    $modulos_previstos = is_array($modulos_previstos_raw)
        ? implode(PHP_EOL, array_filter(array_map('trim', $modulos_previstos_raw), fn($value) => $value !== ''))
        : trim($modulos_previstos_raw);
    $criterios_evaluacion = trim($_POST['criterios_evaluacion'] ?? '');
    $recursos = trim($_POST['recursos'] ?? '');
    $seo_blocks = isset($_POST['seo_items']) ? $_POST['seo_items'] : [];
    $meta_titulo = '';
    $meta_descripcion = '';
    if (!empty($seo_blocks)) {
        $seo_titles = array_filter(array_map(function ($item) { return trim($item['meta_titulo'] ?? ''); }, $seo_blocks), fn($value) => $value !== '');
        $seo_descriptions = array_filter(array_map(function ($item) { return trim($item['meta_descripcion'] ?? ''); }, $seo_blocks), fn($value) => $value !== '');
        $meta_titulo = implode(' | ', $seo_titles);
        $meta_descripcion = implode(' | ', $seo_descriptions);
    } else {
        $meta_titulo = trim($_POST['meta_titulo'] ?? '');
        $meta_descripcion = trim($_POST['meta_descripcion'] ?? '');
    }
    $publicado_en = trim($_POST['publicado_en'] ?? '');
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $mostrar_en_portada = isset($_POST['mostrar_en_portada']) ? 1 : 0;
    $permite_comentarios = isset($_POST['permite_comentarios']) ? 1 : 0;
    $acceso_restringido = isset($_POST['acceso_restringido']) ? 1 : 0;
    $codigo_acceso = trim($_POST['codigo_acceso'] ?? '');
    $cupo_tipo = $_POST['cupo_tipo'] ?? 'ilimitado';
    $cupo_limite = null;
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
    
    $campos_req_arr = isset($_POST['campos_req']) ? $_POST['campos_req'] : [];
    array_unshift($campos_req_arr, 'nombre', 'email');
    $campos_requeridos = implode(',', array_unique($campos_req_arr));

    if ($cupo_tipo == 'limitado') {
        $cupo_limite = intval($_POST['cupo_limite']);
        if ($cupo_limite <= 0) {
            $error = 'El cupo límite debe ser un número entero mayor a 0.';
        }
    }

    if (empty($titulo) || empty(trim(strip_tags($descripcion)))) {
        $error = 'El título y la descripción son obligatorios.';
    }

    if (empty($error) && isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['imagen_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $error = 'El formato de la imagen no es válido. Usa JPG, PNG, WEBP o GIF.';
        } else {
            $fileName = uniqid('curso_', true) . '.' . $ext;
            $targetPath = __DIR__ . '/../uploads/cursos/' . $fileName;

            if (move_uploaded_file($_FILES['imagen_file']['tmp_name'], $targetPath)) {
                $imagen = 'uploads/cursos/' . $fileName;
            } else {
                $error = 'No se pudo subir la imagen de portada.';
            }
        }
    }

    if (empty($imagen)) {
        $imagen = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=450&h=300&q=80';
    }

    if (empty($error)) {
        try {
            $stmt = $conn->prepare("INSERT INTO lc_cursos (nombre, codigo, imagen, descripcion_corta, descripcion_larga, categoria_id, instructor, duracion, horas, nivel, idioma, modalidad, video_intro_url, precio, gratuito, certificado, objetivos, competencias, requisitos, bibliografia, etiquetas, modulos_previstos, criterios_evaluacion, recursos, meta_titulo, meta_descripcion, publicado_en, destacado, mostrar_en_portada, permite_comentarios, acceso_restringido, codigo_acceso, estado) VALUES (:nombre, :codigo, :imagen, :descripcion_corta, :descripcion_larga, :categoria_id, :instructor, :duracion, :horas, :nivel, :idioma, :modalidad, :video_intro_url, :precio, :gratuito, :certificado, :objetivos, :competencias, :requisitos, :bibliografia, :etiquetas, :modulos_previstos, :criterios_evaluacion, :recursos, :meta_titulo, :meta_descripcion, :publicado_en, :destacado, :mostrar_en_portada, :permite_comentarios, :acceso_restringido, :codigo_acceso, :estado)");
            $stmt->execute([
                ':nombre' => $titulo,
                ':codigo' => $codigo,
                ':imagen' => $imagen,
                ':descripcion_corta' => $descripcion_corta,
                ':descripcion_larga' => $descripcion_larga,
                ':categoria_id' => $categoria_id,
                ':instructor' => $instructor,
                ':duracion' => $duracion,
                ':horas' => $horas,
                ':nivel' => $nivel,
                ':idioma' => $idioma,
                ':modalidad' => $modalidad,
                ':video_intro_url' => $video_intro_url,
                ':precio' => $precio,
                ':gratuito' => $gratuito,
                ':certificado' => $certificado,
                ':objetivos' => $objetivos,
                ':competencias' => $competencias,
                ':requisitos' => $requisitos,
                ':bibliografia' => $bibliografia,
                ':etiquetas' => $etiquetas,
                ':modulos_previstos' => $modulos_previstos,
                ':criterios_evaluacion' => $criterios_evaluacion,
                ':recursos' => $recursos,
                ':meta_titulo' => $meta_titulo,
                ':meta_descripcion' => $meta_descripcion,
                ':publicado_en' => !empty($publicado_en) ? $publicado_en : null,
                ':destacado' => $destacado,
                ':mostrar_en_portada' => $mostrar_en_portada,
                ':permite_comentarios' => $permite_comentarios,
                ':acceso_restringido' => $acceso_restringido,
                ':codigo_acceso' => $codigo_acceso,
                ':estado' => $estado == 1 ? 'publicado' : 'borrador'
            ]);
            $success = 'Curso creado con éxito. Redirigiendo al constructor visual...';
            header("refresh:1.5;url=course_builder.php?course_id=" . $conn->lastInsertId());
        } catch (PDOException $e) {
            $error = 'Error al guardar el curso: ' . $e->getMessage();
        }
    }
}

$page_title = 'Crear Curso - Panel de Administración';
// Include modular admin layout header
require_once '../includes/manage/header.php';
?>

    <style>
        .course-builder-shell { position: relative; }
        .course-builder-nav {
            position: sticky;
            top: 1rem;
            z-index: 1040;
            backdrop-filter: blur(18px);
            background: rgba(255,255,255,0.95);
            border: 1px solid rgba(15,23,42,0.06);
        }
        .course-builder-nav .course-title-input {
            border: 0;
            background: transparent;
            padding: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--bs-dark);
            box-shadow: none;
        }
        .course-builder-nav .course-title-input:focus { box-shadow: none; }
        .wizard-step-pill {
            border: 0;
            border-radius: 999px;
            background: #f8fafc;
            padding: 0.7rem 0.95rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            transition: all .2s ease;
            min-width: fit-content;
            white-space: nowrap;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .wizard-step-pill .step-num {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e2e8f0;
            color: #334155;
            font-size: 0.78rem;
        }
        .wizard-step-pill.active {
            background: linear-gradient(135deg, rgba(26,115,232,.14), rgba(31,120,180,.16));
            color: #0f172a;
            box-shadow: inset 0 0 0 1px rgba(26,115,232,.14);
        }
        .wizard-step-pill.active .step-num {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }
        .wizard-step-pill.completed .step-num {
            background: #16a34a;
            color: white;
        }
        .wizard-step-pill.locked { opacity: 0.55; }
        .builder-help-card {
            background: linear-gradient(135deg, rgba(26,115,232,.07), rgba(16,185,129,.04));
            border: 1px solid rgba(15,23,42,.06);
        }
        .builder-help-card .help-bullet { font-size: .92rem; }
        .builder-ghost-card { background: #f8fafc; border: 1px solid rgba(15,23,42,.06); }
        .builder-step-pane { display: none; }
        .builder-step-pane.active { display: block; }
        .builder-workspace {
            border: 1px solid rgba(15, 23, 42, 0.06);
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }
        .component-card {
            border: 1px solid rgba(15,23,42,0.08);
            border-radius: 16px;
            padding: 0.9rem 0.95rem;
            background: #fff;
            color: #0f172a;
            text-align: left;
            transition: all .2s ease;
            box-shadow: 0 1px 3px rgba(15,23,42,0.04);
        }
        .component-card:hover,
        .component-card.active {
            border-color: rgba(26,115,232,0.25);
            box-shadow: 0 14px 30px rgba(26,115,232,0.12);
            transform: translateY(-2px);
            color: #0f172a;
        }
        .component-card .icon-badge {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(26,115,232,0.08);
            color: #1a73e8;
        }
        .builder-module-pill {
            border: 0;
            background: #f8fafc;
            color: #64748b;
            border-radius: 999px;
            padding: 0.6rem 0.95rem;
            font-weight: 600;
            font-size: 0.82rem;
            transition: all .2s ease;
        }
        .builder-module-pill.active {
            background: linear-gradient(135deg, rgba(26,115,232,0.16), rgba(16,185,129,0.12));
            color: #0f172a;
        }
        .builder-section-pane { display: none; }
        .builder-section-pane.active { display: block; }
        .builder-preview-card {
            position: sticky;
            top: 1rem;
            border: 1px solid rgba(15,23,42,0.08);
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }
        .builder-preview-cover {
            min-height: 170px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(26,115,232,0.16), rgba(16,185,129,0.16));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a73e8;
            font-size: 2.2rem;
        }
        .builder-preview-list { font-size: 0.9rem; }
    </style>

    <!-- Main Content -->
    <div class="container-fluid my-4 px-4 course-builder-shell">

        <div class="card border-0 rounded-4 shadow-sm p-3.5 mb-4 bg-white course-builder-nav">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-magic fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <input type="text" id="courseTitleHeader" class="form-control course-title-input" placeholder="Nombre del curso" value="">
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                            <span class="badge rounded-pill bg-warning-subtle text-warning">Borrador</span>
                            <span class="small text-muted" id="lastSavedLabel">Última modificación: ahora</span>
                            <span class="small text-success" id="autosaveStatus"><i class="bi bi-cloud-arrow-up me-1"></i>Guardado automático</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" id="prevStepBtnTop">
                        <i class="bi bi-arrow-left me-1"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-3 rounded-pill text-white fw-medium shadow-sm" id="nextStepBtnTop">
                        Siguiente <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-light btn-sm border px-3 rounded-pill text-dark" id="saveBorradorBtnTop">
                        <i class="bi bi-floppy me-1"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-success btn-sm px-4 rounded-pill fw-semibold text-white shadow-sm" id="publishBtnTop">
                        <i class="bi bi-rocket-takeoff me-1"></i> Publicar
                    </button>
                    <a href="index.php?tab=cursos" class="btn btn-outline-danger btn-sm px-3 rounded-pill">
                        <i class="bi bi-box-arrow-left me-1"></i> Salir
                    </a>
                </div>
            </div>
            <div class="progress mt-3.5" style="height: 6px;">
                <div class="progress-bar" role="progressbar" id="wizardProgressBar" style="width: 7%"></div>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?> <em>Redirigiendo...</em>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="builder-workspace card border-0 rounded-4 shadow-sm p-4 mb-4">
            <div class="row g-4">
                <div class="col-xl-3">
                    <div class="card border-0 rounded-4 shadow-sm p-3 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.06em; font-size:.7rem;">
                            <i class="bi bi-columns-gap me-1 text-primary"></i>Biblioteca de bloques
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" class="component-card active" data-module="info">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="icon-badge"><i class="bi bi-info-circle"></i></span>
                                    <span class="fw-semibold">Información</span>
                                </div>
                                <div class="small text-muted mt-2">Título, descripción, categoría y portada.</div>
                            </button>
                            <button type="button" class="component-card" data-module="constructor">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="icon-badge"><i class="bi bi-box-seam"></i></span>
                                    <span class="fw-semibold">Constructor</span>
                                </div>
                                <div class="small text-muted mt-2">Objetivos, módulos, recursos y video.</div>
                            </button>
                            <button type="button" class="component-card" data-module="learning">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="icon-badge"><i class="bi bi-mortarboard"></i></span>
                                    <span class="fw-semibold">Aprendizaje</span>
                                </div>
                                <div class="small text-muted mt-2">Competencias, requisitos y acreditación.</div>
                            </button>
                            <button type="button" class="component-card" data-module="config">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="icon-badge"><i class="bi bi-sliders"></i></span>
                                    <span class="fw-semibold">Configuración</span>
                                </div>
                                <div class="small text-muted mt-2">Nivel, modalidad, acceso y visibilidad.</div>
                            </button>
                            <button type="button" class="component-card" data-module="publish">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="icon-badge"><i class="bi bi-rocket-takeoff"></i></span>
                                    <span class="fw-semibold">Publicar</span>
                                </div>
                                <div class="small text-muted mt-2">SEO y checklist final.</div>
                            </button>
                        </div>
                    </div>
                    <div class="card border-0 rounded-4 shadow-sm p-3 bg-primary-subtle mt-3">
                        <div class="small text-uppercase fw-bold text-primary mb-2" style="letter-spacing:.06em; font-size:.7rem;">Consejo rápido</div>
                        <div class="small text-dark">Prioriza claridad, una propuesta concreta y una experiencia de matrícula sencilla para que el curso se vea premium desde el primer vistazo.</div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                            <div>
                                <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.06em; font-size:.7rem;">Editor visual</div>
                                <h5 class="fw-bold mb-1 text-dark">Diseña la propuesta de tu curso como una landing page</h5>
                                <div class="small text-muted">Cada bloque actualiza la vista previa en tiempo real y se guarda con el mismo flujo del asistente.</div>
                            </div>
                            <span class="badge rounded-pill bg-warning-subtle text-warning">Borrador</span>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <button type="button" class="builder-module-pill active" data-module="info">Información</button>
                            <button type="button" class="builder-module-pill" data-module="constructor">Constructor</button>
                            <button type="button" class="builder-module-pill" data-module="learning">Aprendizaje</button>
                            <button type="button" class="builder-module-pill" data-module="config">Configuración</button>
                            <button type="button" class="builder-module-pill" data-module="publish">Publicar</button>
                        </div>

                        <div class="builder-section-pane active" data-module-pane="info">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Nombre del curso</label>
                                    <input type="text" id="builderTitle" class="form-control form-control-premium" placeholder="Ej. Diseño UI con Figma y Bootstrap">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Código</label>
                                    <input type="text" id="builderCode" class="form-control form-control-premium" placeholder="Ej. UI-101">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Categoría</label>
                                    <input type="text" id="builderCategory" class="form-control form-control-premium" placeholder="Diseño, Programación, Negocios">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Descripción corta</label>
                                    <input type="text" id="builderShortDescription" class="form-control form-control-premium" placeholder="Resumen breve para el catálogo y la portada">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Descripción completa</label>
                                    <textarea id="builderDescription" rows="5" class="form-control form-control-premium" placeholder="Explica de qué trata el curso, qué aprenderán y por qué vale la pena"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Instructor principal</label>
                                    <input type="text" id="builderInstructor" class="form-control form-control-premium" placeholder="Sofía Ortega">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Duración</label>
                                    <input type="text" id="builderDuration" class="form-control form-control-premium" placeholder="6 semanas o 20 horas">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Nivel</label>
                                    <select id="builderLevel" class="form-select form-control-premium">
                                        <option value="Básico">Básico</option>
                                        <option value="Intermedio">Intermedio</option>
                                        <option value="Avanzado">Avanzado</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Modalidad</label>
                                    <select id="builderModality" class="form-select form-control-premium">
                                        <option value="Online">Online</option>
                                        <option value="Híbrido">Híbrido</option>
                                        <option value="Presencial">Presencial</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Idioma</label>
                                    <input type="text" id="builderLanguage" class="form-control form-control-premium" value="Español">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Precio</label>
                                    <input type="number" id="builderPrice" class="form-control form-control-premium" value="0" min="0" step="0.01">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="builderFree" checked>
                                        <label class="form-check-label small fw-bold" for="builderFree">Curso gratuito</label>
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="builderCertificate" checked>
                                        <label class="form-check-label small fw-bold" for="builderCertificate">Certificado incluido</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Etiquetas</label>
                                    <input type="text" id="builderTags" class="form-control form-control-premium" placeholder="ui, figma, bootstrap">
                                </div>
                            </div>
                        </div>

                        <div class="builder-section-pane" data-module-pane="constructor">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Objetivos de aprendizaje</label>
                                    <textarea id="builderObjectives" rows="5" class="form-control form-control-premium" placeholder="Escribe un objetivo por línea"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Módulos y unidades</label>
                                    <textarea id="builderModules" rows="5" class="form-control form-control-premium" placeholder="Módulo 1 · Fundamentos"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Recursos y materiales</label>
                                    <textarea id="builderResources" rows="4" class="form-control form-control-premium" placeholder="PDF, plantillas, enlaces o guías"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Video de introducción</label>
                                    <input type="url" id="builderVideo" class="form-control form-control-premium" placeholder="https://youtube.com/watch?v=...">
                                </div>
                            </div>
                        </div>

                        <div class="builder-section-pane" data-module-pane="learning">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Competencias esperadas</label>
                                    <textarea id="builderCompetencies" rows="4" class="form-control form-control-premium" placeholder="Diseñar interfaces accesibles, liderar presentaciones, etc."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Requisitos previos</label>
                                    <textarea id="builderRequirements" rows="4" class="form-control form-control-premium" placeholder="Herramientas, conocimientos previos o equipo necesario"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="builder-section-pane" data-module-pane="config">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Visibilidad</label>
                                    <select id="builderVisibility" class="form-select form-control-premium">
                                        <option value="Público">Público</option>
                                        <option value="Privado">Privado</option>
                                        <option value="Programado">Programado</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Acceso restringido</label>
                                    <select id="builderAccessMode" class="form-select form-control-premium">
                                        <option value="Libre">Libre</option>
                                        <option value="Con código">Con código</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Meta title</label>
                                    <input type="text" id="builderMetaTitle" class="form-control form-control-premium" placeholder="Diseño UI con Figma y Bootstrap">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Meta description</label>
                                    <textarea id="builderMetaDescription" rows="3" class="form-control form-control-premium" placeholder="Descripción breve para buscadores y redes"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="builder-section-pane" data-module-pane="publish">
                            <div class="card border-0 rounded-4 bg-light p-4">
                                <div class="fw-semibold text-dark mb-3">Checklist de publicación</div>
                                <div class="d-grid gap-2 small text-muted">
                                    <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Título claro y orientado a valor.</span></div>
                                    <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Descripción que traduzca el beneficio real.</span></div>
                                    <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Objetivos y módulos bien definidos.</span></div>
                                    <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Vista previa disponible y coherente.</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="card border-0 rounded-4 shadow-sm p-4 builder-preview-card">
                        <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.06em; font-size:.7rem;">
                            <i class="bi bi-eye me-1 text-primary"></i>Vista previa inmediata
                        </div>
                        <div class="builder-preview-cover mb-3">
                            <i class="bi bi-play-circle"></i>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark" id="builderPreviewTitle">Título del curso</h5>
                        <div class="d-flex flex-wrap gap-2 mb-3" id="builderPreviewMeta"></div>
                        <p class="text-muted small mb-3" id="builderPreviewShort">Describe brevemente lo que aprenderás y por qué este curso es valioso.</p>
                        <div class="small text-dark mb-3" id="builderPreviewDescription">Aquí aparecerá la descripción completa mientras editas.</div>
                        <div class="builder-preview-list">
                            <div class="fw-semibold text-dark mb-2">Qué incluye</div>
                            <div id="builderPreviewObjectives" class="d-grid gap-2"></div>
                            <div class="fw-semibold text-dark mt-3 mb-2">Ruta de aprendizaje</div>
                            <div id="builderPreviewModules" class="small text-muted"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 d-none">
            
            <div class="col-12 mb-4">
                <div class="card border-0 rounded-4 shadow-sm p-3.5 bg-white">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.06em; font-size:.7rem;">Asistente inteligente</div>
                            <div class="fw-semibold text-dark">Tu curso se construye en una sola página y se guarda automáticamente</div>
                        </div>
                        <div class="small text-muted">Progreso general: <span class="fw-semibold text-dark" id="wizardProgressLabel">7%</span></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2" id="wizardSteps">
                        <button type="button" class="wizard-step-pill active" data-step="1"><span class="step-num">1</span><span>General</span></button>
                        <button type="button" class="wizard-step-pill" data-step="2"><span class="step-num">2</span><span>Portada</span></button>
                        <button type="button" class="wizard-step-pill" data-step="3"><span class="step-num">3</span><span>Objetivos</span></button>
                        <button type="button" class="wizard-step-pill" data-step="4"><span class="step-num">4</span><span>Competencias</span></button>
                        <button type="button" class="wizard-step-pill" data-step="5"><span class="step-num">5</span><span>Requisitos</span></button>
                        <button type="button" class="wizard-step-pill" data-step="6"><span class="step-num">6</span><span>Estructura</span></button>
                        <button type="button" class="wizard-step-pill" data-step="7"><span class="step-num">7</span><span>Contenido</span></button>
                        <button type="button" class="wizard-step-pill" data-step="8"><span class="step-num">8</span><span>Recursos</span></button>
                        <button type="button" class="wizard-step-pill" data-step="9"><span class="step-num">9</span><span>Actividades</span></button>
                        <button type="button" class="wizard-step-pill" data-step="10"><span class="step-num">10</span><span>Evaluaciones</span></button>
                        <button type="button" class="wizard-step-pill" data-step="11"><span class="step-num">11</span><span>Certificado</span></button>
                        <button type="button" class="wizard-step-pill" data-step="12"><span class="step-num">12</span><span>Configuración</span></button>
                        <button type="button" class="wizard-step-pill" data-step="13"><span class="step-num">13</span><span>SEO</span></button>
                        <button type="button" class="wizard-step-pill" data-step="14"><span class="step-num">14</span><span>Vista previa</span></button>
                        <button type="button" class="wizard-step-pill" data-step="15"><span class="step-num">15</span><span>Finalizar</span></button>
                    </div>
                </div>
            </div>

            <!-- Columna 2: Panel del Formulario Activo (col-lg-5) -->
            <div class="col-lg-5">
                <form action="crear_curso.php" method="POST" enctype="multipart/form-data" id="courseWizardForm">
                    <div class="wizard-shell rounded-4 border bg-white shadow-sm p-4 mb-4">

                            <div class="wizard-step-pane active" data-step-pane="1">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Paso 1 · Información general</h6>
                                    <div class="row g-3">
                                        <div class="col-md-8"><label for="titulo" class="form-label fw-bold small">Nombre del curso</label><input type="text" name="titulo" id="titulo" class="form-control form-control-premium" placeholder="Ej. Diseño UI con Figma y Bootstrap" required></div>
                                        <div class="col-md-4"><label for="codigo" class="form-label fw-bold small">Código</label><input type="text" name="codigo" id="codigo" class="form-control form-control-premium" placeholder="Ej. UI-101" required></div>
                                        <div class="col-md-6"><label for="descripcion_corta" class="form-label fw-bold small">Descripción corta</label><input type="text" name="descripcion_corta" id="descripcion_corta" class="form-control form-control-premium" placeholder="Resumen breve para tarjetas" maxlength="255"></div>
                                        <div class="col-md-6"><label for="categoria" class="form-label fw-bold small">Categoría</label><input type="text" name="categoria" id="categoria" class="form-control form-control-premium" placeholder="Diseño, Programación, Negocios"></div>
                                        <div class="col-md-6"><label for="categoria_id" class="form-label fw-bold small">Subcategoría / sistema</label><select name="categoria_id" id="categoria_id" class="form-select form-control-premium"><option value="">Sin categoría</option><?php foreach ($categorias as $categoriaItem): ?><option value="<?= (int) $categoriaItem['id'] ?>"><?= htmlspecialchars($categoriaItem['nombre']) ?></option><?php endforeach; ?></select></div>
                                        <div class="col-md-6"><label for="idioma" class="form-label fw-bold small">Idioma</label><input type="text" name="idioma" id="idioma" class="form-control form-control-premium" value="Español"></div>
                                        <div class="col-md-6"><label for="nivel" class="form-label fw-bold small">Nivel</label><select name="nivel" id="nivel" class="form-select form-control-premium"><option value="Básico">Básico</option><option value="Intermedio">Intermedio</option><option value="Avanzado">Avanzado</option></select></div>
                                        <div class="col-md-6"><label for="modalidad" class="form-label fw-bold small">Modalidad</label><select name="modalidad" id="modalidad" class="form-select form-control-premium"><option value="Online">Online</option><option value="Híbrido">Híbrido</option><option value="Presencial">Presencial</option></select></div>
                                        <div class="col-md-6"><label for="duracion" class="form-label fw-bold small">Duración</label><input type="text" name="duracion" id="duracion" class="form-control form-control-premium" placeholder="Ej. 6 semanas"></div>
                                        <div class="col-md-6"><label for="horas" class="form-label fw-bold small">Horas estimadas</label><input type="number" name="horas" id="horas" class="form-control form-control-premium" min="0" value="0"></div>
                                        <div class="col-12"><label for="descripcion" class="form-label fw-bold small">Descripción completa</label><div id="editor" style="height: 220px;"></div><textarea name="descripcion" id="descripcion" rows="4" class="form-control form-control-premium d-none" required></textarea></div>
                                        <div class="col-md-6"><label for="instructor" class="form-label fw-bold small">Instructor principal</label><input type="text" name="instructor" id="instructor" class="form-control form-control-premium" placeholder="Ej. Sofía Ortega"></div>
                                        <div class="col-md-6"><label for="etiquetas" class="form-label fw-bold small">Etiquetas</label><input type="text" name="etiquetas" id="etiquetas" class="form-control form-control-premium" placeholder="ui, figma, bootstrap"></div>
                                        <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="gratuito" id="gratuito" value="1" checked><label class="form-check-label small fw-bold" for="gratuito">Curso gratuito</label></div></div>
                                        <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="certificado" id="certificado" value="1" checked><label class="form-check-label small fw-bold" for="certificado">Certificado</label></div></div>
                                        <div class="col-md-4"><label for="precio" class="form-label fw-bold small">Precio</label><input type="number" step="0.01" name="precio" id="precio" class="form-control form-control-premium" value="0" min="0"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="2">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-mortarboard me-2 text-primary"></i>Paso 2 · Configuración académica</h6><div class="row g-3"><div class="col-md-6"><label for="horas_semanales" class="form-label fw-bold small">Horas semanales</label><input type="number" name="horas_semanales" id="horas_semanales" class="form-control form-control-premium" min="0" value="4"></div><div class="col-md-6"><label for="fecha_inicio" class="form-label fw-bold small">Fecha de inicio</label><input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-premium"></div><div class="col-md-6"><label for="fecha_fin" class="form-label fw-bold small">Fecha de finalización</label><input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-premium"></div><div class="col-md-6"><label for="capacidad" class="form-label fw-bold small">Número máximo de estudiantes</label><input type="number" name="capacidad" id="capacidad" class="form-control form-control-premium" min="1" value="30"></div><div class="col-md-6"><label for="requisitos_minimos" class="form-label fw-bold small">Requisitos mínimos</label><textarea name="requisitos_minimos" id="requisitos_minimos" rows="3" class="form-control form-control-premium" placeholder="Conocimientos previos, hardware, software"></textarea></div><div class="col-md-6"><label for="edad_minima" class="form-label fw-bold small">Edad mínima</label><input type="number" name="edad_minima" id="edad_minima" class="form-control form-control-premium" min="0" value="16"></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="inscripcion_automatica" id="inscripcion_automatica" value="1" checked><label class="form-check-label small fw-bold" for="inscripcion_automatica">Inscripción automática</label></div><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="certificacion_disponible" id="certificacion_disponible" value="1" checked><label class="form-check-label small fw-bold" for="certificacion_disponible">Certificación disponible</label></div></div></div></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="3">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-image me-2 text-primary"></i>Paso 3 · Portada</h6><div class="row g-4 align-items-start"><div class="col-lg-7"><label for="imagen_file" class="form-label fw-bold small">Subir imagen de portada</label><input type="file" name="imagen_file" id="imagen_file" class="form-control form-control-premium" accept="image/*"><div class="form-text small text-muted">JPG, PNG, WEBP o GIF.</div><div class="mt-3"><label for="video_intro_url" class="form-label fw-bold small">Video de presentación</label><input type="url" name="video_intro_url" id="video_intro_url" class="form-control form-control-premium" placeholder="https://youtube.com/watch?v=..."></div><div class="row g-3 mt-1"><div class="col-md-6"><label for="color_principal" class="form-label fw-bold small">Color principal</label><input type="color" name="color_principal" id="color_principal" class="form-control form-control-premium form-control-color" value="#1a73e8"></div><div class="col-md-6"><label for="color_secundario" class="form-label fw-bold small">Color secundario</label><input type="color" name="color_secundario" id="color_secundario" class="form-control form-control-premium form-control-color" value="#2e7d32"></div></div></div><div class="col-lg-5"><div class="bg-white border rounded-4 p-3"><div class="small text-muted fw-bold mb-2">Vista previa</div><img id="cover_preview" src="" alt="Vista previa de portada" class="img-fluid rounded-3" style="display:none; max-height: 260px; object-fit: cover; width:100%;"><div class="mt-3 p-3 bg-light rounded-3"><div class="fw-bold">Portada del curso</div><div class="small text-muted">Se verá en el catálogo y en la vista previa del estudiante.</div></div></div></div></div></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="4">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-bullseye me-2 text-primary"></i>Paso 4 · Objetivos</h6><div id="objetivos_container"><div class="dynamic-entry-row border rounded-3 p-3 mb-2 bg-white"><div class="row g-3 align-items-end"><div class="col-md-4"><label class="form-label fw-bold small">Título</label><input type="text" name="objetivos_titulo[]" class="form-control form-control-premium" placeholder="Ej. Comprender los fundamentos"></div><div class="col-md-6"><label class="form-label fw-bold small">Descripción</label><textarea name="objetivos[]" rows="2" class="form-control form-control-premium" placeholder="Describe el objetivo de aprendizaje"></textarea></div><div class="col-md-2"><label class="form-label fw-bold small">Orden</label><input type="number" name="objetivos_orden[]" class="form-control form-control-premium" value="1" min="1"></div></div><button type="button" class="btn btn-outline-secondary btn-sm mt-3" onclick="removeDynamicEntry(this, 'objetivos_container')">Eliminar</button></div></div><button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add_objetivo_btn">Agregar objetivo</button></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="5">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-award me-2 text-primary"></i>Paso 5 · Competencias</h6><div id="competencias_container"><div class="dynamic-entry-row border rounded-3 p-3 mb-2 bg-white"><div class="row g-3"><div class="col-md-6"><label class="form-label fw-bold small">Competencia</label><input type="text" name="competencias[]" class="form-control form-control-premium" placeholder="Ej. Diseñar interfaces accesibles"></div><div class="col-md-3"><label class="form-label fw-bold small">Nivel</label><select name="competencias_nivel[]" class="form-select form-control-premium"><option value="Básico">Básico</option><option value="Intermedio">Intermedio</option><option value="Avanzado">Avanzado</option></select></div><div class="col-md-3"><label class="form-label fw-bold small">Indicadores</label><input type="text" name="competencias_indicadores[]" class="form-control form-control-premium" placeholder="Ej. Usar contraste"></div></div><button type="button" class="btn btn-outline-secondary btn-sm mt-3" onclick="removeDynamicEntry(this, 'competencias_container')">Eliminar</button></div></div><button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add_competencia_btn">Agregar competencia</button></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="6">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-tools me-2 text-primary"></i>Paso 6 · Requisitos</h6><div id="requisitos_container"><div class="dynamic-entry-row border rounded-3 p-3 mb-2 bg-white"><label class="form-label fw-bold small">Detalle del requisito</label><textarea name="requisitos[]" rows="2" class="form-control form-control-premium" placeholder="Software, conocimiento previo, equipo necesario"></textarea><button type="button" class="btn btn-outline-secondary btn-sm mt-3" onclick="removeDynamicEntry(this, 'requisitos_container')">Eliminar</button></div></div><button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add_requisito_btn">Agregar requisito</button></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="7">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-diagram-3 me-2 text-primary"></i>Paso 7 · Contenido del curso</h6><div class="row g-4"><div class="col-lg-8"><div class="border rounded-4 p-3 bg-white"><div class="small text-muted fw-bold mb-3">Estructura del curso</div><div class="course-tree"><div class="tree-node draggable" draggable="true"><i class="bi bi-journal-bookmark me-2"></i>Curso principal</div><div class="tree-node child" draggable="true"><i class="bi bi-stack me-2"></i>Módulo 1 · Fundamentos</div><div class="tree-node child child-2" draggable="true"><i class="bi bi-folder2-open me-2"></i>Unidad 1 · Introducción</div><div class="tree-node child child-3" draggable="true"><i class="bi bi-play-circle me-2"></i>Lección 1 · Conceptos básicos</div></div></div></div><div class="col-lg-4"><div class="border rounded-4 p-3 bg-white"><label class="form-label fw-bold small">Nombre del módulo</label><input type="text" name="modulo_nombre" class="form-control form-control-premium" placeholder="Ej. Módulo 1"><label class="form-label fw-bold small mt-3">Descripción</label><textarea name="modulo_descripcion" rows="3" class="form-control form-control-premium" placeholder="Describe el contenido del módulo"></textarea><label class="form-label fw-bold small mt-3">Tiempo estimado</label><input type="text" name="modulo_tiempo" class="form-control form-control-premium" placeholder="Ej. 2 semanas"><div class="mt-3"><button type="button" class="btn btn-outline-primary btn-sm">Agregar módulo</button></div></div></div></div></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="8">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-folder2-open me-2 text-primary"></i>Paso 8 · Recursos</h6><div id="recursos_container"><div class="dynamic-entry-row border rounded-3 p-3 mb-2 bg-white"><label class="form-label fw-bold small">Recurso</label><textarea name="recursos[]" rows="2" class="form-control form-control-premium" placeholder="PDF, video, enlace, plantilla, guía"></textarea><button type="button" class="btn btn-outline-secondary btn-sm mt-3" onclick="removeDynamicEntry(this, 'recursos_container')">Eliminar</button></div></div><button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add_recurso_btn">Agregar recurso</button></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="9">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-activity me-2 text-primary"></i>Paso 9 · Actividades</h6><div id="actividades_container"><div class="dynamic-entry-row border rounded-3 p-3 mb-2 bg-white"><div class="row g-3"><div class="col-md-6"><label class="form-label fw-bold small">Tipo de actividad</label><select name="actividades_tipo[]" class="form-select form-control-premium"><option value="Foro">Foro</option><option value="Tarea">Tarea</option><option value="Proyecto">Proyecto</option><option value="Debate">Debate</option></select></div><div class="col-md-6"><label class="form-label fw-bold small">Fecha</label><input type="date" name="actividades_fecha[]" class="form-control form-control-premium"></div></div><label class="form-label fw-bold small mt-3">Detalles</label><textarea name="actividades_detalle[]" rows="2" class="form-control form-control-premium" placeholder="Instrucciones, criterios, archivos"></textarea><button type="button" class="btn btn-outline-secondary btn-sm mt-3" onclick="removeDynamicEntry(this, 'actividades_container')">Eliminar</button></div></div><button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add_actividad_btn">Agregar actividad</button></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="10">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-journal-check me-2 text-primary"></i>Paso 10 · Evaluaciones</h6><div id="evaluaciones_container"><div class="dynamic-entry-row border rounded-3 p-3 mb-2 bg-white"><div class="row g-3"><div class="col-md-6"><label class="form-label fw-bold small">Nombre de la evaluación</label><input type="text" name="evaluaciones_nombre[]" class="form-control form-control-premium" placeholder="Ej. Examen final"></div><div class="col-md-6"><label class="form-label fw-bold small">Tiempo límite</label><input type="text" name="evaluaciones_tiempo[]" class="form-control form-control-premium" placeholder="45 minutos"></div></div><label class="form-label fw-bold small mt-3">Descripción</label><textarea name="evaluaciones_descripcion[]" rows="2" class="form-control form-control-premium" placeholder="Preguntas, tipo, retroalimentación"></textarea><button type="button" class="btn btn-outline-secondary btn-sm mt-3" onclick="removeDynamicEntry(this, 'evaluaciones_container')">Eliminar</button></div></div><button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add_evaluacion_btn">Agregar evaluación</button></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="11">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-patch-check me-2 text-primary"></i>Paso 11 · Certificado</h6><div class="row g-3"><div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="certificado_activo" id="certificado_activo" value="1" checked><label class="form-check-label small fw-bold" for="certificado_activo">Activar certificado</label></div></div><div class="col-md-6"><label class="form-label fw-bold small">Texto personalizado</label><input type="text" name="certificado_texto" class="form-control form-control-premium" placeholder="Certificado de finalización"></div><div class="col-md-6"><label class="form-label fw-bold small">Instructor firmante</label><input type="text" name="certificado_instructor" class="form-control form-control-premium" placeholder="Nombre del profesor"></div><div class="col-md-6"><label class="form-label fw-bold small">Plantilla</label><select name="certificado_plantilla" class="form-select form-control-premium"><option value="estandar">Estándar</option><option value="premium">Premium</option></select></div></div></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="12">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-sliders me-2 text-primary"></i>Paso 12 · Configuraciones</h6><div class="row g-3"><div class="col-md-6"><label class="form-label fw-bold small">Visibilidad</label><select name="visibilidad" class="form-select form-control-premium"><option value="publico">Público</option><option value="privado">Privado</option><option value="programado">Programado</option></select></div><div class="col-md-6"><label class="form-label fw-bold small">URL personalizada</label><input type="text" name="url_personalizada" class="form-control form-control-premium" placeholder="curso-ux-basico"></div><div class="col-12"><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="foros" id="foros" value="1" checked><label class="form-check-label small fw-bold" for="foros">Foros</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="comentarios" id="comentarios" value="1" checked><label class="form-check-label small fw-bold" for="comentarios">Comentarios</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="descargas" id="descargas" value="1" checked><label class="form-check-label small fw-bold" for="descargas">Descargas</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="notificaciones" id="notificaciones" value="1" checked><label class="form-check-label small fw-bold" for="notificaciones">Notificaciones</label></div></div></div></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="13">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-eye me-2 text-primary"></i>Paso 13 · Vista previa</h6><div class="row g-4 align-items-start"><div class="col-lg-7"><div class="border rounded-4 p-3 bg-white"><div class="small text-muted fw-bold mb-2">Vista del estudiante</div><div id="wizardPreviewBody"></div></div></div><div class="col-lg-5"><div class="border rounded-4 p-3 bg-white"><div class="small text-muted fw-bold mb-2">Progreso estimado</div><div class="progress mb-3" style="height: 10px;"><div class="progress-bar bg-primary" style="width: 72%"></div></div><div class="small text-muted">Se encuentra listo para publicar tras revisar los pasos anteriores.</div></div></div></div></div>
                            </div>

                            <div class="wizard-step-pane" data-step-pane="14">
                                <div class="card border-0 rounded-4 bg-light p-4 mb-4"><h6 class="fw-bold text-dark mb-3"><i class="bi bi-rocket-takeoff me-2 text-primary"></i>Paso 14 · Publicación</h6><div class="border rounded-3 p-3 bg-white"><div class="fw-bold mb-2">Checklist de publicación</div><div class="form-check"><input class="form-check-input" type="checkbox" checked><label class="form-check-label small">Título</label></div><div class="form-check"><input class="form-check-input" type="checkbox" checked><label class="form-check-label small">Contenido</label></div><div class="form-check"><input class="form-check-input" type="checkbox" checked><label class="form-check-label small">Objetivos</label></div><div class="form-check"><input class="form-check-input" type="checkbox" checked><label class="form-check-label small">Imagen</label></div><div class="form-check"><input class="form-check-input" type="checkbox" checked><label class="form-check-label small">Evaluaciones</label></div><div class="form-check"><input class="form-check-input" type="checkbox" checked><label class="form-check-label small">Recursos y certificado</label></div></div></div>
                            </div>

                            <!-- Botones de navegación dentro del step activo -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border-top pt-3 mt-3">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="prevStepBtn">
                                        <i class="bi bi-arrow-left me-1"></i> Anterior
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 text-white" id="nextStepBtn">
                                        Siguiente <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <!-- Hidden submit buttons triggered by top bar -->
                                    <button type="submit" name="estado" value="0" class="d-none" id="submitBorradorHidden"></button>
                                    <button type="submit" name="estado" value="1" class="d-none" id="submitPublicarHidden"></button>
                                    <button type="submit" name="estado" value="0" class="btn btn-light btn-sm border px-3 rounded-pill">
                                        <i class="bi bi-floppy me-1"></i> Borrador
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill" onclick="showWizardStep(13)">
                                        <i class="bi bi-eye me-1"></i> Vista previa
                                    </button>
                                    <button type="submit" name="estado" value="1" class="btn btn-success btn-sm px-3 rounded-pill text-white fw-semibold">
                                        <i class="bi bi-rocket-takeoff me-1"></i> Publicar
                                    </button>
                                </div>
                            </div>
                        </div><!-- /wizard-shell -->
                    </form><!-- /courseWizardForm -->
                </div><!-- /col-lg-5 -->

                <div class="col-lg-4">
                    <div class="edit-sidebar-sticky d-grid gap-3">
                        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                            <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.06em; font-size:.7rem;">
                                <i class="bi bi-eye me-1 text-primary"></i>Vista previa inmediata
                            </div>
                            <h5 class="fw-bold mb-3" id="livePreviewTitle">Título del curso</h5>
                            <div class="live-preview-cover rounded-3 overflow-hidden mb-3">
                                <img id="livePreviewCover" src="" alt="Vista previa del curso" class="img-fluid w-100" style="display:none; object-fit:cover; height:180px;">
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3" id="livePreviewMeta"></div>
                            <p class="text-muted small mb-3" id="livePreviewShort">Describe brevemente lo que aprenderás y por qué este curso es valioso.</p>
                            <div class="small text-dark" id="livePreviewDescription">Tu descripción aparecerá aquí mientras editas.</div>
                            <div class="mt-3 d-flex flex-wrap gap-2" id="livePreviewTags"></div>
                            <hr class="my-3">
                            <div class="small text-uppercase fw-bold text-muted mb-2" style="letter-spacing:.06em; font-size:.68rem;">Estructura del curso</div>
                            <div id="livePreviewStructure" class="small text-muted"></div>
                        </div>

                        <div class="card border-0 rounded-4 shadow-sm p-4 builder-help-card" id="contextualHelpPanel">
                            <div class="small text-uppercase fw-bold text-primary mb-3" style="letter-spacing:.06em; font-size:.68rem;">
                                <i class="bi bi-lightbulb me-1"></i>Ayuda contextual
                            </div>
                            <div id="contextualHelpContent" class="d-grid gap-2"></div>
                        </div>

                        <div class="card border-0 rounded-4 shadow-sm p-4 builder-ghost-card">
                            <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.06em; font-size:.7rem;">Acciones rápidas</div>
                            <div class="d-grid gap-2">
                                <a href="index.php?tab=cursos" class="btn btn-light btn-sm text-start px-3">
                                    <i class="bi bi-grid me-2 text-muted"></i>Ver todos los cursos
                                </a>
                                <a href="index.php?tab=dashboard" class="btn btn-light btn-sm text-start px-3">
                                    <i class="bi bi-speedometer2 me-2 text-primary"></i>Ir al panel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /row -->
        </div><!-- /container-fluid -->

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        const imgInput = document.getElementById('imagen_file');
        const imgPreview = document.getElementById('cover_preview');
        const livePreviewCover = document.getElementById('livePreviewCover');
        const livePreviewTitle = document.getElementById('livePreviewTitle');
        const livePreviewShort = document.getElementById('livePreviewShort');
        const livePreviewDescription = document.getElementById('livePreviewDescription');
        const livePreviewMeta = document.getElementById('livePreviewMeta');
        const livePreviewTags = document.getElementById('livePreviewTags');
        const livePreviewStructure = document.getElementById('livePreviewStructure');
        const wizardPreviewBody = document.getElementById('wizardPreviewBody');
        const addModuleBtn = document.getElementById('add_module_btn');
        const modulesContainer = document.getElementById('modulos_container');
        const addObjetivoBtn = document.getElementById('add_objetivo_btn');
        const objetivosContainer = document.getElementById('objetivos_container');
        const addSeoBtn = document.getElementById('add_seo_btn');
        const seoContainer = document.getElementById('seo_container');
        const stepPills = document.querySelectorAll('.wizard-step-pill');
        const stepPanes = document.querySelectorAll('.wizard-step-pane');
        const progressBar = document.getElementById('wizardProgressBar');
        const prevStepBtn = document.getElementById('prevStepBtn');
        const nextStepBtn = document.getElementById('nextStepBtn');
        let currentWizardStep = 1;

        function buildPreviewStructure() {
            const modules = Array.from(document.querySelectorAll('.module-row input[name="modulo_nombre"]'))
                .map(function (input) { return input.value.trim(); })
                .filter(Boolean);
            const resources = Array.from(document.querySelectorAll('textarea[name="recursos[]"]'))
                .map(function (field) { return field.value.trim(); })
                .filter(Boolean);
            const activities = Array.from(document.querySelectorAll('select[name="actividades_tipo[]"]'))
                .map(function (select, index) {
                    const detail = document.querySelectorAll('textarea[name="actividades_detalle[]"]')[index]?.value?.trim() || '';
                    const date = document.querySelectorAll('input[name="actividades_fecha[]"]')[index]?.value || '';
                    const label = select.value || 'Actividad';
                    return detail ? label + (date ? ' · ' + date : '') + ' · ' + detail : label + (date ? ' · ' + date : '');
                })
                .filter(Boolean);
            const evaluations = Array.from(document.querySelectorAll('input[name="evaluaciones_nombre[]"]'))
                .map(function (input, index) {
                    const desc = document.querySelectorAll('textarea[name="evaluaciones_descripcion[]"]')[index]?.value?.trim() || '';
                    const time = document.querySelectorAll('input[name="evaluaciones_tiempo[]"]')[index]?.value?.trim() || '';
                    return [input.value.trim(), time, desc].filter(Boolean).join(' · ');
                })
                .filter(Boolean);
            const certificateEnabled = document.getElementById('certificado_activo')?.checked;
            const visibility = document.querySelector('select[name="visibilidad"]')?.value || 'Público';
            const sections = [];

            sections.push('<div class="mb-3"><div class="fw-semibold text-dark">Estructura profesional</div><ul class="ps-3 mb-0 small text-muted">');
            if (modules.length) {
                sections.push('<li><span class="fw-semibold text-dark">Módulos:</span> ' + escapeHtml(modules.slice(0, 4).join(', ')) + (modules.length > 4 ? '…' : '') + '</li>');
            } else {
                sections.push('<li><span class="fw-semibold text-dark">Módulos:</span> Tu contenido se organizará por unidades y lecciones.</li>');
            }
            if (resources.length) {
                sections.push('<li><span class="fw-semibold text-dark">Recursos:</span> ' + escapeHtml(resources.slice(0, 3).join(' • ')) + '</li>');
            } else {
                sections.push('<li><span class="fw-semibold text-dark">Recursos:</span> Se agregará material de apoyo y documentos.</li>');
            }
            if (activities.length) {
                sections.push('<li><span class="fw-semibold text-dark">Actividades:</span> ' + escapeHtml(activities.slice(0, 2).join(' • ')) + '</li>');
            } else {
                sections.push('<li><span class="fw-semibold text-dark">Actividades:</span> Se podrán incluir foros, tareas o proyectos.</li>');
            }
            if (evaluations.length) {
                sections.push('<li><span class="fw-semibold text-dark">Evaluaciones:</span> ' + escapeHtml(evaluations.slice(0, 2).join(' • ')) + '</li>');
            } else {
                sections.push('<li><span class="fw-semibold text-dark">Evaluaciones:</span> Se definirán pruebas o entregables al final del curso.</li>');
            }
            sections.push('<li><span class="fw-semibold text-dark">Certificado:</span> ' + (certificateEnabled ? 'Activado' : 'Sin certificado') + '</li>');
            sections.push('<li><span class="fw-semibold text-dark">Visibilidad:</span> ' + escapeHtml(visibility) + '</li>');
            sections.push('</ul></div>');

            const html = sections.join('');
            if (livePreviewStructure) {
                livePreviewStructure.innerHTML = html;
            }
            if (wizardPreviewBody) {
                wizardPreviewBody.innerHTML = '<h4 class="fw-bold mb-3">' + escapeHtml(document.getElementById('titulo')?.value?.trim() || 'Título del curso') + '</h4><p class="text-muted mb-3">' + escapeHtml(document.getElementById('descripcion_corta')?.value?.trim() || 'Describe brevemente lo que aprenderás y por qué este curso es valioso.') + '</p><div class="mb-3">' + livePreviewMeta?.innerHTML + '</div>' + html;
            }
        }

        function updateLivePreview() {
            const title = document.getElementById('titulo')?.value?.trim() || 'Título del curso';
            const short = document.getElementById('descripcion_corta')?.value?.trim() || 'Describe brevemente lo que aprenderás y por qué este curso es valioso.';
            const level = document.getElementById('nivel')?.value || 'Básico';
            const duration = document.getElementById('duracion')?.value?.trim() || 'Duración flexible';
            const modality = document.getElementById('modalidad')?.value || 'Online';
            const tags = document.getElementById('etiquetas')?.value?.trim() || '';
            const descriptionText = quill ? quill.getText().trim() : '';

            if (livePreviewTitle) {
                livePreviewTitle.textContent = title;
            }
            if (livePreviewShort) {
                livePreviewShort.textContent = short;
            }
            if (livePreviewDescription) {
                livePreviewDescription.textContent = descriptionText || 'Tu descripción aparecerá aquí mientras editas.';
            }
            if (livePreviewMeta) {
                livePreviewMeta.innerHTML = '<span class="badge rounded-pill bg-primary-subtle text-primary">' + escapeHtml(level) + '</span><span class="badge rounded-pill bg-light text-dark">' + escapeHtml(duration) + '</span><span class="badge rounded-pill bg-success-subtle text-success">' + escapeHtml(modality) + '</span>';
            }
            if (livePreviewTags) {
                if (tags) {
                    const chips = tags.split(',').map(function (item) { return item.trim(); }).filter(Boolean).map(function (item) { return '<span class="badge rounded-pill bg-light text-muted">' + escapeHtml(item) + '</span>'; }).join('');
                    livePreviewTags.innerHTML = chips;
                } else {
                    livePreviewTags.innerHTML = '<span class="badge rounded-pill bg-light text-muted">Sin etiquetas</span>';
                }
            }
            buildPreviewStructure();
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function handlePreview() {
            if (imgInput && imgInput.files && imgInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (imgPreview) {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                    }
                    if (livePreviewCover) {
                        livePreviewCover.src = e.target.result;
                        livePreviewCover.style.display = 'block';
                    }
                };
                reader.readAsDataURL(imgInput.files[0]);
            } else {
                if (imgPreview) {
                    imgPreview.style.display = 'none';
                }
                if (livePreviewCover) {
                    livePreviewCover.style.display = 'none';
                }
            }
        }

        if (imgInput) {
            imgInput.addEventListener('change', function () {
                handlePreview();
                updateLivePreview();
            });
        }

        // ─── WIZARD NAVIGATION ───────────────────────────────────────────────────
        const TOTAL_STEPS = 15;
        const stepTitles = [
            '', // index 0 unused
            'Paso 1 de 15: Información general',
            'Paso 2 de 15: Portada del curso',
            'Paso 3 de 15: Objetivos de aprendizaje',
            'Paso 4 de 15: Competencias',
            'Paso 5 de 15: Requisitos previos',
            'Paso 6 de 15: Estructura académica',
            'Paso 7 de 15: Contenido del curso',
            'Paso 8 de 15: Recursos',
            'Paso 9 de 15: Actividades',
            'Paso 10 de 15: Evaluaciones',
            'Paso 11 de 15: Certificado',
            'Paso 12 de 15: Configuración y acceso',
            'Paso 13 de 15: SEO y publicación',
            'Paso 14 de 15: Vista previa del estudiante',
            'Paso 15 de 15: Finalizar y publicar'
        ];

        const helpContentMap = {
            1: ['Define una propuesta clara y específica.', 'Usa un título comprensible y una descripción breve con valor.', 'Apoya la confianza del profesor con categoría, duración y nivel.'],
            2: ['Sube una portada visual que represente el curso.', 'Prioriza imágenes 16:9 y una estética consistente.', 'El banner y la miniatura deben reforzar la marca.'],
            3: ['Escribe objetivos accionables y medibles.', 'Cada objetivo debe responder: ¿qué aprenderá el estudiante?', 'Usa verbos de acción como diseñar, aplicar, analizar o crear.'],
            4: ['Relaciona cada competencia con resultados concretos.', 'Incluye indicadores que permitan evidenciar aprendizaje.', 'Haz que las competencias sean observables y progresivas.'],
            5: ['Indica herramientas, conocimientos previos o recursos necesarios.', 'Esto reduce fricción y mejora el onboarding.', 'Mantén la lista simple y útil.'],
            6: ['Organiza el curso en módulos, unidades y lecciones.', 'La progresión debe facilitar el aprendizaje continuo.', 'Haz la estructura clara para estudiantes y profesores.'],
            7: ['Combina teoría, evidencia visual y práctica.', 'Incluye recursos que apoyen cada etapa del aprendizaje.', 'Un contenido bien segmentado mejora la retención.'],
            8: ['Agrupa recursos por tipo y propósito.', 'Incluye documentos, enlaces y plantillas útiles.', 'Una biblioteca bien ordenada acelera el uso.'],
            9: ['Diseña actividades que generen participación.', 'Foros, debates y tareas aumentan la interacción.', 'Asegura que cada actividad tenga un propósito claro.'],
            10: ['Usa evaluaciones alineadas con los objetivos del curso.', 'Define tiempos, criterios de retroalimentación y expectativas.', 'Un ecosistema de evaluación bien diseñado mejora la percepción del programa.'],
            11: ['El certificado refuerza el valor del curso.', 'Incluye nombre del estudiante, fecha y firma.', 'La vista previa permite comprobar la experiencia final.'],
            12: ['Establece visibilidad, accesos, fechas y permisos.', 'Una configuración clara reduce dudas de matrícula y seguimiento.', 'Ajusta la privacidad según el tipo de audiencia.'],
            13: ['Define una URL personalizada y metadatos claros.', 'Meta title y description mejoran la búsqueda y el CTR.', 'Agrega palabras clave relevantes y coherentes.'],
            14: ['Revisa cómo verá el estudiante la propuesta completa.', 'Valida portada, módulos y botón de inscripción.', 'La experiencia del alumno debe ser fluida y elegante.'],
            15: ['Completa el checklist final antes de publicar.', 'Cada bloque debe estar consistente con el resto del curso.', 'Si todo está listo, publica con confianza.']
        };

        function updateContextualHelp(step) {
            const content = document.getElementById('contextualHelpContent');
            if (!content) return;
            const items = (helpContentMap[step] || helpContentMap[1] || []).map(function (item) {
                return '<div class="d-flex gap-2 align-items-start help-bullet"><i class="bi bi-stars text-primary mt-1"></i><span>' + escapeHtml(item) + '</span></div>';
            }).join('');
            content.innerHTML = items;
        }

        function getDraftSnapshot() {
            const form = document.getElementById('courseWizardForm');
            const data = {};
            if (!form) return data;
            const formData = new FormData(form);
            formData.forEach(function (value, key) {
                if (key === 'descripcion') {
                    data[key] = value;
                    return;
                }
                if (typeof value === 'string') {
                    data[key] = value;
                }
            });
            data.currentStep = currentWizardStep;
            data.lastSavedAt = new Date().toISOString();
            return data;
        }

        function saveDraft(force) {
            const draft = getDraftSnapshot();
            const payload = JSON.stringify(draft);
            try {
                localStorage.setItem('northstarCourseBuilderDraft', payload);
                const history = JSON.parse(localStorage.getItem('northstarCourseBuilderHistory') || '[]');
                history.unshift({ timestamp: draft.lastSavedAt, data: draft });
                localStorage.setItem('northstarCourseBuilderHistory', JSON.stringify(history.slice(0, 5)));
                const autosaveLabel = document.getElementById('autosaveStatus');
                const lastSavedLabel = document.getElementById('lastSavedLabel');
                if (autosaveLabel) {
                    autosaveLabel.innerHTML = '<i class="bi bi-cloud-check me-1"></i>Guardado ' + (force ? 'ahora' : 'automático');
                }
                if (lastSavedLabel) {
                    lastSavedLabel.textContent = 'Última modificación: ' + new Date(draft.lastSavedAt).toLocaleString('es-ES', { hour: '2-digit', minute: '2-digit' });
                }
            } catch (e) {
                console.warn('No se pudo guardar el borrador localmente', e);
            }
        }

        function restoreDraft() {
            try {
                const raw = localStorage.getItem('northstarCourseBuilderDraft');
                if (!raw) return;
                const draft = JSON.parse(raw);
                const form = document.getElementById('courseWizardForm');
                if (!form) return;
                Object.entries(draft).forEach(function ([key, value]) {
                    if (key === 'currentStep' || key === 'lastSavedAt' || key === 'descripcion') return;
                    const field = form.querySelector('[name="' + key + '"]');
                    if (!field) return;
                    if (field.type === 'checkbox') {
                        field.checked = Boolean(value);
                    } else if (field.type === 'radio') {
                        field.checked = field.value === String(value);
                    } else {
                        field.value = value;
                    }
                });
                const titleField = document.getElementById('titulo');
                const headerField = document.getElementById('courseTitleHeader');
                if (titleField && headerField) {
                    headerField.value = titleField.value || headerField.value;
                    titleField.addEventListener('input', function () {
                        headerField.value = this.value;
                    });
                }
                if (draft.descripcion) {
                    const editorContent = draft.descripcion;
                    if (typeof quill !== 'undefined') {
                        quill.root.innerHTML = editorContent;
                        document.getElementById('descripcion').value = editorContent;
                    }
                }
                if (draft.currentStep) {
                    showWizardStep(Number(draft.currentStep));
                }
                updateLivePreview();
                saveDraft(true);
            } catch (e) {
                console.warn('No se pudo restaurar el borrador localmente', e);
            }
        }

        function showWizardStep(step) {
            currentWizardStep = step;

            stepPanes.forEach(function (pane) {
                pane.classList.toggle('active', pane.getAttribute('data-step-pane') == step);
            });

            document.querySelectorAll('.wizard-step-pill').forEach(function (pill) {
                const isActive = pill.getAttribute('data-step') == step;
                const isCompleted = Number(pill.getAttribute('data-step')) < step;
                pill.classList.toggle('active', isActive);
                pill.classList.toggle('completed', isCompleted);
                pill.classList.toggle('locked', Number(pill.getAttribute('data-step')) > step);
                if (isActive) {
                    pill.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });

            const progress = Math.round((step / TOTAL_STEPS) * 100);
            document.querySelectorAll('#wizardProgressBar').forEach(function (bar) {
                bar.style.width = progress + '%';
            });
            const progressLabel = document.getElementById('wizardProgressLabel');
            if (progressLabel) {
                progressLabel.textContent = progress + '%';
            }

            const titleEl = document.getElementById('wizardActiveStepTitle');
            if (titleEl) titleEl.textContent = stepTitles[step] || 'Paso ' + step + ' de ' + TOTAL_STEPS;

            document.querySelectorAll('#prevStepBtn, #prevStepBtnTop').forEach(function (btn) {
                btn.disabled = step === 1;
                btn.classList.toggle('opacity-50', step === 1);
            });

            document.querySelectorAll('#nextStepBtn, #nextStepBtnTop').forEach(function (btn) {
                btn.disabled = step === TOTAL_STEPS;
                btn.classList.toggle('opacity-50', step === TOTAL_STEPS);
            });

            updateContextualHelp(step);
            saveDraft(false);

            const wizardShell = document.querySelector('.wizard-shell');
            if (wizardShell) {
                wizardShell.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // ─── Wire all step pills (sidebar)
        document.querySelectorAll('.wizard-step-pill').forEach(function (pill) {
            pill.addEventListener('click', function () {
                showWizardStep(parseInt(this.getAttribute('data-step'), 10));
            });
        });

        // ─── Wire PREV buttons (inside step + top bar)
        document.querySelectorAll('#prevStepBtn, #prevStepBtnTop').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showWizardStep(Math.max(1, currentWizardStep - 1));
            });
        });

        // ─── Wire NEXT buttons (inside step + top bar)
        document.querySelectorAll('#nextStepBtn, #nextStepBtnTop').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showWizardStep(Math.min(TOTAL_STEPS, currentWizardStep + 1));
            });
        });

        // ─── Wire top bar SAVE DRAFT button → trigger hidden submit
        const saveBorradorBtnTop = document.getElementById('saveBorradorBtnTop');
        if (saveBorradorBtnTop) {
            saveBorradorBtnTop.addEventListener('click', function () {
                const hidden = document.getElementById('submitBorradorHidden');
                if (hidden) hidden.click();
            });
        }

        // ─── Wire top bar PUBLISH button → trigger hidden submit
        const publishBtnTop = document.getElementById('publishBtnTop');
        if (publishBtnTop) {
            publishBtnTop.addEventListener('click', function () {
                const hidden = document.getElementById('submitPublicarHidden');
                if (hidden) hidden.click();
            });
        }

        if (addModuleBtn && modulesContainer) {
            addModuleBtn.addEventListener('click', function () {
                const firstRow = modulesContainer.querySelector('.module-row');
                const newRow = firstRow.cloneNode(true);
                const input = newRow.querySelector('input');
                input.value = '';
                input.placeholder = 'Ej. Módulo 2: Arquitectura';
                modulesContainer.appendChild(newRow);
                updateLivePreview();
            });
        }

        window.removeModuleRow = function (button) {
            const rows = modulesContainer ? modulesContainer.querySelectorAll('.module-row') : [];
            if (!rows.length) return;
            if (rows.length > 1) {
                button.closest('.module-row').remove();
            } else {
                const input = button.closest('.module-row').querySelector('input');
                if (input) input.value = '';
            }
            updateLivePreview();
        };

        window.removeDynamicEntry = function (button, containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            const rows = container.querySelectorAll('.dynamic-entry-row');
            if (!rows.length) return;
            if (rows.length > 1) {
                button.closest('.dynamic-entry-row').remove();
            } else {
                const textInput = button.closest('.dynamic-entry-row').querySelector('textarea, input');
                if (textInput) textInput.value = '';
            }
            updateLivePreview();
        };

        if (addObjetivoBtn && objetivosContainer) {
            addObjetivoBtn.addEventListener('click', function () {
                const firstRow = objetivosContainer.querySelector('.dynamic-entry-row');
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input, textarea').forEach(function (field) {
                    field.value = '';
                });
                objetivosContainer.appendChild(newRow);
                updateLivePreview();
            });
        }

        if (addSeoBtn && seoContainer) {
            addSeoBtn.addEventListener('click', function () {
                const firstRow = seoContainer.querySelector('.dynamic-entry-row');
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input, textarea').forEach(function (field) {
                    field.value = '';
                });
                seoContainer.appendChild(newRow);
                updateLivePreview();
            });
        }

        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Describe brevemente de qué trata el curso...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    ['link'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        ['titulo', 'descripcion_corta', 'nivel', 'duracion', 'modalidad', 'etiquetas'].forEach(function (fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', updateLivePreview);
                field.addEventListener('change', updateLivePreview);
            }
        });

        document.querySelectorAll('input[name="modulo_nombre"], textarea[name="recursos[]"], select[name="actividades_tipo[]"], input[name="actividades_fecha[]"], textarea[name="actividades_detalle[]"], input[name="evaluaciones_nombre[]"], input[name="evaluaciones_tiempo[]"], textarea[name="evaluaciones_descripcion[]"], select[name="visibilidad"], input[name="certificado_activo"]').forEach(function (field) {
            field.addEventListener('input', updateLivePreview);
            field.addEventListener('change', updateLivePreview);
        });

        if (quill) {
            quill.on('text-change', updateLivePreview);
        }

        document.querySelector('form').addEventListener('submit', function () {
            document.getElementById('descripcion').value = quill.root.innerHTML;
        });

        updateLivePreview();

        function toggleCupoInput() {
            var ilimitado = document.getElementById('cupo_ilimitado');
            var cupoContainer = document.getElementById('cupo_numero_container');
            var cupoInput = document.getElementById('cupo_limite');
            if (ilimitado && cupoContainer && cupoInput) {
                if (ilimitado.checked) {
                    cupoContainer.style.display = 'none';
                    cupoInput.removeAttribute('required');
                    cupoInput.value = '';
                } else {
                    cupoContainer.style.display = 'block';
                    cupoInput.setAttribute('required', 'required');
                }
            }
        }

        window.showWizardStep = showWizardStep;
        showWizardStep(1);
    </script>
    <script>
        (function () {
            const moduleButtons = document.querySelectorAll('.builder-module-pill');
            const componentCards = document.querySelectorAll('.component-card');
            const modulePanes = document.querySelectorAll('.builder-section-pane');

            function activateModule(moduleName) {
                moduleButtons.forEach(function (button) {
                    button.classList.toggle('active', button.getAttribute('data-module') === moduleName);
                });
                componentCards.forEach(function (card) {
                    card.classList.toggle('active', card.getAttribute('data-module') === moduleName);
                });
                modulePanes.forEach(function (pane) {
                    pane.classList.toggle('active', pane.getAttribute('data-module-pane') === moduleName);
                });
            }

            moduleButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    activateModule(button.getAttribute('data-module'));
                });
            });

            componentCards.forEach(function (card) {
                card.addEventListener('click', function () {
                    activateModule(card.getAttribute('data-module'));
                });
            });

            const controls = {
                title: document.getElementById('builderTitle'),
                code: document.getElementById('builderCode'),
                shortDescription: document.getElementById('builderShortDescription'),
                description: document.getElementById('builderDescription'),
                category: document.getElementById('builderCategory'),
                instructor: document.getElementById('builderInstructor'),
                duration: document.getElementById('builderDuration'),
                level: document.getElementById('builderLevel'),
                modality: document.getElementById('builderModality'),
                language: document.getElementById('builderLanguage'),
                price: document.getElementById('builderPrice'),
                free: document.getElementById('builderFree'),
                certificate: document.getElementById('builderCertificate'),
                objectives: document.getElementById('builderObjectives'),
                modules: document.getElementById('builderModules'),
                resources: document.getElementById('builderResources'),
                video: document.getElementById('builderVideo'),
                competencies: document.getElementById('builderCompetencies'),
                requirements: document.getElementById('builderRequirements'),
                tags: document.getElementById('builderTags'),
                visibility: document.getElementById('builderVisibility'),
                accessMode: document.getElementById('builderAccessMode'),
                metaTitle: document.getElementById('builderMetaTitle'),
                metaDescription: document.getElementById('builderMetaDescription')
            };

            const previewTitle = document.getElementById('builderPreviewTitle');
            const previewShort = document.getElementById('builderPreviewShort');
            const previewDescription = document.getElementById('builderPreviewDescription');
            const previewMeta = document.getElementById('builderPreviewMeta');
            const previewObjectives = document.getElementById('builderPreviewObjectives');
            const previewModules = document.getElementById('builderPreviewModules');

            function syncBuilderToForm() {
                const hiddenFields = {
                    titulo: document.querySelector('input[name="titulo"]'),
                    codigo: document.querySelector('input[name="codigo"]'),
                    descripcion_corta: document.querySelector('input[name="descripcion_corta"]'),
                    descripcion: document.querySelector('textarea[name="descripcion"]'),
                    categoria: document.querySelector('input[name="categoria"]'),
                    instructor: document.querySelector('input[name="instructor"]'),
                    duracion: document.querySelector('input[name="duracion"]'),
                    nivel: document.querySelector('select[name="nivel"]'),
                    modalidad: document.querySelector('select[name="modalidad"]'),
                    idioma: document.querySelector('input[name="idioma"]'),
                    precio: document.querySelector('input[name="precio"]'),
                    gratuito: document.querySelector('input[name="gratuito"]'),
                    certificado: document.querySelector('input[name="certificado"]'),
                    objetivos: document.querySelector('textarea[name="objetivos"]'),
                    competencias: document.querySelector('textarea[name="competencias"]'),
                    requisitos: document.querySelector('textarea[name="requisitos"]'),
                    etiquetas: document.querySelector('input[name="etiquetas"]'),
                    modulos_previstos: document.querySelector('textarea[name="modulos_previstos"]'),
                    recursos: document.querySelector('textarea[name="recursos"]'),
                    video_intro_url: document.querySelector('input[name="video_intro_url"]'),
                    meta_titulo: document.querySelector('input[name="meta_titulo"]'),
                    meta_descripcion: document.querySelector('textarea[name="meta_descripcion"]')
                };

                const title = controls.title?.value?.trim() || '';
                if (hiddenFields.titulo) hiddenFields.titulo.value = title;
                if (hiddenFields.codigo) hiddenFields.codigo.value = controls.code?.value?.trim() || '';
                if (hiddenFields.descripcion_corta) hiddenFields.descripcion_corta.value = controls.shortDescription?.value?.trim() || '';
                if (hiddenFields.descripcion) hiddenFields.descripcion.value = controls.description?.value || '';
                if (hiddenFields.categoria) hiddenFields.categoria.value = controls.category?.value?.trim() || '';
                if (hiddenFields.instructor) hiddenFields.instructor.value = controls.instructor?.value?.trim() || '';
                if (hiddenFields.duracion) hiddenFields.duracion.value = controls.duration?.value?.trim() || '';
                if (hiddenFields.nivel) hiddenFields.nivel.value = controls.level?.value || 'Básico';
                if (hiddenFields.modalidad) hiddenFields.modalidad.value = controls.modality?.value || 'Online';
                if (hiddenFields.idioma) hiddenFields.idioma.value = controls.language?.value?.trim() || 'Español';
                if (hiddenFields.precio) hiddenFields.precio.value = controls.price?.value || '0';
                if (hiddenFields.gratuito) hiddenFields.gratuito.checked = Boolean(controls.free?.checked);
                if (hiddenFields.certificado) hiddenFields.certificado.checked = Boolean(controls.certificate?.checked);
                if (hiddenFields.objetivos) hiddenFields.objetivos.value = controls.objectives?.value?.trim() || '';
                if (hiddenFields.competencias) hiddenFields.competencias.value = controls.competencies?.value?.trim() || '';
                if (hiddenFields.requisitos) hiddenFields.requisitos.value = controls.requirements?.value?.trim() || '';
                if (hiddenFields.etiquetas) hiddenFields.etiquetas.value = controls.tags?.value?.trim() || '';
                if (hiddenFields.modulos_previstos) hiddenFields.modulos_previstos.value = controls.modules?.value?.trim() || '';
                if (hiddenFields.recursos) hiddenFields.recursos.value = controls.resources?.value?.trim() || '';
                if (hiddenFields.video_intro_url) hiddenFields.video_intro_url.value = controls.video?.value?.trim() || '';
                if (hiddenFields.meta_titulo) hiddenFields.meta_titulo.value = controls.metaTitle?.value?.trim() || '';
                if (hiddenFields.meta_descripcion) hiddenFields.meta_descripcion.value = controls.metaDescription?.value?.trim() || '';

                const titleHeader = document.getElementById('courseTitleHeader');
                if (titleHeader) titleHeader.value = title;
            }

            function updateBuilderPreview() {
                const title = controls.title?.value?.trim() || 'Título del curso';
                const short = controls.shortDescription?.value?.trim() || 'Describe brevemente lo que aprenderás y por qué este curso es valioso.';
                const level = controls.level?.value || 'Básico';
                const modality = controls.modality?.value || 'Online';
                const duration = controls.duration?.value?.trim() || 'Duración flexible';
                const description = controls.description?.value?.trim() || 'Aquí aparecerá la descripción completa mientras editas.';
                const tags = controls.tags?.value?.trim() || '';
                const objectives = (controls.objectives?.value || '').split(/\n+/).map(function (item) { return item.trim(); }).filter(Boolean).slice(0, 4);
                const modules = (controls.modules?.value || '').split(/\n+/).map(function (item) { return item.trim(); }).filter(Boolean).slice(0, 4);

                if (previewTitle) previewTitle.textContent = title;
                if (previewShort) previewShort.textContent = short;
                if (previewDescription) previewDescription.innerHTML = description.replace(/\n/g, '<br>');
                if (previewMeta) {
                    previewMeta.innerHTML = '<span class="badge rounded-pill bg-primary-subtle text-primary">' + escapeHtml(level) + '</span><span class="badge rounded-pill bg-light text-dark">' + escapeHtml(duration) + '</span><span class="badge rounded-pill bg-success-subtle text-success">' + escapeHtml(modality) + '</span>';
                }
                if (previewObjectives) {
                    previewObjectives.innerHTML = objectives.length ? objectives.map(function (item) { return '<div class="d-flex gap-2 align-items-start"><i class="bi bi-check-circle-fill text-success mt-1"></i><span>' + escapeHtml(item) + '</span></div>'; }).join('') : '<div class="small text-muted">Añade objetivos para convertir tu propuesta en un curso claro y atractivo.</div>';
                }
                if (previewModules) {
                    previewModules.innerHTML = modules.length ? modules.map(function (item) { return '<div class="d-flex gap-2 align-items-start"><i class="bi bi-diagram-3 text-primary mt-1"></i><span>' + escapeHtml(item) + '</span></div>'; }).join('') : '<div class="small text-muted">Tu ruta de aprendizaje aparecerá aquí.</div>';
                }
                if (tags) {
                    const chips = tags.split(',').map(function (item) { return item.trim(); }).filter(Boolean).slice(0, 6).map(function (item) { return '<span class="badge rounded-pill bg-light text-muted">' + escapeHtml(item) + '</span>'; }).join('');
                    if (previewMeta) previewMeta.innerHTML += chips;
                }
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function persistDraft() {
                syncBuilderToForm();
                updateBuilderPreview();
                try {
                    const payload = JSON.stringify({
                        title: controls.title?.value || '',
                        shortDescription: controls.shortDescription?.value || '',
                        description: controls.description?.value || '',
                        code: controls.code?.value || '',
                        category: controls.category?.value || '',
                        instructor: controls.instructor?.value || '',
                        duration: controls.duration?.value || '',
                        level: controls.level?.value || 'Básico',
                        modality: controls.modality?.value || 'Online',
                        language: controls.language?.value || 'Español',
                        price: controls.price?.value || '0',
                        free: controls.free?.checked || false,
                        certificate: controls.certificate?.checked || false,
                        objectives: controls.objectives?.value || '',
                        modules: controls.modules?.value || '',
                        resources: controls.resources?.value || '',
                        video: controls.video?.value || '',
                        competencies: controls.competencies?.value || '',
                        requirements: controls.requirements?.value || '',
                        tags: controls.tags?.value || '',
                        visibility: controls.visibility?.value || 'Público',
                        accessMode: controls.accessMode?.value || 'Libre',
                        metaTitle: controls.metaTitle?.value || '',
                        metaDescription: controls.metaDescription?.value || ''
                    });
                    localStorage.setItem('northstarCourseBuilderDraftV2', payload);
                } catch (e) {
                    console.warn('No se pudo guardar el borrador', e);
                }
            }

            function restoreDraft() {
                try {
                    const raw = localStorage.getItem('northstarCourseBuilderDraftV2');
                    if (!raw) return;
                    const draft = JSON.parse(raw);
                    if (draft.title !== undefined && controls.title) controls.title.value = draft.title;
                    if (draft.shortDescription !== undefined && controls.shortDescription) controls.shortDescription.value = draft.shortDescription;
                    if (draft.description !== undefined && controls.description) controls.description.value = draft.description;
                    if (draft.code !== undefined && controls.code) controls.code.value = draft.code;
                    if (draft.category !== undefined && controls.category) controls.category.value = draft.category;
                    if (draft.instructor !== undefined && controls.instructor) controls.instructor.value = draft.instructor;
                    if (draft.duration !== undefined && controls.duration) controls.duration.value = draft.duration;
                    if (draft.level !== undefined && controls.level) controls.level.value = draft.level;
                    if (draft.modality !== undefined && controls.modality) controls.modality.value = draft.modality;
                    if (draft.language !== undefined && controls.language) controls.language.value = draft.language;
                    if (draft.price !== undefined && controls.price) controls.price.value = draft.price;
                    if (draft.free !== undefined && controls.free) controls.free.checked = Boolean(draft.free);
                    if (draft.certificate !== undefined && controls.certificate) controls.certificate.checked = Boolean(draft.certificate);
                    if (draft.objectives !== undefined && controls.objectives) controls.objectives.value = draft.objectives;
                    if (draft.modules !== undefined && controls.modules) controls.modules.value = draft.modules;
                    if (draft.resources !== undefined && controls.resources) controls.resources.value = draft.resources;
                    if (draft.video !== undefined && controls.video) controls.video.value = draft.video;
                    if (draft.competencies !== undefined && controls.competencies) controls.competencies.value = draft.competencies;
                    if (draft.requirements !== undefined && controls.requirements) controls.requirements.value = draft.requirements;
                    if (draft.tags !== undefined && controls.tags) controls.tags.value = draft.tags;
                    if (draft.visibility !== undefined && controls.visibility) controls.visibility.value = draft.visibility;
                    if (draft.accessMode !== undefined && controls.accessMode) controls.accessMode.value = draft.accessMode;
                    if (draft.metaTitle !== undefined && controls.metaTitle) controls.metaTitle.value = draft.metaTitle;
                    if (draft.metaDescription !== undefined && controls.metaDescription) controls.metaDescription.value = draft.metaDescription;
                } catch (e) {
                    console.warn('No se pudo restaurar el borrador', e);
                }
            }

            Object.keys(controls).forEach(function (key) {
                const control = controls[key];
                if (!control) return;
                const handleChange = function () {
                    persistDraft();
                };
                control.addEventListener('input', handleChange);
                control.addEventListener('change', handleChange);
            });

            const form = document.getElementById('courseWizardForm');
            if (form) {
                form.addEventListener('submit', function () {
                    syncBuilderToForm();
                });
            }

            const saveBorradorBtnTop = document.getElementById('saveBorradorBtnTop');
            if (saveBorradorBtnTop) {
                saveBorradorBtnTop.addEventListener('click', function () {
                    const hidden = document.getElementById('submitBorradorHidden');
                    if (hidden) hidden.click();
                });
            }

            const publishBtnTop = document.getElementById('publishBtnTop');
            if (publishBtnTop) {
                publishBtnTop.addEventListener('click', function () {
                    const hidden = document.getElementById('submitPublicarHidden');
                    if (hidden) hidden.click();
                });
            }

            activateModule('info');
            restoreDraft();
            persistDraft();
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
