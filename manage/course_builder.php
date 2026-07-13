<?php
require_once '../config/db.php';
require_once '../models/Usuario.php';

$is_wizard = true; // minimal navigation bar
session_start();

// Auth check
if (!isset($_SESSION['user_role']) || !in_array((int) $_SESSION['user_role'], [1, 2], true)) {
    header("Location: ../login.php");
    exit;
}

$usuarioModel = new Usuario($conn);
$course_id = intval($_GET['course_id'] ?? 0);

if ($course_id <= 0) {
    header("Location: index.php?tab=cursos");
    exit;
}

// Fetch course information
$stmt = $conn->prepare("SELECT * FROM lc_cursos WHERE id = :id");
$stmt->execute(['id' => $course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header("Location: index.php?tab=cursos");
    exit;
}

// Load current hierarchy
$modules = [];
$modStmt = $conn->prepare("SELECT * FROM lc_modulos WHERE curso_id = :curso_id ORDER BY orden_num ASC");
$modStmt->execute(['curso_id' => $course_id]);
$rawModules = $modStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rawModules as $module) {
    $module_id = $module['id'];
    
    // Get units
    $unitStmt = $conn->prepare("SELECT * FROM lc_unidades WHERE modulo_id = :modulo_id ORDER BY orden_num ASC");
    $unitStmt->execute(['modulo_id' => $module_id]);
    $rawUnits = $unitStmt->fetchAll(PDO::FETCH_ASSOC);
    $units = [];
    
    foreach ($rawUnits as $unit) {
        $unit_id = $unit['id'];
        
        // Get lessons
        $lesStmt = $conn->prepare("SELECT * FROM lc_lecciones WHERE unidad_id = :unidad_id ORDER BY orden_num ASC");
        $lesStmt->execute(['unidad_id' => $unit_id]);
        $rawLessons = $lesStmt->fetchAll(PDO::FETCH_ASSOC);
        $lessons = [];
        
        foreach ($rawLessons as $lesson) {
            $lesson_id = $lesson['id'];
            
            // Get resources
            $resStmt = $conn->prepare("SELECT * FROM lc_recursos WHERE leccion_id = :leccion_id");
            $resStmt->execute(['leccion_id' => $lesson_id]);
            $resources = $resStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get activity
            $actStmt = $conn->prepare("SELECT * FROM lc_actividades WHERE leccion_id = :leccion_id");
            $actStmt->execute(['leccion_id' => $lesson_id]);
            $activity = $actStmt->fetch(PDO::FETCH_ASSOC);
            
            // Get evaluation
            $evalStmt = $conn->prepare("SELECT * FROM lc_evaluaciones WHERE leccion_id = :leccion_id");
            $evalStmt->execute(['leccion_id' => $lesson_id]);
            $evaluation = $evalStmt->fetch(PDO::FETCH_ASSOC);
            
            $lessons[] = [
                'id' => $lesson_id,
                'nombre' => $lesson['nombre'],
                'tipo' => $lesson['tipo'],
                'contenido_texto' => $lesson['contenido_texto'],
                'contenido_url' => $lesson['contenido_url'],
                'duracion_segundos' => $lesson['duracion_segundos'],
                'estado' => $lesson['estado'],
                'recursos' => $resources,
                'actividad' => $activity,
                'evaluacion' => $evaluation
            ];
        }
        
        $units[] = [
            'id' => $unit_id,
            'nombre' => $unit['nombre'],
            'lessons' => $lessons
        ];
    }
    
    $modules[] = [
        'id' => $module_id,
        'nombre' => $module['nombre'],
        'units' => $units
    ];
}

$page_title = 'Constructor de Curso - ' . htmlspecialchars($course['nombre']);
require_once '../includes/manage/header.php';
?>

