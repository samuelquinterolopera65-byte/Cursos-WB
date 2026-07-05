<?php
require_once '../config/db.php';
require_once '../models/Curso.php';
require_once '../models/Usuario.php';
require_once '../models/Inscripcion.php';
require_once '../models/Servicio.php';
require_once '../models/Ajustes.php';

session_start();

// Verify user is administrator
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit;
}

// Initialize models
$cursoModel = new Curso($conn);
$usuarioModel = new Usuario($conn);
$inscripcionModel = new Inscripcion($conn);
$servicioModel = new Servicio($conn);
$ajustesModel = new Ajustes($conn);

// Log user activity
$usuarioModel->updateLastAccess($_SESSION['user_id']);

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
$error = '';
$success = '';

// Handle actions (GET)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    try {
        if ($action == 'toggle_course_status' && $id > 0) {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'editar_cursos')) {
                $error = 'No tienes permiso para editar cursos.';
            } else {
                $cursoModel->toggleStatus($id);
                $success = 'Estado del curso cambiado con éxito.';
            }
            header("Location: index.php?tab=cursos&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        } 
        elseif ($action == 'delete_course' && $id > 0) {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'eliminar_cursos')) {
                $error = 'No tienes permiso para eliminar cursos.';
            } else {
                $cursoModel->delete($id);
                $success = 'Curso eliminado con éxito.';
            }
            header("Location: index.php?tab=cursos&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }
        elseif ($action == 'delete_user' && $id > 0) {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_usuarios')) {
                $error = 'No tienes permiso para gestionar usuarios.';
            } elseif ($id == $_SESSION['user_id']) {
                $error = 'No puedes eliminar tu propio usuario.';
            } else {
                $usuarioModel->delete($id);
                $success = 'Usuario eliminado con éxito.';
            }
            header("Location: index.php?tab=usuarios&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }
        elseif ($action == 'delete_service' && $id > 0) {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_servicios')) {
                $error = 'No tienes permiso para gestionar roles y servicios.';
            } else {
                $servicioModel->delete($id);
                $success = 'Servicio eliminado con éxito.';
            }
            header("Location: index.php?tab=servicios&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }
        elseif ($action == 'clear_logo') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_ajustes')) {
                $error = 'No tienes permiso para gestionar ajustes.';
            } else {
                $currentLogo = $ajustesModel->get('logo');
                if (!empty($currentLogo) && file_exists('../' . $currentLogo)) {
                    unlink('../' . $currentLogo); // Delete old file from directory
                }
                $ajustesModel->set('logo', '');
                $success = 'Logo restablecido por defecto.';
            }
            header("Location: index.php?tab=ajustes&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }
    } catch (PDOException $e) {
        $error = 'Error al procesar la acción: ' . $e->getMessage();
    }
}

// Handle Form Submissions (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        // Add User
        if ($action == 'add_user') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_usuarios')) {
                $error = 'No tienes permiso para crear usuarios.';
            } else {
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $rol_id = intval($_POST['rol_id']);
                $permisos_arr = isset($_POST['permisos']) ? $_POST['permisos'] : [];
                $permisos = implode(',', $permisos_arr);

                if (empty($nombre) || empty($email) || empty($password)) {
                    $error = 'Todos los campos son obligatorios.';
                } else {
                    $usuarioModel->create($nombre, $email, $password, $rol_id, $permisos);
                    $success = 'Usuario creado con éxito.';
                }
            }
            header("Location: index.php?tab=usuarios&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }
        
        // Edit User
        elseif ($action == 'edit_user') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_usuarios')) {
                $error = 'No tienes permiso para modificar usuarios.';
            } else {
                $id = intval($_POST['id']);
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                $rol_id = intval($_POST['rol_id']);
                $password = $_POST['password'];
                $permisos_arr = isset($_POST['permisos']) ? $_POST['permisos'] : [];
                $permisos = implode(',', $permisos_arr);

                if (empty($nombre) || empty($email)) {
                    $error = 'Nombre y correo son obligatorios.';
                } else {
                    $usuarioModel->update($id, $nombre, $email, $rol_id, $password, $permisos);
                    $success = 'Usuario actualizado con éxito.';
                }
            }
            header("Location: index.php?tab=usuarios&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }

        // Add Service
        elseif ($action == 'add_service') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_servicios')) {
                $error = 'No tienes permiso para gestionar servicios.';
            } else {
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                $precio = floatval($_POST['precio']);

                if (empty($nombre)) {
                    $error = 'El nombre del servicio es obligatorio.';
                } else {
                    $servicioModel->create($nombre, $descripcion, $precio);
                    $success = 'Servicio creado con éxito.';
                }
            }
            header("Location: index.php?tab=servicios&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }

        // Edit Service
        elseif ($action == 'edit_service') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_servicios')) {
                $error = 'No tienes permiso para editar servicios.';
            } else {
                $id = intval($_POST['id']);
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                $precio = floatval($_POST['precio']);

                if (empty($nombre)) {
                    $error = 'El nombre del servicio es obligatorio.';
                } else {
                    $servicioModel->update($id, $nombre, $descripcion, $precio);
                    $success = 'Servicio actualizado con éxito.';
                }
            }
            header("Location: index.php?tab=servicios&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }

        // Update Logo (Ajustes)
        elseif ($action == 'update_logo') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_ajustes')) {
                $error = 'No tienes permiso para gestionar ajustes.';
            } else {
                if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] == 0) {
                    $allowed_extensions = ['png', 'jpg', 'jpeg', 'gif', 'svg'];
                    $file_name = $_FILES['logo_file']['name'];
                    $file_size = $_FILES['logo_file']['size'];
                    $file_tmp = $_FILES['logo_file']['tmp_name'];
                    
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed_extensions)) {
                        // Check file size (max 2MB)
                        if ($file_size <= 2 * 1024 * 1024) {
                            // Ensure uploads directory exists relative to root
                            if (!is_dir('../uploads')) {
                                mkdir('../uploads', 0777, true);
                            }
                            
                            // Remove old logo file if exists
                            $oldLogo = $ajustesModel->get('logo');
                            if (!empty($oldLogo) && file_exists('../' . $oldLogo)) {
                                unlink('../' . $oldLogo);
                            }
                            
                            // Set new unique filename and path
                            $new_file_name = 'logo_' . time() . '.' . $ext;
                            $dest_path = '../uploads/' . $new_file_name;
                            
                            if (move_uploaded_file($file_tmp, $dest_path)) {
                                $ajustesModel->set('logo', 'uploads/' . $new_file_name);
                                $success = 'Logo del sitio actualizado con éxito.';
                            } else {
                                $error = 'Error al mover el archivo al directorio de destino.';
                            }
                        } else {
                            $error = 'El archivo supera el límite de tamaño de 2MB.';
                        }
                    } else {
                        $error = 'Extensión de archivo no permitida. Use PNG, JPG, JPEG, GIF o SVG.';
                    }
                } else {
                    $error = 'No se ha seleccionado ningún archivo o ocurrió un error al subir.';
                }
            }
            header("Location: index.php?tab=ajustes&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }
    } catch (PDOException $e) {
        $error = 'Error en base de datos: ' . $e->getMessage();
        header("Location: index.php?tab=" . $tab . "&error=" . urlencode($error));
        exit;
    }
}

