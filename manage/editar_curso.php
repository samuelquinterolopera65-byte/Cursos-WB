<?php
require_once '../config/db.php';
require_once '../models/Curso.php';
require_once '../models/Usuario.php';

session_start();

// Check authorization for admins and course creators
if (!isset($_SESSION['user_role']) || !in_array((int) $_SESSION['user_role'], [1, 2], true)) {
    header("Location: ../login.php");
    exit;
}

$cursoModel = new Curso($conn);
$usuarioModel = new Usuario($conn);

// Verify specific permission
if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'editar_cursos')) {
    header("Location: index.php?tab=cursos&error=" . urlencode("No tienes permiso para editar cursos."));
    exit;
}

$error = '';
$success = '';
$course = null;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?tab=cursos");
    exit;
}

$id = intval($_GET['id']);

// Fetch course details
try {
    $course = $cursoModel->getById($id);
    
    if (!$course) {
        header("Location: index.php?tab=cursos");
        exit;
    }
} catch (PDOException $e) {
    die("Error al consultar el curso: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $imagen = $course['imagen'];
    $materiales = trim($_POST['materiales']);
    $categoria = trim($_POST['categoria']);
    $cupo_tipo = $_POST['cupo_tipo']; // 'ilimitado' or 'limitado'
    $cupo_limite = null;
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
    
    // Process requested fields
    $campos_req_arr = isset($_POST['campos_req']) ? $_POST['campos_req'] : [];
    array_unshift($campos_req_arr, 'nombre', 'email'); // Always required
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
            $cursoModel->update($id, $titulo, $descripcion, $imagen, $materiales, $categoria, $cupo_limite, $campos_requeridos, $estado);
            $success = 'Curso actualizado con éxito.';
            
            // Reload course info
            $course = $cursoModel->getById($id);
            
            // Redirect after 1 second
            header("refresh:1;url=index.php?tab=cursos");
        } catch (PDOException $e) {
            $error = 'Error al actualizar el curso: ' . $e->getMessage();
        }
    }
}

// Parse current requested fields
$current_campos_arr = explode(',', $course['campos_requeridos']);
$has_phone = in_array('telefono', $current_campos_arr);
$has_age = in_array('edad', $current_campos_arr);
$has_company = in_array('empresa', $current_campos_arr);