<div class="container-fluid my-4 px-4">
    <!-- Top Control Bar -->
    <div class="card border-0 rounded-4 shadow-sm p-3.5 mb-4 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                    <i class="bi bi-diagram-3 fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Constructor Visual: <?= htmlspecialchars($course['nombre']) ?></h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Crea y organiza la estructura curricular con Drag & Drop</span>
                        <span class="badge rounded-pill bg-light text-muted border px-2 py-0.5" id="autosaveStatus">
                            <i class="bi bi-check2-all text-success me-1"></i>Guardado
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?tab=cursos" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Cursos
                </a>
                <a href="../index.php?action=course&id=<?= $course_id ?>" target="_blank" class="btn btn-outline-primary btn-sm px-3 rounded-pill">
                    <i class="bi bi-eye me-1"></i> Vista Estudiante
                </a>
                <button type="button" class="btn btn-primary btn-sm px-4 rounded-pill text-white fw-semibold shadow-sm" id="manualSaveBtn">
                    <i class="bi bi-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <!-- Main Workspace Row -->
    <div class="row g-4">
        
        <!-- Column 1: Curriculum Tree (Left, col-lg-4) -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white edit-sidebar-sticky" style="max-height: calc(100vh - 140px); overflow-y: auto;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="small text-uppercase fw-bold text-muted mb-0" style="letter-spacing:.06em; font-size:.7rem;">Estructura Curricular</h6>
                    <button type="button" class="btn btn-xs btn-primary rounded-pill px-2 py-1" id="addModuleBtn">
                        <i class="bi bi-plus-circle me-1"></i>Módulo
                    </button>
                </div>
                
                <div class="curriculum-tree-container" id="modulesSortable">
                    <?php if (empty($modules)): ?>
                        <div class="text-center py-5 text-muted" id="noModulesPlaceholder">
                            <i class="bi bi-folder-plus display-4 text-muted mb-2"></i>
                            <p class="small mb-0">No hay módulos creados en este curso.</p>
                            <button type="button" class="btn btn-sm btn-link mt-2" onclick="document.getElementById('addModuleBtn').click();">Crear primer módulo</button>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach ($modules as $m): ?>
                        <div class="card border rounded-3 mb-3 module-node" data-id="<?= $m['id'] ?>">
                            <div class="card-header bg-light d-flex align-items-center justify-content-between py-2 px-3">
                                <div class="d-flex align-items-center gap-2 cursor-grab sort-handle">
                                    <i class="bi bi-grip-vertical text-muted"></i>
                                    <span class="fw-bold text-dark module-title" onclick="selectItem('module', <?= $m['id'] ?>, '<?= addslashes($m['nombre']) ?>')"><?= htmlspecialchars($m['nombre']) ?></span>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-link btn-sm text-muted p-0" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                        <li><a class="dropdown-item" href="#" onclick="selectItem('module', <?= $m['id'] ?>, '<?= addslashes($m['nombre']) ?>')"><i class="bi bi-pencil me-2"></i>Editar Módulo</a></li>
                                        <li><a class="dropdown-item text-primary" href="#" onclick="addUnit(<?= $m['id'] ?>)"><i class="bi bi-plus-circle me-2"></i>Añadir Unidad</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteItem('module', <?= $m['id'] ?>)"><i class="bi bi-trash me-2"></i>Eliminar</a></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="card-body p-2 bg-white units-container" data-module-id="<?= $m['id'] ?>">
                                <?php foreach ($m['units'] as $u): ?>
                                    <div class="border rounded-2 mb-2 p-2 bg-light unit-node" data-id="<?= $u['id'] ?>">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="d-flex align-items-center gap-2 cursor-grab sort-handle-unit">
                                                <i class="bi bi-grip-vertical text-muted"></i>
                                                <span class="fw-semibold text-secondary small unit-title" onclick="selectItem('unit', <?= $u['id'] ?>, '<?= addslashes($u['nombre']) ?>')"><?= htmlspecialchars($u['nombre']) ?></span>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-link btn-xs text-muted p-0" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                                    <li><a class="dropdown-item" href="#" onclick="selectItem('unit', <?= $u['id'] ?>, '<?= addslashes($u['nombre']) ?>')"><i class="bi bi-pencil me-2"></i>Editar Unidad</a></li>
                                                    <li><a class="dropdown-item text-primary" href="#" onclick="addLesson(<?= $u['id'] ?>)"><i class="bi bi-plus-circle me-2"></i>Añadir Lección</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteItem('unit', <?= $u['id'] ?>)"><i class="bi bi-trash me-2"></i>Eliminar</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <div class="lessons-container" data-unit-id="<?= $u['id'] ?>">
                                            <?php foreach ($u['lessons'] as $l): ?>
                                                <div class="border rounded-1 p-2 bg-white mb-1 lesson-node" data-id="<?= $l['id'] ?>">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center gap-2 cursor-grab sort-handle-lesson">
                                                            <i class="bi bi-grip-vertical text-muted"></i>
                                                            <i class="bi <?= getLessonIcon($l['tipo']) ?> text-primary small"></i>
                                                            <span class="small text-muted lesson-title" onclick="selectLesson(<?= htmlspecialchars(json_encode($l)) ?>)"><?= htmlspecialchars($l['nombre']) ?></span>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-link btn-xs text-muted p-0" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                                                <li><a class="dropdown-item" href="#" onclick="selectLesson(<?= htmlspecialchars(json_encode($l)) ?>)"><i class="bi bi-pencil me-2"></i>Configurar Lección</a></li>
                                                                <li><a class="dropdown-item text-success" href="#" onclick="manageActivity(<?= $l['id'] ?>, <?= htmlspecialchars(json_encode($l['actividad'])) ?>)"><i class="bi bi-check-circle me-2"></i>Actividad</a></li>
                                                                <li><a class="dropdown-item text-warning" href="#" onclick="manageEvaluation(<?= $l['id'] ?>, <?= htmlspecialchars(json_encode($l['evaluacion'])) ?>)"><i class="bi bi-journal-check me-2"></i>Evaluación</a></li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteItem('lesson', <?= $l['id'] ?>)"><i class="bi bi-trash me-2"></i>Eliminar</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Column 2: Workspace Editor (Center, col-lg-5) -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100" id="editorContainer">
                
                <!-- Welcome Blank Slate -->
                <div class="text-center py-5 my-5" id="welcomePane">
                    <i class="bi bi-brush display-3 text-primary bg-light rounded-circle p-4 mb-4 d-inline-block"></i>
                    <h5 class="fw-bold">Comienza a Diseñar tu Curso</h5>
                    <p class="text-muted small px-4">Selecciona cualquier módulo, unidad o lección del panel de la izquierda para editar sus contenidos y configuraciones.</p>
                </div>

                <!-- Form: Modulo Edit -->
                <div class="d-none" id="moduleForm">
                    <h5 class="fw-bold mb-3"><i class="bi bi-folder-fill text-primary me-2"></i>Editar Módulo</h5>
                    <input type="hidden" id="moduleId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre del Módulo</label>
                        <input type="text" class="form-control form-control-premium" id="moduleName" placeholder="Ej. Fundamentos Básicos">
                    </div>
                </div>

                <!-- Form: Unidad Edit -->
                <div class="d-none" id="unitForm">
                    <h5 class="fw-bold mb-3"><i class="bi bi-journal-album text-primary me-2"></i>Editar Unidad</h5>
                    <input type="hidden" id="unitId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre de la Unidad</label>
                        <input type="text" class="form-control form-control-premium" id="unitName" placeholder="Ej. Instalación de Herramientas">
                    </div>
                </div>

                <!-- Form: Lección Edit -->
                <div class="d-none" id="lessonForm">
                    <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-play-fill text-primary me-2"></i>Configurar Lección</h5>
                    <input type="hidden" id="lessonId">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Título de la Lección</label>
                        <input type="text" class="form-control form-control-premium" id="lessonName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tipo de Contenido</label>
                        <select class="form-select form-control-premium" id="lessonType">
                            <option value="texto">Texto Enriquecido / Lectura</option>
                            <option value="video">Video (MP4 / Local)</option>
                            <option value="youtube">YouTube Embed</option>
                            <option value="vimeo">Vimeo Embed</option>
                            <option value="pdf">Documento PDF</option>
                            <option value="codigo">Editor de Código</option>
                            <option value="link">Enlace Externo</option>
                        </select>
                    </div>

                    <!-- URL / Archivo Container -->
                    <div class="mb-3 d-none" id="lessonUrlContainer">
                        <label class="form-label small fw-bold" id="lessonUrlLabel">URL del Contenido</label>
                        <input type="text" class="form-control form-control-premium" id="lessonUrl" placeholder="https://...">
                    </div>

                    <!-- Duration -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Duración Estimada (Segundos)</label>
                        <input type="number" class="form-control form-control-premium" id="lessonDuration" min="0" value="0">
                        <div class="form-text small text-muted">Ej: 300 segundos = 5 minutos.</div>
                    </div>

                    <!-- Quill Text Editor -->
                    <div class="mb-3" id="lessonEditorContainer">
                        <label class="form-label small fw-bold">Contenido de la clase</label>
                        <div id="lessonQuillEditor" style="height: 240px;"></div>
                        <textarea class="d-none" id="lessonTextarea"></textarea>
                    </div>

                    <!-- Visibility -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Estado</label>
                        <select class="form-select form-control-premium" id="lessonStatus">
                            <option value="1">Publicada (Visible)</option>
                            <option value="0">Borrador (Oculta)</option>
                        </select>
                    </div>
                </div>

                <!-- Form: Actividades -->
                <div class="d-none" id="activityForm">
                    <h5 class="fw-bold mb-3 text-success"><i class="bi bi-check-circle-fill me-2"></i>Asignar Actividad Práctica</h5>
                    <input type="hidden" id="activityLessonId">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre de la Actividad</label>
                        <input type="text" class="form-control form-control-premium" id="activityName">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tipo de Actividad</label>
                        <select class="form-select form-control-premium" id="activityType">
                            <option value="tarea">Subida de Archivo / Entregable</option>
                            <option value="foro">Participación en Foro de debate</option>
                            <option value="proyecto">Proyecto Integrador</option>
                            <option value="debate">Debate Virtual</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Instrucciones y Pautas</label>
                        <div id="activityQuillEditor" style="height: 180px;"></div>
                        <textarea class="d-none" id="activityTextarea"></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="activityGroup" value="1">
                            <label class="form-check-label small fw-bold" for="activityGroup">Actividad Grupal</label>
                        </div>
                    </div>
                </div>

                <!-- Form: Evaluaciones -->
                <div class="d-none" id="evaluationForm">
                    <h5 class="fw-bold mb-3 text-warning"><i class="bi bi-journal-check me-2"></i>Configurar Examen / Evaluación</h5>
                    <input type="hidden" id="evaluationLessonId">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre del Examen</label>
                        <input type="text" class="form-control form-control-premium" id="evaluationName">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Descripción / Instrucciones</label>
                        <textarea class="form-control form-control-premium" id="evaluationDesc" rows="3"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tiempo Límite (Minutos)</label>
                            <input type="number" class="form-control form-control-premium" id="evaluationTime" min="0" value="0">
                            <div class="form-text small text-muted">0 = Sin límite.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Intentos Permitidos</label>
                            <input type="number" class="form-control form-control-premium" id="evaluationAttempts" min="1" value="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Calificación Mínima (%)</label>
                            <input type="number" class="form-control form-control-premium" id="evaluationScore" min="1" max="100" value="60">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Column 3: Live Preview & Tips (Right, col-lg-3) -->
        <div class="col-lg-3">
            <div class="edit-sidebar-sticky d-grid gap-3">
                
                <!-- Student Live Preview Card -->
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                    <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.06em; font-size:.7rem;">
                        <i class="bi bi-play-circle me-1 text-primary"></i>Vista Previa Estudiante
                    </div>
                    <div id="previewCardContent">
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-eye-slash display-6 text-muted mb-2 d-block"></i>
                            Elige una lección para ver cómo la visualiza tu alumno.
                        </div>
                    </div>
                </div>

                <!-- Instructional Tips Panel -->
                <div class="card border-0 rounded-4 shadow-sm p-4" style="background: linear-gradient(135deg, rgba(26,115,232,0.06) 0%, rgba(46,125,50,0.04) 100%); border: 1px solid rgba(26,115,232,0.1) !important;">
                    <div class="small text-uppercase fw-bold text-primary mb-3" style="letter-spacing:.06em; font-size:.68rem;">
                        <i class="bi bi-lightbulb me-1"></i>Recomendaciones LMS
                    </div>
                    <div id="instructionalTips">
                        <ul class="list-unstyled mb-0 d-grid gap-2 small text-muted">
                            <li class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                                Mantén las lecciones entre 3-7 minutos para retener atención.
                            </li>
                            <li class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                                Añade una actividad entregable al finalizar cada módulo principal.
                            </li>
                            <li class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                                Escribe objetivos de aprendizaje claros usando verbos de la taxonomía de Bloom.
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Scripts -->
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
let activeItemType = '';
let activeItemId = 0;
let hasUnsavedChanges = false;
let autoSaveInterval = null;