// Get success/error alerts from redirection
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Fetch dashboard data
try {
    // Metrics
    $total_courses = $cursoModel->countAll();
    $active_courses = $cursoModel->countActive();
    $total_registrations = $inscripcionModel->countAll();
    $online_users = $usuarioModel->countOnline();

    // Obtener los últimos 10 usuarios registrados (filtrado opcional por curso)
    $dashboard_curso_id = isset($_GET['dashboard_curso_id']) && $_GET['dashboard_curso_id'] !== 'all' ? intval($_GET['dashboard_curso_id']) : null;
    $last_registrations = $inscripcionModel->getLastTen($dashboard_curso_id);

    // Fetch lists depending on the active tab
    $cursos = [];
    $usuarios = [];
    $roles = [];
    $servicios = [];
    $registros = [];
    $logoPath = '';
    
    // Always fetch active courses to populate selects/filters
    $all_active_courses = $cursoModel->getEnabled();

    if ($tab == 'cursos') {
        $cursos = $cursoModel->getAll();
    } elseif ($tab == 'usuarios') {
        $usuarios = $usuarioModel->getAll();
        $roles = $usuarioModel->getRoles();
    } elseif ($tab == 'servicios') {
        $servicios = $servicioModel->getAll();
        $roles = $usuarioModel->getRoles();
        $usuarios = $usuarioModel->getAll();
    } elseif ($tab == 'inscripciones') {
        $filter_curso_id = isset($_GET['curso_id']) && $_GET['curso_id'] !== 'all' ? intval($_GET['curso_id']) : null;
        $registros = $inscripcionModel->getAll($filter_curso_id);
    } elseif ($tab == 'ajustes') {
        $logoPath = $ajustesModel->get('logo');
    }

} catch (PDOException $e) {
    $error = 'Error al cargar datos: ' . $e->getMessage();
}