$page_title = 'Editar Curso - Panel de Administración';
// Include modular admin layout header
require_once '../includes/manage/header.php';
?>

    <!-- Main Content -->
    <div class="container my-5">

        <!-- Cabecera de página -->
        <div class="edit-course-header p-4 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <a href="index.php?tab=cursos" class="small text-muted text-decoration-none d-inline-flex align-items-center gap-1 mb-2">
                    <i class="bi bi-arrow-left"></i> Volver al panel
                </a>
                <h2 class="fw-bold text-dark mb-1">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Editar Curso
                </h2>
                <p class="text-muted mb-0 small">
                    Modifica el contenido, visibilidad y configuración del curso seleccionado
                </p>
            </div>
            <div class="d-flex gap-2">
                <?php if (!empty($course['id'])): ?>
                    <a href="course_builder.php?course_id=<?php echo $course['id']; ?>"
                       class="btn btn-outline-success btn-sm rounded-pill px-3">
                        <i class="bi bi-diagram-3 me-1"></i>Constructor Visual
                    </a>
                    <a href="../index.php?action=course&id=<?php echo $course['id']; ?>" target="_blank"
                       class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-eye me-1"></i>Ver curso público
                    </a>
                <?php endif; ?>
                <a href="crear_curso.php" class="btn btn-primary-premium btn-sm px-3">
                    <i class="bi bi-plus-circle me-1"></i>Crear nuevo
                </a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?> <em>Redirigiendo...</em>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">

            <!-- Columna principal: formulario -->
            <div class="col-lg-8">
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                    <form action="editar_curso.php?id=<?php echo $course['id']; ?>" method="POST" enctype="multipart/form-data">

                        <div class="mb-4">
                            <label for="titulo" class="form-label fw-bold small">Título del Curso</label>
                            <input type="text" name="titulo" id="titulo" class="form-control form-control-premium"
                                   placeholder="Ej. Master en PHP 8 y Bases de Datos"
                                   value="<?php echo htmlspecialchars($course['titulo']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">Descripción del Curso</label>
                            <div id="editor" style="height: 220px;"></div>
                            <textarea name="descripcion" id="descripcion" rows="4"
                                      class="form-control form-control-premium d-none"
                                      required><?php echo htmlspecialchars($course['descripcion']); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="imagen_file" class="form-label fw-bold small">Imagen de Portada</label>
                            <input type="file" name="imagen_file" id="imagen_file"
                                   class="form-control form-control-premium" accept="image/*">
                            <div class="form-text small text-muted">JPG, PNG, WEBP o GIF. Deja vacío para conservar la imagen actual.</div>
                            <div class="cover-preview-wrapper mt-3">
                                <img id="cover_preview"
                                     src="<?php echo htmlspecialchars($course['imagen']); ?>"
                                     alt="Vista previa de portada"
                                     style="<?php echo empty($course['imagen']) ? 'display:none;' : ''; ?>">
                                <?php if (empty($course['imagen'])): ?>
                                    <div class="text-center text-muted">
                                        <i class="bi bi-image fs-2 mb-2 d-block"></i>
                                        <span class="small">Sin imagen de portada</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="materiales" class="form-label fw-bold small">Materiales del Curso</label>
                            <textarea name="materiales" id="materiales" rows="3"
                                      class="form-control form-control-premium"
                                      placeholder="Escribe un material por línea: PDF, video, repositorio..."><?php echo htmlspecialchars($course['materiales']); ?></textarea>
                            <div class="form-text small text-muted">Cada línea será mostrada como un elemento de lista para los usuarios.</div>
                        </div>

                        <div class="mb-4">
                            <label for="categoria" class="form-label fw-bold small">Categoría del Curso</label>
                            <input type="text" name="categoria" id="categoria"
                                   class="form-control form-control-premium"
                                   placeholder="Ej. Programación, Diseño, Gestión"
                                   value="<?php echo htmlspecialchars($course['categoria'] ?? ''); ?>">
                        </div>

                        <!-- Campos personalizados en registro -->
                        <div class="p-3 bg-light rounded-3 border mb-4">
                            <label class="form-label fw-bold small d-block mb-2 text-secondary">
                                <i class="bi bi-ui-checks me-1"></i>Campos Solicitados en Registro Público
                            </label>
                            <span class="text-muted small d-block mb-3">
                                Nombre y correo son obligatorios. Marca para solicitar datos adicionales:
                            </span>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="campos_req[]"
                                           value="telefono" id="campos_telefono"
                                           <?php echo $has_phone ? 'checked' : ''; ?>>
                                    <label class="form-check-label small fw-bold" for="campos_telefono">
                                        <i class="bi bi-telephone me-1 text-muted"></i>Teléfono
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="campos_req[]"
                                           value="edad" id="campos_edad"
                                           <?php echo $has_age ? 'checked' : ''; ?>>
                                    <label class="form-check-label small fw-bold" for="campos_edad">
                                        <i class="bi bi-person-badge me-1 text-muted"></i>Edad
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="campos_req[]"
                                           value="empresa" id="campos_empresa"
                                           <?php echo $has_company ? 'checked' : ''; ?>>
                                    <label class="form-check-label small fw-bold" for="campos_empresa">
                                        <i class="bi bi-building me-1 text-muted"></i>Empresa / Institución
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Cupo -->
                        <div class="row mb-4 g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small d-block">Tipo de Cupo</label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="cupo_tipo"
                                               id="cupo_ilimitado" value="ilimitado"
                                               <?php echo is_null($course['cupo_limite']) ? 'checked' : ''; ?>
                                               onclick="toggleCupoInput()">
                                        <label class="form-check-label small" for="cupo_ilimitado">Ilimitado</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="cupo_tipo"
                                               id="cupo_limitado" value="limitado"
                                               <?php echo !is_null($course['cupo_limite']) ? 'checked' : ''; ?>
                                               onclick="toggleCupoInput()">
                                        <label class="form-check-label small" for="cupo_limitado">Limitado</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6" id="cupo_numero_container"
                                 style="display: <?php echo is_null($course['cupo_limite']) ? 'none' : 'block'; ?>;">
                                <label for="cupo_limite" class="form-label fw-bold small">Máximo de Alumnos</label>
                                <input type="number" name="cupo_limite" id="cupo_limite"
                                       class="form-control form-control-premium" min="1" placeholder="Ej. 30"
                                       value="<?php echo !is_null($course['cupo_limite']) ? htmlspecialchars($course['cupo_limite']) : ''; ?>"
                                       <?php echo !is_null($course['cupo_limite']) ? 'required' : ''; ?>>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="mb-4">
                            <label for="estado" class="form-label fw-bold small">Visibilidad / Estado</label>
                            <select name="estado" id="estado" class="form-select form-control-premium">
                                <option value="1" <?php echo $course['estado'] == 1 ? 'selected' : ''; ?>>
                                    ✅ Habilitado (Público)
                                </option>
                                <option value="0" <?php echo $course['estado'] == 0 ? 'selected' : ''; ?>>
                                    🔒 Deshabilitado (Borrador)
                                </option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="index.php?tab=cursos" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary-premium px-4">
                                <i class="bi bi-floppy me-1"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Columna lateral: info y acciones -->
            <div class="col-lg-4">
                <div class="edit-sidebar-sticky d-grid gap-3">

                    <!-- Estado actual del curso -->
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.06em; font-size:.7rem;">
                            Estado del curso
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <?php if ($course['estado'] == 1): ?>
                                <span class="badge-estado-publicado"><i class="bi bi-check-circle me-1"></i>Publicado</span>
                            <?php else: ?>
                                <span class="badge-estado-borrador"><i class="bi bi-pencil me-1"></i>Borrador</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($course['created_at'])): ?>
                            <div class="small text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                Creado: <?php echo date('d/m/Y', strtotime($course['created_at'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info de inscripciones -->
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.06em; font-size:.7rem;">
                            Inscripciones
                        </div>
                        <?php
                        $inscritos_count = 0;
                        try {
                            $stmt_ins = $conn->prepare("SELECT COUNT(*) FROM lc_inscripciones WHERE curso_id = ?");
                            $stmt_ins->execute([$course['id']]);
                            $inscritos_count = (int)$stmt_ins->fetchColumn();
                        } catch (Exception $e) {}
                        ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0 text-primary"><?php echo $inscritos_count; ?></h3>
                                <div class="small text-muted">Estudiantes inscritos</div>
                            </div>
                        </div>
                        <?php if (!is_null($course['cupo_limite'])): ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Capacidad utilizada</span>
                                    <span class="fw-bold"><?php echo $inscritos_count; ?>/<?php echo $course['cupo_limite']; ?></span>
                                </div>
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar bg-primary" style="width:<?php echo min(100, round(($inscritos_count / max(1, $course['cupo_limite'])) * 100)); ?>%"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="index.php?tab=inscripciones&curso_id=<?php echo $course['id']; ?>"
                           class="btn btn-outline-primary btn-sm w-100 mt-2 rounded-pill">
                            <i class="bi bi-list-check me-1"></i>Ver inscripciones
                        </a>
                    </div>

                    <!-- Acciones rápidas -->
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.06em; font-size:.7rem;">
                            Acciones
                        </div>
                        <div class="d-grid gap-2">
                            <a href="index.php?tab=cursos" class="btn btn-light btn-sm text-start px-3">
                                <i class="bi bi-arrow-left me-2 text-muted"></i>Volver al listado
                            </a>
                            <a href="crear_curso.php" class="btn btn-light btn-sm text-start px-3">
                                <i class="bi bi-plus-circle me-2 text-success"></i>Crear otro curso
                            </a>
                            <?php if (!empty($course['id'])): ?>
                            <a href="../index.php?action=course_detail&id=<?php echo $course['id']; ?>"
                               target="_blank" class="btn btn-light btn-sm text-start px-3">
                                <i class="bi bi-eye me-2 text-primary"></i>Ver en el sitio público
                            </a>
                            <a href="index.php?action=delete_course&id=<?php echo $course['id']; ?>"
                               class="btn btn-light btn-sm text-start px-3 text-danger"
                               onclick="return confirm('¿Eliminar este curso? Esta acción no se puede deshacer.')">
                                <i class="bi bi-trash me-2"></i>Eliminar curso
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        </div><!-- /row -->
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        const imgInput = document.getElementById('imagen_file');
        const imgPreview = document.getElementById('cover_preview');

        function handlePreview() {
            if (imgInput.files && imgInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                };
                reader.readAsDataURL(imgInput.files[0]);
            } else {
                imgPreview.src = '<?php echo addslashes($course['imagen']); ?>';
                imgPreview.style.display = 'block';
            }
        }

        imgInput.addEventListener('change', handlePreview);

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

        quill.root.innerHTML = <?php echo json_encode($course['descripcion']); ?>;

        document.querySelector('form').addEventListener('submit', function () {
            document.getElementById('descripcion').value = quill.root.innerHTML;
        });

        function toggleCupoInput() {
            var ilimitado = document.getElementById('cupo_ilimitado');
            var cupoContainer = document.getElementById('cupo_numero_container');
            var cupoInput = document.getElementById('cupo_limite');
            
            if (ilimitado.checked) {
                cupoContainer.style.display = 'none';
                cupoInput.removeAttribute('required');
                cupoInput.value = '';
            } else {
                cupoContainer.style.display = 'block';
                cupoInput.setAttribute('required', 'required');
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