// Initialize Quill Editors
const lessonQuill = new Quill('#lessonQuillEditor', {
    theme: 'snow',
    placeholder: 'Escribe el contenido detallado de la lección aquí...',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image', 'video'],
            ['clean']
        ]
    }
});

const activityQuill = new Quill('#activityQuillEditor', {
    theme: 'snow',
    placeholder: 'Detalla las pautas, requerimientos y rúbricas de la actividad...',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link'],
            ['clean']
        ]
    }
});

// Configure Drag & Drop with SortableJS
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sort Modules
    const modulesEl = document.getElementById('modulesSortable');
    if (modulesEl) {
        new Sortable(modulesEl, {
            handle: '.sort-handle',
            animation: 150,
            onEnd: function() {
                const ids = Array.from(modulesEl.querySelectorAll('.module-node')).map(el => el.getAttribute('data-id'));
                saveOrdering('sort_modules', ids);
            }
        });
    }

    // 2. Sort Units
    document.querySelectorAll('.units-container').forEach(el => {
        new Sortable(el, {
            handle: '.sort-handle-unit',
            animation: 150,
            onEnd: function() {
                const ids = Array.from(el.querySelectorAll('.unit-node')).map(node => node.getAttribute('data-id'));
                saveOrdering('sort_units', ids);
            }
        });
    });

    // 3. Sort Lessons
    document.querySelectorAll('.lessons-container').forEach(el => {
        new Sortable(el, {
            handle: '.sort-handle-lesson',
            animation: 150,
            onEnd: function() {
                const ids = Array.from(el.querySelectorAll('.lesson-node')).map(node => node.getAttribute('data-id'));
                saveOrdering('sort_lessons', ids);
            }
        });
    });

    // Handle autosave loop (30 seconds)
    autoSaveInterval = setInterval(autoSave, 30000);
});

