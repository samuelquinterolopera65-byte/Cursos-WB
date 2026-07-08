<?php
require_once 'config/db.php';
require_once 'models/Curso.php';
require_once 'models/Inscripcion.php';
require_once 'models/Usuario.php';

session_start();

// Inicializar modelos
$cursoModel = new Curso($conn);
$inscripcionModel = new Inscripcion($conn);
$usuarioModel = new Usuario($conn);

$message = '';
$message_type = ''; // 'success', 'danger', 'warning'
$selected_course_id = isset($_GET['curso_id']) ? intval($_GET['curso_id']) : 0;

// Procesar el envío de inscripciones
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_course'])) {
    $curso_id = intval($_POST['curso_id']);
    
    // Verificar si el curso existe y está habilitado
    $curso = $cursoModel->getById($curso_id);
    
    if (!$curso || $curso['estado'] != 1) {
        $message = 'El curso seleccionado no está disponible.';
        $message_type = 'danger';
    } else {
        // Contar las inscripciones actuales
        $current_regs = $inscripcionModel->countByCurso($curso_id);
        
        // Verificar el límite de capacidad
        if (!is_null($curso['cupo_limite']) && $current_regs >= $curso['cupo_limite']) {
            $message = 'Lo sentimos, el curso "' . htmlspecialchars($curso['titulo']) . '" ya está completo.';
            $message_type = 'danger';
        } else {
            $usuario_id = null;
            
            // Determinar el ID del usuario (ya sea conectado o invitado)
            if (isset($_SESSION['user_id'])) {
                $usuario_id = $_SESSION['user_id'];
            } else {
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                
                if (empty($nombre) || empty($email)) {
                    $message = 'Por favor, introduce tu nombre y correo electrónico.';
                    $message_type = 'danger';
                } else {
                    // Verificar si el usuario ya existe
                    $existing_user_id = $usuarioModel->getByEmail($email);
                    
                    if ($existing_user_id) {
                        $usuario_id = $existing_user_id;
                    } else {
                        // Crear un nuevo usuario invitado (Rol: Editor)
                        $usuario_id = $usuarioModel->create($nombre, $email, bin2hex(random_bytes(8)), 2);
                        if (!$usuario_id) {
                            $message = 'Error al crear la cuenta de usuario.';
                            $message_type = 'danger';
                        }
                    }
                }
            }
            
            if ($usuario_id) {
                // Intentar registrar al usuario en el curso
                try {
                    $campos_arr = explode(',', $curso['campos_requeridos']);
                    $telefono = in_array('telefono', $campos_arr) ? trim($_POST['telefono']) : null;
                    $edad = in_array('edad', $campos_arr) ? intval($_POST['edad']) : null;
                    $empresa = in_array('empresa', $campos_arr) ? trim($_POST['empresa']) : null;
                    
                    $inscripcionModel->register($curso_id, $usuario_id, $telefono, $edad, $empresa);
                    $message = '¡Te has registrado con éxito en el curso: ' . htmlspecialchars($curso['titulo']) . '!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    // Verificar si hay un error de entrada duplicada
                    if ($e->getCode() == 23000) {
                        $message = 'Ya te encuentras registrado en el curso: ' . htmlspecialchars($curso['titulo']);
                        $message_type = 'warning';
                    } else {
                        $message = 'Error al registrarse: ' . $e->getMessage();
                        $message_type = 'danger';
                    }
                }
            }
        }
    }
}

// Obtener cursos habilitados
try {
    $cursos = $cursoModel->getEnabled();
} catch (PDOException $e) {
    die("Error al consultar cursos: " . $e->getMessage());
}