$page_title = 'Panel de Administración - Cursos-WB';
// Include modular admin layout header
require_once '../includes/manage/header.php';
?>

    <!-- Main Container -->
    <div class="container my-5">
        
        <!-- Success and Error Alerts -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            
            <!-- Left Sidebar Navigation Include -->
            <div class="col-lg-3 mb-4">
                <?php require_once '../includes/manage/sidebar.php'; ?>
            </div>

            <!-- Right Content Area -->
            <div class="col-lg-9">
                
                <!-- ================= TAB: DASHBOARD ================= -->
                <?php if ($tab == 'dashboard'): ?>
                    <h3 class="fw-bold text-dark mb-4">Panel de Control</h3>
                    
                    <!-- Cuadrícula de Estadísticas -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-4 col-sm-6">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Cursos</h6>
                                        <h2 class="fw-bold mb-0 text-primary"><?php echo $total_courses; ?></h2>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                        <i class="bi bi-journal-text fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <span><?php echo $active_courses; ?> visibles en el sitio</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Usuarios</h6>
                                        <h2 class="fw-bold mb-0 text-success"><?php echo $total_registrations; ?></h2>
                                    </div>
                                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                        <i class="bi bi-people-fill fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <span>Total de Usuarios en la Pagina</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">En Línea</h6>
                                        <h2 class="fw-bold mb-0 text-teal" style="color: var(--accent-color);"><?php echo $online_users; ?></h2>
                                    </div>
                                    <div class="bg-info bg-opacity-10 text-teal rounded-3 p-3" style="color: var(--accent-color); background-color: rgba(13, 148, 136, 0.1);">
                                        <i class="bi bi-wifi fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <span>Usuarios activos últ. 5 min</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de 10 Últimos Usuarios Registrados -->
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>10 Últimos Usuarios Registrados</h5>
                            <div class="d-flex align-items-center gap-2">
                                <label for="filter_curso_dashboard" class="small fw-bold text-muted mb-0">Filtrar por curso:</label>
                                <select id="filter_curso_dashboard" class="form-select form-select-sm" style="width: auto; max-width: 200px;" onchange="filterDashboardCourse(this.value)">
                                    <option value="all">Todos los cursos</option>
                                    <?php foreach ($all_active_courses as $ac): ?>
                                        <option value="<?php echo $ac['id']; ?>" <?php echo ($dashboard_curso_id == $ac['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ac['titulo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <a href="index.php?tab=inscripciones" class="btn btn-sm btn-outline-primary px-3 rounded-pill">Ver todos</a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Participante</th>
                                        <th>Curso</th>
                                        <th>Fecha Registro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($last_registrations) > 0): ?>
                                        <?php foreach ($last_registrations as $reg): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo htmlspecialchars($reg['user_name']); ?></td>
                                                <td><span class="badge bg-light text-dark p-2 border"><?php echo htmlspecialchars($reg['course_title']); ?></span></td>
                                                <td class="text-muted small"><?php echo htmlspecialchars($reg['fecha_inscripcion']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No hay usuarios registrados en este curso todavía.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ================= TAB: CURSOS ================= -->
                <?php if ($tab == 'cursos'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold text-dark mb-0">Gestión de Cursos</h3>
                        <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')): ?>
                            <a href="crear_curso.php" class="btn btn-primary-premium"><i class="bi bi-plus-circle me-1"></i>Crear Curso</a>
                        <?php endif; ?>
                    </div>

                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Imagen</th>
                                        <th>Título</th>
                                        <th>Capacidad</th>
                                        <th>Inscritos</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($cursos) > 0): ?>
                                        <?php foreach ($cursos as $c): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars(strpos($c['imagen'], 'http') === 0 ? $c['imagen'] : '../' . $c['imagen']); ?>" class="rounded" style="width: 60px; height: 40px; object-fit: cover;" alt="...">
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($c['titulo']); ?></div>
                                                    <div class="text-muted small text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($c['descripcion']); ?></div>
                                                </td>
                                                <td>
                                                    <?php if (is_null($c['cupo_limite'])): ?>
                                                        <span class="badge badge-unlimited">Ilimitado</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-spots">Límite: <?php echo $c['cupo_limite']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="fw-bold"><?php echo $c['inscritos']; ?></span>
                                                    <?php if (!is_null($c['cupo_limite']) && $c['inscritos'] >= $c['cupo_limite']): ?>
                                                        <span class="badge bg-danger ms-1">Lleno</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($c['estado'] == 1): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success p-2">Habilitado</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning bg-opacity-10 text-warning p-2">Deshabilitado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group gap-1">
                                                        <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'editar_cursos')): ?>
                                                            <a href="index.php?action=toggle_course_status&id=<?php echo $c['id']; ?>" class="btn btn-sm <?php echo $c['estado'] == 1 ? 'btn-outline-warning' : 'btn-outline-success'; ?>" title="<?php echo $c['estado'] == 1 ? 'Deshabilitar' : 'Habilitar'; ?>">
                                                                <i class="bi <?php echo $c['estado'] == 1 ? 'bi-eye-slash' : 'bi-eye'; ?>"></i>
                                                            </a>
                                                            <a href="editar_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="descargar_qr.php?id=<?php echo $c['id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Descargar flyer QR">
                                                            <i class="bi bi-upc-scan"></i>
                                                        </a>
                                                        <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'eliminar_cursos')): ?>
                                                            <a href="index.php?action=delete_course&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este curso? Se borrarán todas las inscripciones asociadas.');">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No hay cursos registrados.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ================= TAB: USUARIOS ================= -->
                <?php if ($tab == 'usuarios'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold text-dark mb-0">Gestión de Usuarios</h3>
                        <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_usuarios')): ?>
                            <button class="btn btn-primary-premium" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus me-1"></i>Crear Usuario</button>
                        <?php endif; ?>
                    </div>

                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Actividad</th>
                                        <th>Fecha Registro</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($usuarios) > 0): ?>
                                        <?php foreach ($usuarios as $u): 
                                            $isActiveOnline = (strtotime($u['ultimo_acceso']) >= strtotime('-5 minutes'));
                                        ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo htmlspecialchars($u['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                <td>
                                                    <?php if ($u['rol_id'] == 1): ?>
                                                        <span class="badge bg-danger bg-opacity-10 text-danger p-2"><i class="bi bi-shield-fill-check me-1"></i><?php echo htmlspecialchars($u['rol_nombre']); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary p-2"><?php echo htmlspecialchars($u['rol_nombre']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($isActiveOnline): ?>
                                                        <span class="badge bg-success p-1.5"><span class="spinner-grow spinner-grow-sm me-1" role="status" style="width: 8px; height: 8px;"></span>En línea</span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Últ. acceso: <?php echo date('d/m H:i', strtotime($u['ultimo_acceso'])); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-muted small"><?php echo htmlspecialchars($u['fecha_registro']); ?></td>
                                                <td class="text-end">
                                                    <div class="btn-group gap-1">
                                                        <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_usuarios')): ?>
                                                            <button class="btn btn-sm btn-outline-primary" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#editUserModal"
                                                                    data-bs-id="<?php echo $u['id']; ?>"
                                                                    data-bs-nombre="<?php echo htmlspecialchars($u['nombre']); ?>"
                                                                    data-bs-email="<?php echo htmlspecialchars($u['email']); ?>"
                                                                    data-bs-rol="<?php echo $u['rol_id']; ?>"
                                                                    data-bs-permisos="<?php echo htmlspecialchars($u['permisos']); ?>"
                                                                    title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <a href="index.php?action=delete_user&id=<?php echo $u['id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger <?php echo $u['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>" 
                                                               title="Eliminar" 
                                                               onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No hay usuarios registrados.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ================= PESTAÑA: ROLES Y PERMISOS ================= -->
                <?php if ($tab == 'servicios'): ?>
                    <h4 class="fw-bold text-dark mb-3">Roles y Permisos del Sistema</h4>
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <h5 class="fw-semibold mb-4">Permisos de administrador</h5>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $u): ?>
                                <?php if (!in_array($u['rol_id'], [1, 2])) continue; ?>
                                <?php $assignedPerms = array_filter(array_map('trim', explode(',', $u['permisos']))); ?>
                                <?php $roleLabel = htmlspecialchars($u['rol_id'] == 1 ? 'Administrador' : 'Editor'); ?>
                                <form class="permissions-form mb-4" data-user-id="<?php echo $u['id']; ?>">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="fw-bold text-secondary">Permisos de <?php echo $roleLabel; ?></div>
                                                <span class="badge bg-<?php echo $u['rol_id'] == 1 ? 'danger' : 'secondary'; ?> bg-opacity-10 text-<?php echo $u['rol_id'] == 1 ? 'danger' : 'secondary'; ?> py-2 px-3 rounded-pill"><?php echo $roleLabel; ?></span>
                                            </div>
                                            <div class="row g-2">
                                                <?php $permissionsList = [
                                                    'crear_cursos' => 'Crear Cursos',
                                                    'editar_cursos' => 'Editar Cursos',
                                                    'eliminar_cursos' => 'Eliminar Cursos',
                                                    'gestionar_usuarios' => 'Gestionar Usuarios',
                                                    'gestionar_servicios' => 'Gestionar Roles y Servicios',
                                                    'descargar_excel' => 'Descargar Excel',
                                                    'gestionar_ajustes' => 'Gestionar Ajustes / Logo'
                                                ]; ?>
                                                <?php foreach ($permissionsList as $permKey => $permLabel): ?>
                                                    <div class="col-6 col-lg-4">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" name="permisos[]" value="<?php echo $permKey; ?>" id="perm_<?php echo $permKey . '_' . $u['id']; ?>" <?php echo in_array($permKey, $assignedPerms) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label small" for="perm_<?php echo $permKey . '_' . $u['id']; ?>"><?php echo $permLabel; ?></label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-sm btn-primary save-permissions-btn"><i class="bi bi-save me-1"></i>Guardar permisos</button>
                                            <div class="permissions-alert mt-2"></div>
                                        </div>
                                    </div>
                                </form>
                            <?php endforeach; ?>
                            <?php if (count(array_filter($usuarios, fn($user) => in_array($user['rol_id'], [1, 2]))) === 0): ?>
                                <div class="alert alert-warning mb-0">No hay administradores ni editores registrados para asignar permisos.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">No hay usuarios registrados en el sistema.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- ================= PESTAÑA: INSCRIPCIONES ================= -->
                <?php if ($tab == 'inscripciones'): ?>
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <h3 class="fw-bold text-dark mb-0">Usuarios Registrados en Cursos</h3>
                        
                        <div class="d-flex align-items-center bg-white p-2 rounded-3 border shadow-sm gap-2">
                            <label for="filter_curso_inscripciones" class="small fw-bold text-muted mb-0">Filtrar por curso:</label>
                            <select id="filter_curso_inscripciones" class="form-select form-select-sm" style="width: auto; max-width: 250px;" onchange="filterInscripcionesCourse(this.value)">
                                <option value="all">Todos los inscritos</option>
                                <?php foreach ($all_active_courses as $ac): ?>
                                    <option value="<?php echo $ac['id']; ?>" <?php echo (isset($filter_curso_id) && $filter_curso_id == $ac['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($ac['titulo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            
                            <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'descargar_excel')): ?>
                                <button type="button" class="btn btn-sm btn-success px-3" onclick="triggerExcelDownload()">
                                    <i class="bi bi-file-earmark-excel me-1"></i>Descargar Excel (CSV)
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Participante</th>
                                        <th>Curso</th>
                                        <th>Privacidad</th>
                                        <th>Fecha Inscripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($registros) > 0): ?>
                                        <?php foreach ($registros as $reg): ?>
                                            <tr>
                                                <td>#<?php echo $reg['inscripcion_id']; ?></td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($reg['usuario_nombre']); ?></td>
                                                <td><span class="badge bg-light text-dark p-2 border"><?php echo htmlspecialchars($reg['curso_titulo']); ?></span></td>
                                                <td class="small text-muted">Datos protegidos y ocultos para privacidad.</td>
                                                <td class="text-muted small"><?php echo htmlspecialchars($reg['fecha_registro']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No hay registros de usuarios en cursos.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ================= TAB: AJUSTES (LOGO UPLOAD) ================= -->
                <?php if ($tab == 'ajustes'): ?>
                    <h3 class="fw-bold text-dark mb-4">Ajustes del Sitio</h3>
                    
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <h5 class="fw-bold mb-3"><i class="bi bi-palette text-primary me-2"></i>Personalización y Logo</h5>
                        <p class="text-muted small">Cambia la identidad visual de la plataforma subiendo tu propio logo.</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-5 mb-4 text-center">
                                <div class="p-4 border rounded-3 bg-light d-flex flex-column align-items-center justify-content-center" style="min-height: 200px;">
                                    <span class="text-secondary small fw-bold mb-3 d-block">Logo Actual</span>
                                    <?php if (!empty($logoPath) && file_exists('../' . $logoPath)): ?>
                                        <img src="../<?php echo htmlspecialchars($logoPath); ?>" alt="Logo actual" class="img-fluid mb-3" style="max-height: 60px; max-width: 180px; object-fit: contain;">
                                        <span class="badge bg-success p-2">Personalizado</span>
                                        <div class="mt-3">
                                            <a href="index.php?action=clear_logo" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Deseas restablecer el logo por defecto?');"><i class="bi bi-trash me-1"></i>Restablecer por defecto</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-2">
                                            <i class="bi bi-journal-bookmark-fill fs-1"></i>
                                        </div>
                                        <span class="fw-bold fs-5 text-primary">Cursos-WB</span>
                                        <span class="badge bg-secondary p-2 mt-2">Por Defecto (Texto)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-7">
                                <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_ajustes')): ?>
                                    <form action="index.php?tab=ajustes" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="update_logo">
                                        
                                        <div class="mb-3">
                                            <label for="logo_file" class="form-label fw-bold small">Seleccionar archivo de imagen</label>
                                            <input class="form-control form-control-premium" type="file" id="logo_file" name="logo_file" accept=".png, .jpg, .jpeg, .gif, .svg" required>
                                            <div class="form-text small text-muted">Formatos admitidos: PNG, JPG, JPEG, GIF, SVG. Tamaño máximo sugerido: 2MB.</div>
                                        </div>
                                        
                                        <div class="d-grid mt-4">
                                            <button type="submit" class="btn btn-primary-premium"><i class="bi bi-upload me-1"></i>Subir y Guardar Logo</button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-warning">No tienes permisos para modificar el logo del sitio.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- ================= MODALS ================= -->

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-premium p-3">
                <div class="modal-header border-0">
                    <h5 class="fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Crear Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?tab=usuarios" method="POST">
                    <div class="modal-body border-0">
                        <input type="hidden" name="action" value="add_user">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_user_nombre" class="form-label small fw-bold">Nombre Completo</label>
                                <input type="text" name="nombre" id="add_user_nombre" class="form-control form-control-premium" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_user_email" class="form-label small fw-bold">Correo Electrónico</label>
                                <input type="email" name="email" id="add_user_email" class="form-control form-control-premium" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_user_password" class="form-label small fw-bold">Contraseña</label>
                                <input type="password" name="password" id="add_user_password" class="form-control form-control-premium" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_user_rol_id" class="form-label small fw-bold">Rol</label>
                                <select name="rol_id" id="add_user_rol_id" class="form-select form-control-premium" required>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- ACL Permissions Checklist -->
                        <div class="p-3 bg-light rounded-3 border mt-2">
                            <label class="form-label small fw-bold d-block mb-2 text-secondary"><i class="bi bi-key-fill me-1"></i>Asignar Permisos Administrativos</label>
                            <span class="text-muted small d-block mb-3">Concede individualmente los permisos de ejecución para este usuario (solo aplica a administradores):</span>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="crear_cursos" id="add_perm_crear_cursos" checked>
                                        <label class="form-check-label small" for="add_perm_crear_cursos">Crear Cursos</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="editar_cursos" id="add_perm_editar_cursos" checked>
                                        <label class="form-check-label small" for="add_perm_editar_cursos">Editar Cursos</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="eliminar_cursos" id="add_perm_eliminar_cursos" checked>
                                        <label class="form-check-label small" for="add_perm_eliminar_cursos">Eliminar Cursos</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="gestionar_usuarios" id="add_perm_gestionar_usuarios">
                                        <label class="form-check-label small" for="add_perm_gestionar_usuarios">Gestionar Usuarios</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="gestionar_servicios" id="add_perm_gestionar_servicios">
                                        <label class="form-check-label small" for="add_perm_gestionar_servicios">Gestionar Roles y Servicios</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="descargar_excel" id="add_perm_descargar_excel" checked>
                                        <label class="form-check-label small" for="add_perm_descargar_excel">Descargar Excel</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="gestionar_ajustes" id="add_perm_gestionar_ajustes">
                                        <label class="form-check-label small" for="add_perm_gestionar_ajustes">Gestionar Ajustes / Logo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-premium">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-premium p-3">
                <div class="modal-header border-0">
                    <h5 class="fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="index.php?tab=usuarios" method="POST">
                    <div class="modal-body border-0">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="id" id="edit_user_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_user_nombre" class="form-label small fw-bold">Nombre Completo</label>
                                <input type="text" name="nombre" id="edit_user_nombre" class="form-control form-control-premium" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_user_email" class="form-label small fw-bold">Correo Electrónico</label>
                                <input type="email" name="email" id="edit_user_email" class="form-control form-control-premium" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_user_password" class="form-label small fw-bold">Contraseña <span class="text-muted fw-normal">(Dejar en blanco para no cambiar)</span></label>
                                <input type="password" name="password" id="edit_user_password" class="form-control form-control-premium" placeholder="••••••••">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_user_rol_id" class="form-label small fw-bold">Rol</label>
                                <select name="rol_id" id="edit_user_rol_id" class="form-select form-control-premium" required>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- ACL Permissions Checklist -->
                        <div class="p-3 bg-light rounded-3 border mt-2">
                            <label class="form-label small fw-bold d-block mb-2 text-secondary"><i class="bi bi-key-fill me-1"></i>Asignar Permisos Administrativos</label>
                            <span class="text-muted small d-block mb-3">Modifica los permisos de ejecución para este administrador:</span>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="crear_cursos" id="edit_perm_crear_cursos">
                                        <label class="form-check-label small" for="edit_perm_crear_cursos">Crear Cursos</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="editar_cursos" id="edit_perm_editar_cursos">
                                        <label class="form-check-label small" for="edit_perm_editar_cursos">Editar Cursos</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="eliminar_cursos" id="edit_perm_eliminar_cursos">
                                        <label class="form-check-label small" for="edit_perm_eliminar_cursos">Eliminar Cursos</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="gestionar_usuarios" id="edit_perm_gestionar_usuarios">
                                        <label class="form-check-label small" for="edit_perm_gestionar_usuarios">Gestionar Usuarios</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="gestionar_servicios" id="edit_perm_gestionar_servicios">
                                        <label class="form-check-label small" for="edit_perm_gestionar_servicios">Gestionar Roles y Servicios</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="descargar_excel" id="edit_perm_descargar_excel">
                                        <label class="form-check-label small" for="edit_perm_descargar_excel">Descargar Excel</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permisos[]" value="gestionar_ajustes" id="edit_perm_gestionar_ajustes">
                                        <label class="form-check-label small" for="edit_perm_gestionar_ajustes">Gestionar Ajustes / Logo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-premium">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtrar el panel de control por el curso seleccionado
        function filterDashboardCourse(val) {
            window.location.href = 'index.php?tab=dashboard&dashboard_curso_id=' + val;
        }

        // Filtrar la tabla de inscripciones por el curso seleccionado
        function filterInscripcionesCourse(val) {
            window.location.href = 'index.php?tab=inscripciones&curso_id=' + val;
        }

        // Descargar el archivo Excel (CSV) filtrado por el curso activo
        function triggerExcelDownload() {
            var select = document.getElementById('filter_curso_inscripciones');
            var cursoId = select ? select.value : 'all';
            window.location.href = 'export_excel.php?curso_id=' + cursoId;
        }

        // Asistentes de modal para rellenar formularios con datos existentes
        var editUserModal = document.getElementById('editUserModal');
        if (editUserModal) {
            editUserModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-bs-id');
                var nombre = button.getAttribute('data-bs-nombre');
                var email = button.getAttribute('data-bs-email');
                var rol = button.getAttribute('data-bs-rol');
                var permisos = button.getAttribute('data-bs-permisos') || '';
                
                editUserModal.querySelector('#edit_user_id').value = id;
                editUserModal.querySelector('#edit_user_nombre').value = nombre;
                editUserModal.querySelector('#edit_user_email').value = email;
                editUserModal.querySelector('#edit_user_rol_id').value = rol;
                editUserModal.querySelector('#edit_user_password').value = '';
                
                // Configurar casillas de verificación de permisos ACL
                var permArray = permisos.split(',');
                editUserModal.querySelectorAll('input[name="permisos[]"]').forEach(function(cb) {
                    cb.checked = permArray.includes(cb.value);
                });
            });
        }

        // Permisos directos en la sección Roles y Permisos
        document.querySelectorAll('.permissions-form').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                var userId = form.dataset.userId;
                var submitBtn = form.querySelector('.save-permissions-btn');
                var alertBox = form.querySelector('.permissions-alert');
                var formData = new FormData(form);
                formData.append('user_id', userId);

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

                fetch('update_permisos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (alertBox) {
                        alertBox.className = 'mt-3 permissions-alert alert ' + (data.ok ? 'alert-success' : 'alert-danger');
                        alertBox.textContent = data.msg;
                    }
                })
                .catch(function() {
                    if (alertBox) {
                        alertBox.className = 'mt-3 permissions-alert alert alert-danger';
                        alertBox.textContent = 'Error al guardar los permisos. Intenta de nuevo.';
                    }
                })
                .finally(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-save me-1"></i>Guardar permisos';
                });
            });
        });
    </script>
</body>
</html>