// Save sorting AJAX
function saveOrdering(action, ids) {
    setStatus('saving');
    const formData = new FormData();
    formData.append('action', action);
    ids.forEach(id => formData.append('ids[]', id));

    fetch('save_builder.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            setStatus('saved');
        } else {
            setStatus('error');
            console.error(data.error);
        }
    })
    .catch(err => {
        setStatus('error');
        console.error(err);
    });
}

// Select Sidebar Nodes
function selectItem(type, id, name) {
    saveCurrent(() => {
        activeItemType = type;
        activeItemId = id;
        
        // Hide welcome pane
        document.getElementById('welcomePane').classList.add('d-none');
        
        // Hide all forms
        document.getElementById('moduleForm').classList.add('d-none');
        document.getElementById('unitForm').classList.add('d-none');
        document.getElementById('lessonForm').classList.add('d-none');
        document.getElementById('activityForm').classList.add('d-none');
        document.getElementById('evaluationForm').classList.add('d-none');
        
        if (type === 'module') {
            document.getElementById('moduleForm').classList.remove('d-none');
            document.getElementById('moduleId').value = id;
            document.getElementById('moduleName').value = name;
            updateTips('module');
            updatePreview('module', { nombre: name });
        } else if (type === 'unit') {
            document.getElementById('unitForm').classList.remove('d-none');
            document.getElementById('unitId').value = id;
            document.getElementById('unitName').value = name;
            updateTips('unit');
            updatePreview('unit', { nombre: name });
        }
        
        hasUnsavedChanges = false;
        setStatus('saved');
    });
}