// Incluir encabezado del diseño
require_once 'includes/public/header.php';
?>

    <!-- Encabezado hero -->
    <header class="header-premium py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <?php if (!empty($logoPath) && file_exists($logoPath)): ?>
                    <div class="hero-brand">
                        <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Cursos-WB Logo" class="hero-logo">
                        <div class="hero-brand-text">
                            <span>Cursos-WB</span>
                            <span>Tu plataforma de formación online</span>
                        </div>
                    </div>
                <?php endif; ?>
                <h1 class="display-4 fw-bolder mb-3">Lleva tus habilidades al siguiente nivel</h1>
                <p class="lead fw-normal text-white-75 mb-0">Explora y regístrate en nuestros cursos especializados.</p>
            </div>
        </div>
    </header>

    <!-- Sección de cursos -->
    <section class="py-5 courses-section">
        <div class="container px-4 px-lg-5 mt-4">
            
            <!-- Success/Alert Message -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show shadow-sm mb-4 rounded-3" role="alert">
                    <i class="bi <?php echo $message_type == 'success' ? 'bi-check-circle-fill' : ($message_type == 'warning' ? 'bi-exclamation-circle-fill' : 'bi-exclamation-triangle-fill'); ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-md-2 row-cols-xl-3 justify-content-center">
                
                <?php if (count($cursos) > 0): ?>
                    <?php foreach ($cursos as $c): 
                        $inscritos = intval($c['inscritos']);
                        $limite = $c['cupo_limite'];
                        $is_full = !is_null($limite) && $inscritos >= $limite;
                        $remaining = !is_null($limite) ? $limite - $inscritos : null;
                        $descripcion_resumen = htmlspecialchars(strip_tags($c['descripcion']));
                        $descripcion_html = strip_tags($c['descripcion'], '<p><strong><em><u><a><ul><ol><li><br>');
                    ?>
                        <div class="col mb-5" id="curso-<?php echo $c['id']; ?>">
                            <div class="card h-100 course-card">
                                <!-- Capacity Badge -->
                                <div class="position-absolute" style="top: 0.75rem; right: 0.75rem; z-index: 10;">
                                    <?php if ($is_full): ?>
                                        <span class="badge badge-full p-2"><i class="bi bi-slash-circle me-1"></i>Curso Completo</span>
                                    <?php elseif (is_null($limite)): ?>
                                        <span class="badge badge-unlimited p-2"><i class="bi bi-infinity me-1"></i>Cupos Ilimitados</span>
                                    <?php else: ?>
                                        <span class="badge badge-spots p-2"><i class="bi bi-people me-1"></i><?php echo $remaining; ?> cupos disponibles</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Course Image -->
                                <img class="card-img-top" src="<?php echo htmlspecialchars($c['imagen']); ?>" alt="<?php echo htmlspecialchars($c['titulo']); ?>" />
                                
                                <!-- Course Details -->
                                <div class="card-body p-4">
                                    <div class="text-start">
                                        <!-- Course Title -->
                                        <h5 class="fw-bold text-dark text-truncate mb-2"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                                        <!-- Course Description -->
                                        <p class="text-muted small text-clamp-3 mb-0" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; min-height: 4.5em;">
                                            <?php echo $descripcion_resumen; ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Course Actions -->
                                <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                    <div class="text-center d-grid">
                                        <button class="btn btn-primary-premium" data-bs-toggle="modal" data-bs-target="#courseModal-<?php echo $c['id']; ?>">
                                            <i class="bi bi-info-circle me-1"></i>Ver Detalles e Inscribirse
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ================= COURSE MODAL ================= -->
                        <div class="modal fade" id="courseModal-<?php echo $c['id']; ?>" tabindex="-1" aria-labelledby="modalLabel-<?php echo $c['id']; ?>">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content modal-content-premium p-3">
                                    <div class="modal-header border-0 pb-0">
                                        <h4 class="fw-bold modal-title" id="modalLabel-<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['titulo']); ?></h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <!-- Image & Description -->
                                            <div class="col-md-6 mb-4">
                                                <img src="<?php echo htmlspecialchars($c['imagen']); ?>" class="img-fluid rounded shadow-sm mb-3 w-100" style="height: 200px; object-fit: cover;" alt="">
                                                <h6 class="fw-bold text-secondary">Descripción</h6>
                                                <div class="text-muted small"><?php echo $descripcion_html; ?></div>
                                            </div>
                                            <!-- Materials & Registration -->
                                            <div class="col-md-6">
                                                <!-- Materials Table -->
                                                <h6 class="fw-bold text-secondary mb-2"><i class="bi bi-folder2-open me-2"></i>Materiales Incluidos</h6>
                                                <div class="table-responsive mb-4" style="max-height: 180px; overflow-y: auto;">
                                                    <table class="table table-striped table-hover small custom-table">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" style="width: 10%;">#</th>
                                                                <th scope="col">Material / Recurso</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php 
                                                            $materialsList = preg_split('/\r\n|\r|\n/', $c['materiales']);
                                                            $counter = 1;
                                                            foreach ($materialsList as $mat) {
                                                                $mat = trim($mat);
                                                                if (!empty($mat)) {
                                                                    echo "<tr><td>{$counter}</td><td>" . htmlspecialchars($mat) . "</td></tr>";
                                                                    $counter++;
                                                                }
                                                            }
                                                            if ($counter == 1) {
                                                                echo "<tr><td colspan='2' class='text-muted text-center'>No se especificaron materiales.</td></tr>";
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Registration Section -->
                                                <div class="p-3 bg-light rounded-3 border">
                                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-journal-check me-2 text-primary"></i>Inscripción al Curso</h6>
                                                    
                                                    <?php if ($is_full): ?>
                                                        <div class="alert alert-danger mb-0 small text-center rounded-3">
                                                            <i class="bi bi-slash-circle-fill me-1"></i><strong>Curso completo</strong><br>Las inscripciones están bloqueadas ya que se alcanzó el límite de alumnos.
                                                        </div>
                                                    <?php else: ?>
                                                        <form action="index.php" method="POST">
                                                            <input type="hidden" name="register_course" value="1">
                                                            <input type="hidden" name="curso_id" value="<?php echo $c['id']; ?>">
                                                            
                                                            <?php 
                                                            $campos_arr = explode(',', $c['campos_requeridos']);
                                                            $req_phone = in_array('telefono', $campos_arr);
                                                            $req_age = in_array('edad', $campos_arr);
                                                            $req_company = in_array('empresa', $campos_arr);
                                                            ?>
                                                            <div class="mb-2">
                                                                <label for="reg_nombre-<?php echo $c['id']; ?>" class="form-label small fw-bold mb-1">Nombre Completo</label>
                                                                <input type="text" name="nombre" id="reg_nombre-<?php echo $c['id']; ?>" class="form-control form-control-sm form-control-premium" placeholder="Ej. Juan Pérez" value="" required>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label for="reg_email-<?php echo $c['id']; ?>" class="form-label small fw-bold mb-1">Correo Electrónico</label>
                                                                <input type="email" name="email" id="reg_email-<?php echo $c['id']; ?>" class="form-control form-control-sm form-control-premium" placeholder="juan@correo.com" value="" required>
                                                            </div>
                                                            <?php if ($req_phone): ?>
                                                                <div class="mb-2">
                                                                    <label for="reg_telefono-<?php echo $c['id']; ?>" class="form-label small fw-bold mb-1">Teléfono</label>
                                                                    <input type="text" name="telefono" id="reg_telefono-<?php echo $c['id']; ?>" class="form-control form-control-sm form-control-premium" placeholder="Ej. +56 9 1234 5678" required>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if ($req_age): ?>
                                                                <div class="mb-2">
                                                                    <label for="reg_edad-<?php echo $c['id']; ?>" class="form-label small fw-bold mb-1">Edad</label>
                                                                    <input type="number" name="edad" id="reg_edad-<?php echo $c['id']; ?>" class="form-control form-control-sm form-control-premium" placeholder="Ej. 25" min="1" max="120" required>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if ($req_company): ?>
                                                                <div class="mb-3">
                                                                    <label for="reg_empresa-<?php echo $c['id']; ?>" class="form-label small fw-bold mb-1">Empresa / Institución</label>
                                                                    <input type="text" name="empresa" id="reg_empresa-<?php echo $c['id']; ?>" class="form-control form-control-sm form-control-premium" placeholder="Ej. Universidad de Santiago" required>
                                                                </div>
                                                            <?php endif; ?>
                                                            <button type="submit" class="btn btn-primary-premium btn-sm w-100 py-2"><i class="bi bi-pencil-square me-1"></i>Registrarme e Inscribirme</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="text-muted fs-5"><i class="bi bi-info-circle me-2"></i>No hay cursos habilitados en este momento.</div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <!-- Script para abrir automáticamente el modal del curso si se pasa su ID en la URL -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const cursoId = urlParams.get('curso_id');
            if (cursoId) {
                const courseCard = document.getElementById('curso-' + cursoId);
                const modalElement = document.getElementById('courseModal-' + cursoId);

                if (courseCard) {
                    courseCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    setTimeout(function() {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                        const firstInput = modalElement.querySelector('input[name="nombre"]');
                        if (firstInput) {
                            firstInput.focus();
                        }
                    }, 220);
                }
            }
        });
    </script>

<?php
// Incluir el pie de página del diseño
require_once 'includes/public/footer.php';
?>
