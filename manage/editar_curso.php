<?php
require_once '../config/db.php';
require_once '../models/Curso.php';
require_once '../models/Usuario.php';

session_start();

// Check authorization
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
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
            $cursoModel->update($id, $titulo, $descripcion, $imagen, $materiales, $cupo_limite, $campos_requeridos, $estado);
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Back Link -->
                <div class="mb-4">
                    <a href="index.php?tab=cursos" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i>Volver al Panel de Control</a>
                </div>

                <div class="card border-0 rounded-4 shadow-md p-4 bg-white">
                    <div class="border-bottom pb-3 mb-4">
                        <h2 class="fw-bold text-dark mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Editar Curso</h2>
                        <p class="text-muted mb-0 small">Modifica los detalles del curso seleccionado</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?> <em>Redirigiendo...</em>
                        </div>
                    <?php endif; ?>

                    <form action="editar_curso.php?id=<?php echo $course['id']; ?>" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="titulo" class="form-label fw-bold small">Título del Curso</label>
                            <input type="text" name="titulo" id="titulo" class="form-control form-control-premium" placeholder="Ej. Master en PHP 8 y Bases de Datos" value="<?php echo htmlspecialchars($course['titulo']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-bold small">Descripción del Curso</label>
                            <div id="editor" style="height: 220px;"></div>
                            <textarea name="descripcion" id="descripcion" rows="4" class="form-control form-control-premium d-none" required><?php echo htmlspecialchars($course['descripcion']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="imagen_file" class="form-label fw-bold small">Imagen de Portada</label>
                            <input type="file" name="imagen_file" id="imagen_file" class="form-control form-control-premium" accept="image/*">
                            <div class="form-text small text-muted">Sube una imagen JPG, PNG, WEBP o GIF. Si dejas este campo vacío, se conservará la imagen actual.</div>
                            <div class="mt-3">
                                <span class="d-block small text-muted fw-bold mb-1">Vista Previa de la Portada:</span>
                                <img id="cover_preview" src="<?php echo htmlspecialchars($course['imagen']); ?>" alt="Vista previa" class="img-thumbnail" style="max-height: 180px; max-width: 270px; object-fit: cover;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="materiales" class="form-label fw-bold small">Materiales del Curso</label>
                            <textarea name="materiales" id="materiales" rows="3" class="form-control form-control-premium" placeholder="Ej.
Manual de PDO en PDF
Acceso a repositorio GitHub
Diapositivas de clase"><?php echo htmlspecialchars($course['materiales']); ?></textarea>
                            <div class="form-text small text-muted">Escribe un material por cada línea. Se mostrarán como una lista/tabla a los usuarios.</div>
                        </div>

                        <!-- Dynamic custom fields configuration -->
                        <div class="p-3 bg-light rounded-3 border mb-4">
                            <label class="form-label fw-bold small d-block mb-2 text-secondary"><i class="bi bi-ui-checks me-1"></i>Campos Solicitados en Registro Público</label>
                            <span class="text-muted small d-block mb-3">Nombre completo y Correo Electrónico son obligatorios por defecto. Marca las casillas para solicitar información extra:</span>
                            <div class="form-check form-check-inline me-4">
                                <input class="form-check-input" type="checkbox" name="campos_req[]" value="telefono" id="campos_telefono" <?php echo $has_phone ? 'checked' : ''; ?>>
                                <label class="form-check-label small fw-bold text-dark" for="campos_telefono">Solicitar Teléfono</label>
                            </div>
                            <div class="form-check form-check-inline me-4">
                                <input class="form-check-input" type="checkbox" name="campos_req[]" value="edad" id="campos_edad" <?php echo $has_age ? 'checked' : ''; ?>>
                                <label class="form-check-label small fw-bold text-dark" for="campos_edad">Solicitar Edad</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="campos_req[]" value="empresa" id="campos_empresa" <?php echo $has_company ? 'checked' : ''; ?>>
                                <label class="form-check-label small fw-bold text-dark" for="campos_empresa">Solicitar Empresa / Institución</label>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small d-block">Tipo de Cupo / Capacidad</label>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="cupo_tipo" id="cupo_ilimitado" value="ilimitado" <?php echo is_null($course['cupo_limite']) ? 'checked' : ''; ?> onclick="toggleCupoInput()">
                                    <label class="form-check-label" for="cupo_ilimitado">Ilimitado</label>
                                </div>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="cupo_tipo" id="cupo_limitado" value="limitado" <?php echo !is_null($course['cupo_limite']) ? 'checked' : ''; ?> onclick="toggleCupoInput()">
                                    <label class="form-check-label" for="cupo_limitado">Limitado (Finito)</label>
                                </div>
                            </div>
                            <div class="col-md-6" id="cupo_numero_container" style="display: <?php echo is_null($course['cupo_limite']) ? 'none' : 'block'; ?>;">
                                <label for="cupo_limite" class="form-label fw-bold small">Cantidad Máxima de Alumnos</label>
                                <input type="number" name="cupo_limite" id="cupo_limite" class="form-control form-control-premium" min="1" placeholder="Ej. 15" value="<?php echo !is_null($course['cupo_limite']) ? htmlspecialchars($course['cupo_limite']) : ''; ?>" <?php echo !is_null($course['cupo_limite']) ? 'required' : ''; ?>>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="estado" class="form-label fw-bold small">Visibilidad / Estado</label>
                            <select name="estado" id="estado" class="form-select form-control-premium">
                                <option value="1" <?php echo $course['estado'] == 1 ? 'selected' : ''; ?>>Habilitado (Público)</option>
                                <option value="0" <?php echo $course['estado'] == 0 ? 'selected' : ''; ?>>Deshabilitado (Borrador)</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="index.php?tab=cursos" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary-premium px-4">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