function selectLesson(lesson) {
    saveCurrent(() => {
        activeItemType = 'lesson';
        activeItemId = lesson.id;
        
        document.getElementById('welcomePane').classList.add('d-none');
        document.getElementById('moduleForm').classList.add('d-none');
        document.getElementById('unitForm').classList.add('d-none');
        document.getElementById('activityForm').classList.add('d-none');
        document.getElementById('evaluationForm').classList.add('d-none');
        
        const form = document.getElementById('lessonForm');
        form.classList.remove('d-none');
        
        document.getElementById('lessonId').value = lesson.id;
        document.getElementById('lessonName').value = lesson.nombre;
        document.getElementById('lessonType').value = lesson.tipo;
        document.getElementById('lessonUrl').value = lesson.contenido_url || '';
        document.getElementById('lessonDuration').value = lesson.duracion_segundos || 0;
        document.getElementById('lessonStatus').value = lesson.estado || 1;
        
        lessonQuill.root.innerHTML = lesson.contenido_texto || '';
        
        toggleUrlField(lesson.tipo);
        updateTips('lesson', lesson.tipo);
        updatePreview('lesson', lesson);
        
        hasUnsavedChanges = false;
        setStatus('saved');
    });
}

function manageActivity(lessonId, activity) {
    saveCurrent(() => {
        activeItemType = 'activity';
        activeItemId = lessonId;
        
        document.getElementById('welcomePane').classList.add('d-none');
        document.getElementById('moduleForm').classList.add('d-none');
        document.getElementById('unitForm').classList.add('d-none');
        document.getElementById('lessonForm').classList.add('d-none');
        document.getElementById('evaluationForm').classList.add('d-none');
        
        const form = document.getElementById('activityForm');
        form.classList.remove('d-none');
        
        document.getElementById('activityLessonId').value = lessonId;
        document.getElementById('activityName').value = activity ? activity.nombre : 'Actividad Práctica';
        document.getElementById('activityType').value = activity ? activity.tipo : 'tarea';
        document.getElementById('activityGroup').checked = activity && parseInt(activity.es_grupal) === 1;
        activityQuill.root.innerHTML = activity ? activity.descripcion : '';
        
        updateTips('activity');
        updatePreview('activity', activity || { nombre: 'Actividad Práctica', tipo: 'tarea', descripcion: '' });
        
        hasUnsavedChanges = false;
        setStatus('saved');
    });
}

function manageEvaluation(lessonId, evaluation) {
    saveCurrent(() => {
        activeItemType = 'evaluation';
        activeItemId = lessonId;
        
        document.getElementById('welcomePane').classList.add('d-none');
        document.getElementById('moduleForm').classList.add('d-none');
        document.getElementById('unitForm').classList.add('d-none');
        document.getElementById('lessonForm').classList.add('d-none');
        document.getElementById('activityForm').classList.add('d-none');
        
        const form = document.getElementById('evaluationForm');
        form.classList.remove('d-none');
        
        document.getElementById('evaluationLessonId').value = lessonId;
        document.getElementById('evaluationName').value = evaluation ? evaluation.nombre : 'Examen del Tema';
        document.getElementById('evaluationDesc').value = evaluation ? evaluation.descripcion : '';
        document.getElementById('evaluationTime').value = evaluation ? evaluation.tiempo_limite_minutos : 0;
        document.getElementById('evaluationAttempts').value = evaluation ? evaluation.intentos_permitidos : 1;
        document.getElementById('evaluationScore').value = evaluation ? Math.round(evaluation.calificacion_minima) : 60;
        
        updateTips('evaluation');
        updatePreview('evaluation', evaluation || { nombre: 'Examen del Tema', descripcion: '', tiempo_limite_minutos: 0, intentos_permitidos: 1, calificacion_minima: 60 });
        
        hasUnsavedChanges = false;
        setStatus('saved');
    });
}

// Handle Form field visibility changes
const typeSelect = document.getElementById('lessonType');
if (typeSelect) {
    typeSelect.addEventListener('change', function() {
        toggleUrlField(this.value);
        markChanges();
    });
}

function toggleUrlField(type) {
    const container = document.getElementById('lessonUrlContainer');
    const label = document.getElementById('lessonUrlLabel');
    const input = document.getElementById('lessonUrl');
    
    if (type === 'texto') {
        container.classList.add('d-none');
    } else {
        container.classList.remove('d-none');
        if (type === 'video') {
            label.textContent = 'Ruta de archivo MP4 o Video Local';
            input.placeholder = 'Ej: uploads/videos/clase1.mp4';
        } else if (type === 'youtube' || type === 'vimeo') {
            label.textContent = 'Enlace de inserción / Compartir';
            input.placeholder = 'https://www.youtube.com/watch?v=...';
        } else if (type === 'pdf') {
            label.textContent = 'Enlace o Archivo PDF';
            input.placeholder = 'Ej: uploads/documentos/guia.pdf';
        } else {
            label.textContent = 'Enlace Externo';
            input.placeholder = 'https://...';
        }
    }
}

// Mark pending changes
function markChanges() {
    if (!hasUnsavedChanges) {
        hasUnsavedChanges = true;
        setStatus('unsaved');
    }
}

// Track inputs
['moduleName', 'unitName', 'lessonName', 'lessonUrl', 'lessonDuration', 'lessonStatus', 'activityName', 'activityType', 'activityGroup', 'evaluationName', 'evaluationDesc', 'evaluationTime', 'evaluationAttempts', 'evaluationScore'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('input', markChanges);
        el.addEventListener('change', markChanges);
    }
});

lessonQuill.on('text-change', markChanges);
activityQuill.on('text-change', markChanges);

// Auto-saver AJAX logic
function autoSave(callback) {
    if (!hasUnsavedChanges || !activeItemType || activeItemId <= 0) {
        if (callback) callback();
        return;
    }
    
    setStatus('saving');
    const formData = new FormData();
    formData.append('id', activeItemId);

    if (activeItemType === 'module') {
        formData.append('action', 'edit_module');
        formData.append('nombre', document.getElementById('moduleName').value.trim());
        
        // Update sidebar DOM title locally
        const titleNode = document.querySelector(`.module-node[data-id="${activeItemId}"] .module-title`);
        if (titleNode) titleNode.textContent = document.getElementById('moduleName').value.trim();
        
    } else if (activeItemType === 'unit') {
        formData.append('action', 'edit_unit');
        formData.append('nombre', document.getElementById('unitName').value.trim());
        
        // Update sidebar DOM title locally
        const titleNode = document.querySelector(`.unit-node[data-id="${activeItemId}"] .unit-title`);
        if (titleNode) titleNode.textContent = document.getElementById('unitName').value.trim();
        
    } else if (activeItemType === 'lesson') {
        formData.append('action', 'edit_lesson');
        formData.append('nombre', document.getElementById('lessonName').value.trim());
        formData.append('tipo', document.getElementById('lessonType').value);
        formData.append('contenido_url', document.getElementById('lessonUrl').value.trim());
        formData.append('duracion_segundos', document.getElementById('lessonDuration').value);
        formData.append('estado', document.getElementById('lessonStatus').value);
        formData.append('contenido_texto', lessonQuill.root.innerHTML);
        
        // Update sidebar DOM title locally
        const titleNode = document.querySelector(`.lesson-node[data-id="${activeItemId}"] .lesson-title`);
        if (titleNode) titleNode.textContent = document.getElementById('lessonName').value.trim();
        
    } else if (activeItemType === 'activity') {
        formData.append('action', 'save_activity');
        formData.append('leccion_id', activeItemId);
        formData.append('nombre', document.getElementById('activityName').value.trim());
        formData.append('tipo', document.getElementById('activityType').value);
        formData.append('es_grupal', document.getElementById('activityGroup').checked ? 1 : 0);
        formData.append('descripcion', activityQuill.root.innerHTML);
        
    } else if (activeItemType === 'evaluation') {
        formData.append('action', 'save_evaluation');
        formData.append('leccion_id', activeItemId);
        formData.append('nombre', document.getElementById('evaluationName').value.trim());
        formData.append('descripcion', document.getElementById('evaluationDesc').value.trim());
        formData.append('tiempo_limite_minutos', document.getElementById('evaluationTime').value);
        formData.append('intentos_permitidos', document.getElementById('evaluationAttempts').value);
        formData.append('calificacion_minima', document.getElementById('evaluationScore').value);
    }

    fetch('save_builder.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            setStatus('saved');
            hasUnsavedChanges = false;
            if (callback) callback();
        } else {
            setStatus('error');
            console.error(data.error);
        }
    })
    .catch(err => {
        setStatus('error');
        console.error(err);
    });
}

function saveCurrent(callback) {
    if (hasUnsavedChanges) {
        autoSave(callback);
    } else {
        if (callback) callback();
    }
}

// Status Indicator display
function setStatus(status) {
    const statusEl = document.getElementById('autosaveStatus');
    if (!statusEl) return;
    
    if (status === 'saved') {
        statusEl.className = 'badge rounded-pill bg-light text-muted border px-2 py-0.5';
        statusEl.innerHTML = '<i class="bi bi-check2-all text-success me-1"></i>Guardado';
    } else if (status === 'saving') {
        statusEl.className = 'badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-2 py-0.5';
        statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1" role="status" style="width:10px; height:10px;"></span>Guardando...';
    } else if (status === 'unsaved') {
        statusEl.className = 'badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10 px-2 py-0.5';
        statusEl.innerHTML = '<i class="bi bi-exclamation-circle text-warning me-1"></i>Cambios sin guardar';
    } else if (status === 'error') {
        statusEl.className = 'badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 px-2 py-0.5';
        statusEl.innerHTML = '<i class="bi bi-x-circle text-danger me-1"></i>Error al guardar';
    }
}

// Wire manual save button
const manualSaveBtn = document.getElementById('manualSaveBtn');
if (manualSaveBtn) {
    manualSaveBtn.addEventListener('click', () => {
        if (hasUnsavedChanges) {
            autoSave();
        } else {
            alert('No hay cambios pendientes de guardar.');
        }
    });
}

// Node CRUD operations
document.getElementById('addModuleBtn').addEventListener('click', () => {
    const name = prompt('Nombre del nuevo módulo:', 'Nuevo Módulo');
    if (!name) return;
    
    const formData = new FormData();
    formData.append('action', 'add_module');
    formData.append('curso_id', <?= $course_id ?>);
    formData.append('nombre', name);
    
    fetch('save_builder.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al añadir módulo: ' + data.error);
        }
    });
});

function addUnit(moduleId) {
    const name = prompt('Nombre de la nueva unidad:', 'Nueva Unidad');
    if (!name) return;
    
    const formData = new FormData();
    formData.append('action', 'add_unit');
    formData.append('modulo_id', moduleId);
    formData.append('nombre', name);
    
    fetch('save_builder.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al añadir unidad: ' + data.error);
        }
    });
}

function addLesson(unitId) {
    const name = prompt('Nombre de la lección:', 'Nueva Lección');
    if (!name) return;
    
    const type = prompt('Tipo de contenido (escribe: texto, video, youtube, vimeo, pdf, codigo, link):', 'texto');
    if (!type) return;
    
    const formData = new FormData();
    formData.append('action', 'add_lesson');
    formData.append('unidad_id', unitId);
    formData.append('nombre', name);
    formData.append('tipo', type);
    
    fetch('save_builder.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al añadir lección: ' + data.error);
        }
    });
}

function deleteItem(type, id) {
    if (!confirm(`¿Estás seguro de que deseas eliminar este ${type}? Todos sus elementos contenidos y progresos se perderán irreversiblemente.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    if (type === 'module') formData.append('action', 'delete_module');
    if (type === 'unit') formData.append('action', 'delete_unit');
    if (type === 'lesson') formData.append('action', 'delete_lesson');
    
    fetch('save_builder.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al eliminar: ' + data.error);
        }
    });
}

// Side Content Updating Functions
function updateTips(type, subtype = '') {
    const tipsContainer = document.getElementById('instructionalTips');
    let html = '';
    
    if (type === 'module') {
        html = `<ul class="list-unstyled mb-0 d-grid gap-2 small text-muted">
            <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Los módulos representan las unidades principales o capítulos de tu curso.</li>
            <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Usa nombres cortos y claros de acuerdo a los objetivos de aprendizaje principales.</li>
        </ul>`;
    } else if (type === 'unit') {
        html = `<ul class="list-unstyled mb-0 d-grid gap-2 small text-muted">
            <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Las unidades subdividen un módulo en temáticas específicas e integradas.</li>
            <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Cada unidad debe centrarse en un único resultado conceptual o práctico.</li>
        </ul>`;
    } else if (type === 'lesson') {
        if (subtype === 'video' || subtype === 'youtube' || subtype === 'vimeo') {
            html = `<ul class="list-unstyled mb-0 d-grid gap-2 small text-muted">
                <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>¡Tip: Los videos de más de 8 minutos registran un 40% más de abandono!</li>
                <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Graba en un entorno iluminado y sin ruido de fondo.</li>
            </ul>`;
        } else {
            html = `<ul class="list-unstyled mb-0 d-grid gap-2 small text-muted">
                <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Divide lecturas largas con subtítulos, negritas e imágenes de apoyo.</li>
                <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Incluye enlaces a recursos de la biblioteca del curso al final.</li>
            </ul>`;
        }
    } else if (type === 'activity') {
        html = `<ul class="list-unstyled mb-0 d-grid gap-2 small text-muted">
            <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Las actividades prácticas fomentan la retención del conocimiento.</li>
            <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Define claramente la rúbrica y las expectativas del entregable.</li>
        </ul>`;
    } else if (type === 'evaluation') {
        html = `<ul class="list-unstyled mb-0 d-grid gap-2 small text-muted">
            <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Utiliza evaluaciones formativas para validar el aprendizaje conceptual.</li>
            <li class="d-flex gap-2"><i class="bi bi-lightbulb-fill text-warning mt-1"></i>Una calificación mínima del 60%-70% es el estándar corporativo recomendado.</li>
        </ul>`;
    }
    
    tipsContainer.innerHTML = html;
}

function updatePreview(type, data) {
    const preview = document.getElementById('previewCardContent');
    let html = '';
    
    if (type === 'module' || type === 'unit') {
        html = `<div class="p-3 bg-light rounded-3">
            <h6 class="fw-bold mb-1 text-dark">${escapeHtml(data.nombre)}</h6>
            <span class="badge bg-primary bg-opacity-10 text-primary small">Estructura curricular</span>
        </div>`;
    } else if (type === 'lesson') {
        let typeBadge = `<span class="badge bg-secondary small">${data.tipo}</span>`;
        let embedHtml = '';
        
        if (data.tipo === 'video' || data.tipo === 'youtube' || data.tipo === 'vimeo') {
            embedHtml = `<div class="ratio ratio-16x9 bg-dark rounded-3 mb-2 d-flex align-items-center justify-content-center text-white-50"><i class="bi bi-play-btn display-4"></i></div>`;
        } else if (data.tipo === 'pdf') {
            embedHtml = `<div class="p-3 bg-danger bg-opacity-10 text-danger rounded-3 text-center mb-2"><i class="bi bi-file-pdf display-6"></i><div class="small fw-semibold mt-1">Documento de Lectura PDF</div></div>`;
        } else if (data.tipo === 'codigo') {
            embedHtml = `<div class="p-3 bg-dark text-success rounded-3 font-monospace small mb-2">&lt;code_editor&gt;</div>`;
        }
        
        html = `<div>
            ${embedHtml}
            <h6 class="fw-bold text-dark mb-1">${escapeHtml(data.nombre)}</h6>
            <div class="d-flex gap-2 my-2">${typeBadge}<span class="text-muted small"><i class="bi bi-clock me-1"></i>${data.duracion_segundos || 0}s</span></div>
        </div>`;
    } else if (type === 'activity') {
        html = `<div class="p-3 border rounded-3 bg-success bg-opacity-5">
            <h6 class="fw-bold text-success mb-1"><i class="bi bi-check-circle me-1"></i>${escapeHtml(data.nombre)}</h6>
            <span class="badge bg-success bg-opacity-10 text-success small mb-2">${data.tipo}</span>
            <p class="text-muted small mb-0">Esta sección se convertirá en un buzón de entregas para que el estudiante suba su actividad.</p>
        </div>`;
    } else if (type === 'evaluation') {
        html = `<div class="p-3 border rounded-3 bg-warning bg-opacity-5">
            <h6 class="fw-bold text-warning mb-1"><i class="bi bi-journal-check me-1"></i>${escapeHtml(data.nombre)}</h6>
            <div class="small text-muted mb-2">Intentos: ${data.intentos_permitidos || 1} • Tiempo: ${data.tiempo_limite_minutos || 0}m</div>
            <button class="btn btn-warning text-white btn-sm rounded-pill w-100 mt-2 fw-semibold">Iniciar Evaluación</button>
        </div>`;
    }
    
    preview.innerHTML = html;
}

function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
</script>

<?php
function getLessonIcon($type) {
    switch ($type) {
        case 'video':
        case 'youtube':
        case 'vimeo':
            return 'bi-play-btn-fill';
        case 'pdf':
            return 'bi-file-pdf-fill';
        case 'codigo':
            return 'bi-file-code-fill';
        case 'link':
            return 'bi-link-45deg';
        default:
            return 'bi-file-earmark-text';
    }
}

require_once '../includes/manage/footer.php';
?>
