<?php
require_once '../config/db.php';
require_once '../models/Curso.php';
require_once '../models/Usuario.php';
require_once '../models/Inscripcion.php';
require_once '../models/Servicio.php';
require_once '../models/Ajustes.php';

session_start();

// Verify access to the manage area for admin or course creators
if (!isset($_SESSION['user_role']) || !in_array((int) $_SESSION['user_role'], [1, 2], true)) {
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
        elseif ($action == 'delete_category' && $id > 0) {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_ajustes')) {
                $error = 'No tienes permiso para gestionar categorías.';
            } else {
                $stmt = $conn->prepare("DELETE FROM lc_categorias WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $success = 'Categoría eliminada con éxito.';
            }
            header("Location: index.php?tab=categorias&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }
        elseif ($action == 'delete_blog' && $id > 0) {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')) {
                $error = 'No tienes permiso para eliminar artículos.';
            } else {
                $stmt = $conn->prepare("SELECT imagen FROM lc_blog WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $img = $stmt->fetchColumn();
                if (!empty($img) && file_exists('../' . $img)) {
                    unlink('../' . $img);
                }
                
                $stmt = $conn->prepare("DELETE FROM lc_blog WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $success = 'Artículo de blog eliminado con éxito.';
            }
            header("Location: index.php?tab=blog&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }
        elseif ($action == 'delete_event' && $id > 0) {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')) {
                $error = 'No tienes permiso para eliminar eventos.';
            } else {
                $stmt = $conn->prepare("DELETE FROM lc_eventos WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $success = 'Evento eliminado con éxito.';
            }
            header("Location: index.php?tab=eventos&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }
        elseif ($action == 'delete_media' && $id > 0) {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')) {
                $error = 'No tienes permiso para eliminar archivos.';
            } else {
                $stmt = $conn->prepare("SELECT ruta FROM lc_multimedia WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $path = $stmt->fetchColumn();
                if (!empty($path) && file_exists('../' . $path)) {
                    unlink('../' . $path);
                }
                
                $stmt = $conn->prepare("DELETE FROM lc_multimedia WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $success = 'Archivo eliminado de la biblioteca.';
            }
            header("Location: index.php?tab=multimedia&success=" . urlencode($success) . "&error=" . urlencode($error));
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
                $asignatura = trim($_POST['asignatura'] ?? '');
                $foto = trim($_POST['foto'] ?? '');
                $permisos_arr = isset($_POST['permisos']) ? $_POST['permisos'] : [];
                $permisos = implode(',', $permisos_arr);

                if (empty($nombre) || empty($email) || empty($password)) {
                    $error = 'Todos los campos son obligatorios.';
                } else {
                    $usuarioModel->create($nombre, $email, $password, $rol_id, $permisos, $asignatura, $foto);
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
                $asignatura = trim($_POST['asignatura'] ?? '');
                $foto = trim($_POST['foto'] ?? '');
                $permisos_arr = isset($_POST['permisos']) ? $_POST['permisos'] : [];
                $permisos = implode(',', $permisos_arr);

                if (empty($nombre) || empty($email)) {
                    $error = 'Nombre y correo son obligatorios.';
                } else {
                    $usuarioModel->update($id, $nombre, $email, $rol_id, $password, $permisos, $asignatura, $foto);
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
        // Add Category (Fase 3)
        elseif ($action == 'add_category') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_ajustes')) {
                $error = 'No tienes permiso para gestionar categorías.';
            } else {
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion'] ?? '');
                if (empty($nombre)) {
                    $error = 'El nombre de la categoría es obligatorio.';
                } else {
                    $stmt = $conn->prepare("INSERT INTO lc_categorias (nombre, descripcion) VALUES (:nombre, :descripcion)");
                    $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion]);
                    $success = 'Categoría creada con éxito.';
                }
            }
            header("Location: index.php?tab=categorias&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }

        // Add Blog Post (Fase 3)
        elseif ($action == 'add_blog') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')) {
                $error = 'No tienes permiso para crear artículos del blog.';
            } else {
                $titulo = trim($_POST['titulo']);
                $contenido = trim($_POST['contenido']);
                $imagen = '';
                
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['imagen']['tmp_name'];
                    $file_name = $_FILES['imagen']['name'];
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
                        $new_name = 'blog_' . time() . '.' . $ext;
                        $dest = '../uploads/' . $new_name;
                        if (move_uploaded_file($file_tmp, $dest)) {
                            $imagen = 'uploads/' . $new_name;
                        }
                    }
                }

                if (empty($titulo) || empty($contenido)) {
                    $error = 'Título y contenido son obligatorios.';
                } else {
                    $stmt = $conn->prepare("INSERT INTO lc_blog (titulo, contenido, imagen, autor_id) VALUES (:titulo, :contenido, :imagen, :autor_id)");
                    $stmt->execute(['titulo' => $titulo, 'contenido' => $contenido, 'imagen' => $imagen, 'autor_id' => $_SESSION['user_id']]);
                    $success = 'Artículo publicado con éxito.';
                }
            }
            header("Location: index.php?tab=blog&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }

        // Add Event (Fase 3)
        elseif ($action == 'add_event') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')) {
                $error = 'No tienes permiso para crear eventos.';
            } else {
                $titulo = trim($_POST['titulo']);
                $descripcion = trim($_POST['descripcion'] ?? '');
                $fecha = trim($_POST['fecha_evento']);
                $tipo = trim($_POST['tipo'] ?? 'webinar');
                $enlace = trim($_POST['enlace'] ?? '');

                if (empty($titulo) || empty($fecha)) {
                    $error = 'Título y fecha del evento son obligatorios.';
                } else {
                    $stmt = $conn->prepare("INSERT INTO lc_eventos (titulo, descripcion, fecha_evento, tipo, enlace) VALUES (:titulo, :descripcion, :fecha, :tipo, :enlace)");
                    $stmt->execute(['titulo' => $titulo, 'descripcion' => $descripcion, 'fecha' => $fecha, 'tipo' => $tipo, 'enlace' => $enlace]);
                    $success = 'Evento programado con éxito.';
                }
            }
            header("Location: index.php?tab=eventos&success=" . urlencode($success) . "&error=" . urlencode($error));
            exit;
        }

        // Upload Media (Fase 3)
        elseif ($action == 'upload_media') {
            if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')) {
                $error = 'No tienes permiso para subir archivos.';
            } else {
                if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['archivo']['tmp_name'];
                    $file_name = $_FILES['archivo']['name'];
                    $file_size = $_FILES['archivo']['size'];
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    if (!is_dir('../uploads/multimedia')) {
                        mkdir('../uploads/multimedia', 0777, true);
                    }

                    $new_name = 'media_' . time() . '_' . rand(100,999) . '.' . $ext;
                    $dest = '../uploads/multimedia/' . $new_name;

                    if (move_uploaded_file($file_tmp, $dest)) {
                        $ruta_rel = 'uploads/multimedia/' . $new_name;
                        $etiquetas = trim($_POST['etiquetas'] ?? '');
                        
                        $stmt = $conn->prepare("INSERT INTO lc_multimedia (nombre, ruta, tipo, tamano, etiquetas) VALUES (:nombre, :ruta, :tipo, :tamano, :etiquetas)");
                        $stmt->execute(['nombre' => $file_name, 'ruta' => $ruta_rel, 'tipo' => $ext, 'tamano' => $file_size, 'etiquetas' => $etiquetas]);
                        $success = 'Archivo subido con éxito a la biblioteca.';
                    } else {
                        $error = 'Error al mover el archivo subido.';
                    }
                } else {
                    $error = 'Por favor selecciona un archivo válido.';
                }
            }
            header("Location: index.php?tab=multimedia&success=" . urlencode($success) . "&error=" . urlencode($error));
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

    // Consultas para tendencias y distribución (Fase 3: Analytics)
    $trendStmt = $conn->query("SELECT DATE_FORMAT(fecha_inscripcion, '%b %Y') AS mes, COUNT(*) AS total FROM inscripciones GROUP BY DATE_FORMAT(fecha_inscripcion, '%Y-%m') ORDER BY DATE_FORMAT(fecha_inscripcion, '%Y-%m') ASC LIMIT 6");
    $trendData = $trendStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $catStmt = $conn->query("SELECT COALESCE(cat.nombre, 'Sin Categoría') AS categoria, COUNT(c.id) AS total FROM lc_cursos c LEFT JOIN lc_categorias cat ON cat.id = c.categoria_id GROUP BY cat.nombre");
    $catData = $catStmt->fetchAll(PDO::FETCH_ASSOC);

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
    $published_courses = count($all_active_courses);
    $draft_courses = max(0, $total_courses - $published_courses);
    $recent_courses = array_slice($all_active_courses, 0, 4);

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
    } elseif ($tab == 'categorias') {
        $categorias = $conn->query("SELECT * FROM lc_categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($tab == 'multimedia') {
        $multimedia = $conn->query("SELECT * FROM lc_multimedia ORDER BY creado_en DESC")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($tab == 'blog') {
        $posts = $conn->query("SELECT b.*, u.nombre AS autor_nombre FROM lc_blog b JOIN usuarios u ON b.autor_id = u.id ORDER BY b.creado_en DESC")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($tab == 'eventos') {
        $eventos = $conn->query("SELECT * FROM lc_eventos ORDER BY fecha_evento ASC")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($tab == 'auditoria') {
        $logs = $conn->query("SELECT a.*, COALESCE(u.nombre, 'Sistema/Invitado') AS usuario_nombre FROM lc_auditoria a LEFT JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.creado_en DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error = 'Error al cargar datos: ' . $e->getMessage();
}

$page_title = 'Panel de Administración - Northstar LMS';
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
                    <div class="card dashboard-hero border-0 rounded-4 shadow-sm p-4 p-lg-5 mb-4">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <div class="text-uppercase small text-primary fw-bold mb-2">Panel del profesor</div>
                                <h3 class="fw-bold text-dark mb-3">Gestiona tus cursos, estudiantes y publicaciones desde un solo lugar</h3>
                                <p class="text-muted mb-0">Tu espacio de trabajo está listo para crear experiencias educativas más completas, publicar contenidos y hacer seguimiento del progreso.</p>
                                <div class="d-flex flex-wrap gap-2 mt-4">
                                    <a href="crear_curso.php" class="btn btn-primary-premium"><i class="bi bi-plus-circle me-1"></i>Crear nuevo curso</a>
                                    <a href="index.php?tab=cursos" class="btn btn-outline-primary rounded-pill"><i class="bi bi-journal-text me-1"></i>Administrar cursos</a>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="bg-white rounded-4 p-3 shadow-sm border">
                                    <div class="small text-muted fw-bold mb-2">Resumen rápido</div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Cursos publicados</span>
                                        <strong class="text-primary"><?php echo $published_courses; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Borradores</span>
                                        <strong class="text-warning"><?php echo $draft_courses; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Inscripciones</span>
                                        <strong class="text-success"><?php echo $total_registrations; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6 col-xl-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Cursos</h6>
                                        <h2 class="fw-bold mb-0 text-primary"><?php echo $total_courses; ?></h2>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3"><i class="bi bi-journal-text fs-4"></i></div>
                                </div>
                                <div class="mt-2 text-muted small"><span><?php echo $published_courses; ?> publicados · <?php echo $draft_courses; ?> borradores</span></div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Inscripciones</h6>
                                        <h2 class="fw-bold mb-0 text-success"><?php echo $total_registrations; ?></h2>
                                    </div>
                                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3"><i class="bi bi-people-fill fs-4"></i></div>
                                </div>
                                <div class="mt-2 text-muted small"><span>Estudiantes activos en tus cursos</span></div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">En línea</h6>
                                        <h2 class="fw-bold mb-0 text-teal" style="color: var(--accent-color);"><?php echo $online_users; ?></h2>
                                    </div>
                                    <div class="bg-info bg-opacity-10 text-teal rounded-3 p-3" style="color: var(--accent-color); background-color: rgba(13, 148, 136, 0.1);"><i class="bi bi-wifi fs-4"></i></div>
                                </div>
                                <div class="mt-2 text-muted small"><span>Usuarios activos en los últimos 5 minutos</span></div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card stat-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Actividad</h6>
                                        <h2 class="fw-bold mb-0 text-primary"><?php echo count($last_registrations); ?></h2>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3"><i class="bi bi-clock-history fs-4"></i></div>
                                </div>
                                <div class="mt-2 text-muted small"><span>Últimos registros recientes</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos de Analíticas y KPIs (Fase 3: Analytics) -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-8">
                            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Tendencia de Inscripciones</h5>
                                    <span class="small text-muted">Historial de registros</span>
                                </div>
                                <div style="height: 280px; position: relative;">
                                    <canvas id="enrollmentTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart text-primary me-2"></i>Distribución de Cursos</h5>
                                    <span class="small text-muted">Por Categoría</span>
                                </div>
                                <div style="height: 280px; position: relative; max-width: 250px; margin: 0 auto;">
                                    <canvas id="categoryDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-lg-7">
                            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                    <h5 class="fw-bold mb-0"><i class="bi bi-lightning-charge me-2 text-primary"></i>Acciones rápidas</h5>
                                    <span class="small text-muted">Todo listo para avanzar</span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <a href="crear_curso.php" class="dashboard-action-card d-block p-3 rounded-4 border h-100 text-decoration-none">
                                            <div class="d-flex align-items-center mb-2"><i class="bi bi-plus-circle-fill text-primary fs-4 me-2"></i><span class="fw-bold text-dark">Crear curso</span></div>
                                            <div class="small text-muted">Inicia un nuevo curso con el asistente paso a paso.</div>
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="index.php?tab=cursos" class="dashboard-action-card d-block p-3 rounded-4 border h-100 text-decoration-none">
                                            <div class="d-flex align-items-center mb-2"><i class="bi bi-journal-text text-success fs-4 me-2"></i><span class="fw-bold text-dark">Gestionar cursos</span></div>
                                            <div class="small text-muted">Revisa, publica, edita o desactiva tus contenidos.</div>
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="index.php?tab=inscripciones" class="dashboard-action-card d-block p-3 rounded-4 border h-100 text-decoration-none">
                                            <div class="d-flex align-items-center mb-2"><i class="bi bi-person-check-fill text-info fs-4 me-2"></i><span class="fw-bold text-dark">Inscripciones</span></div>
                                            <div class="small text-muted">Consulta y filtra los estudiantes inscritos.</div>
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="../index.php" class="dashboard-action-card d-block p-3 rounded-4 border h-100 text-decoration-none">
                                            <div class="d-flex align-items-center mb-2"><i class="bi bi-globe2 text-warning fs-4 me-2"></i><span class="fw-bold text-dark">Ver sitio</span></div>
                                            <div class="small text-muted">Ve cómo se ve la plataforma para los estudiantes.</div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0"><i class="bi bi-journal-bookmark me-2 text-primary"></i>Cursos destacados</h5>
                                    <a href="index.php?tab=cursos" class="small text-primary text-decoration-none">Ver todos</a>
                                </div>
                                <div class="d-grid gap-2">
                                    <?php if (count($recent_courses) > 0): ?>
                                        <?php foreach ($recent_courses as $course): ?>
                                            <div class="border rounded-3 p-3">
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div>
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($course['titulo']); ?></div>
                                                        <div class="small text-muted"><?php echo htmlspecialchars($course['categoria'] ?: 'Sin categoría'); ?></div>
                                                    </div>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary"><?php echo $course['estado'] == 1 ? 'Publicado' : 'Borrador'; ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted small">Aún no hay cursos para mostrar.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Últimos registros</h5>
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
                                        <th>Categoría</th>
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
                                                    <?php if (!empty($c['categoria'])): ?>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary p-2"><?php echo htmlspecialchars($c['categoria']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Sin categoría</span>
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
                                                            <a href="editar_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary" title="Editar General">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="course_builder.php?course_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-success" title="Constructor Visual">
                                                                <i class="bi bi-diagram-3"></i>
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
                                            <td colspan="7" class="text-center py-4 text-muted">No hay cursos registrados.</td>
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
                                        <th>Asignatura</th>
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
                                                    <?php if (!empty($u['asignatura'])): ?>
                                                        <span class="badge bg-info bg-opacity-10 text-info p-2"><?php echo htmlspecialchars($u['asignatura']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Sin asignatura</span>
                                                    <?php endif; ?>
                                                </td>
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
                                                                    data-bs-asignatura="<?php echo htmlspecialchars($u['asignatura'] ?? ''); ?>"
                                                                    data-bs-foto="<?php echo htmlspecialchars($u['foto'] ?? ''); ?>"
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
                                            <td colspan="7" class="text-center py-4 text-muted">No hay usuarios registrados.</td>
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
                                        <span class="fw-bold fs-5 text-primary">Northstar LMS</span>
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

                <!-- ================= TAB: CATEGORIAS (Fase 3) ================= -->
                <?php if ($tab == 'categorias'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold text-dark mb-0">Gestión de Categorías</h3>
                        <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_ajustes')): ?>
                            <button class="btn btn-primary-premium animate-hover" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus-circle me-1"></i>Crear Categoría
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Fecha Creación</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($categorias) > 0): ?>
                                        <?php foreach ($categorias as $cat): ?>
                                            <tr>
                                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                                <td class="text-muted small"><?php echo htmlspecialchars($cat['descripcion'] ?: 'Sin descripción.'); ?></td>
                                                <td class="text-muted small"><?php echo htmlspecialchars($cat['creado_en']); ?></td>
                                                <td class="text-end">
                                                    <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_ajustes')): ?>
                                                        <a href="index.php?action=delete_category&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar esta categoría?');">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">No autorizado</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No hay categorías registradas.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ================= TAB: MULTIMEDIA (Fase 3) ================= -->
                <?php if ($tab == 'multimedia'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold text-dark mb-0">Biblioteca de Medios</h3>
                        <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')): ?>
                            <button class="btn btn-primary-premium animate-hover" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                                <i class="bi bi-upload me-1"></i>Subir Archivo
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="row g-4">
                        <?php if (count($multimedia) > 0): ?>
                            <?php foreach ($multimedia as $media): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border rounded-4 bg-white shadow-sm overflow-hidden h-100">
                                        <div class="position-relative bg-light p-3 d-flex align-items-center justify-content-center border-bottom" style="height: 140px;">
                                            <?php if (in_array(strtolower($media['tipo']), ['png', 'jpg', 'jpeg', 'webp', 'gif'])): ?>
                                                <img src="../<?php echo htmlspecialchars($media['ruta']); ?>" class="img-fluid rounded" style="max-height: 120px; object-fit: contain;">
                                            <?php else: ?>
                                                <div class="text-center text-secondary">
                                                    <i class="bi bi-file-earmark-arrow-down-fill display-5 text-primary"></i>
                                                    <div class="small fw-semibold mt-1 text-uppercase text-muted"><?php echo htmlspecialchars($media['tipo']); ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <span class="position-absolute top-0 end-0 badge bg-dark bg-opacity-70 m-2 small"><?php echo htmlspecialchars(strtoupper($media['tipo'])); ?></span>
                                        </div>
                                        <div class="p-3">
                                            <div class="fw-bold text-dark text-truncate small" title="<?php echo htmlspecialchars($media['nombre']); ?>"><?php echo htmlspecialchars($media['nombre']); ?></div>
                                            <div class="text-muted small mt-1">Peso: <?php echo round($media['tamano'] / 1024, 1); ?> KB</div>
                                            <?php if (!empty($media['etiquetas'])): ?>
                                                <div class="mt-2"><span class="badge bg-light text-muted border"><?php echo htmlspecialchars($media['etiquetas']); ?></span></div>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                                <a href="../<?php echo htmlspecialchars($media['ruta']); ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 small" style="font-size: 0.75rem;"><i class="bi bi-eye"></i> Ver URL</a>
                                                <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')): ?>
                                                    <a href="index.php?action=delete_media&id=<?php echo $media['id']; ?>" class="text-danger small text-decoration-none" onclick="return confirm('¿Eliminar este archivo permanentemente?');"><i class="bi bi-trash"></i> Borrar</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5 text-muted bg-white border rounded-4">
                                <i class="bi bi-images display-3 text-muted mb-2"></i>
                                <p class="mb-0">No hay archivos en la biblioteca de medios todavía.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- ================= TAB: BLOG (Fase 3) ================= -->
                <?php if ($tab == 'blog'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold text-dark mb-0">Artículos y Blog</h3>
                        <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')): ?>
                            <button class="btn btn-primary-premium animate-hover" data-bs-toggle="modal" data-bs-target="#addBlogPostModal">
                                <i class="bi bi-plus-circle me-1"></i>Crear Artículo
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Portada</th>
                                        <th>Título</th>
                                        <th>Autor</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($posts) > 0): ?>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($post['imagen'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($post['imagen']); ?>" class="rounded" style="width: 50px; height: 35px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 35px;"><i class="bi bi-image small"></i></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($post['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($post['autor_nombre']); ?></td>
                                                <td><span class="badge bg-success bg-opacity-10 text-success p-2"><?php echo htmlspecialchars($post['estado']); ?></span></td>
                                                <td class="text-muted small"><?php echo htmlspecialchars($post['creado_en']); ?></td>
                                                <td class="text-end">
                                                    <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')): ?>
                                                        <a href="index.php?action=delete_blog&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Seguro de que deseas eliminar este artículo?');">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No hay artículos publicados en el blog.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ================= TAB: EVENTOS (Fase 3) ================= -->
                <?php if ($tab == 'eventos'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold text-dark mb-0">Eventos & Webinars</h3>
                        <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')): ?>
                            <button class="btn btn-primary-premium animate-hover" data-bs-toggle="modal" data-bs-target="#addEventModal">
                                <i class="bi bi-calendar-plus me-1"></i>Programar Evento
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="table-responsive">
                            <table class="table custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Fecha y Hora</th>
                                        <th>Tipo</th>
                                        <th>Enlace de Acceso</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($eventos) > 0): ?>
                                        <?php foreach ($eventos as $ev): ?>
                                            <tr>
                                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($ev['titulo']); ?></td>
                                                <td class="text-muted small"><?php echo htmlspecialchars($ev['fecha_evento']); ?></td>
                                                <td><span class="badge bg-primary bg-opacity-10 text-primary p-2"><?php echo htmlspecialchars($ev['tipo']); ?></span></td>
                                                <td>
                                                    <?php if (!empty($ev['enlace'])): ?>
                                                        <a href="<?php echo htmlspecialchars($ev['enlace']); ?>" target="_blank" class="small text-truncate d-inline-block text-primary" style="max-width: 200px;"><i class="bi bi-box-arrow-up-right me-1"></i>Ir a la sesión</a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Sin enlace</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($usuarioModel->hasPermission($_SESSION['user_id'], 'crear_cursos')): ?>
                                                        <a href="index.php?action=delete_event&id=<?php echo $ev['id']; ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Seguro de que deseas cancelar este evento?');">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No hay eventos programados.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ================= TAB: AUDITORIA (Fase 3) ================= -->
                <?php if ($tab == 'auditoria'): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold text-dark mb-0">Logs de Auditoría y Logs</h3>
                        <span class="badge bg-light text-muted border p-2">Monitoreo de seguridad activo</span>
                    </div>

                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <p class="text-muted small mb-4">Registro cronológico detallado de las actividades e inicios de sesión de la plataforma LMS corporativa.</p>
                        <div class="table-responsive">
                            <table class="table custom-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Acción realizada</th>
                                        <th>Detalles adicionales</th>
                                        <th>IP origen</th>
                                        <th>Fecha y Hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($logs) > 0): ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($log['usuario_nombre']); ?></td>
                                                <td><span class="badge bg-dark bg-opacity-10 text-dark p-2"><?php echo htmlspecialchars($log['accion']); ?></span></td>
                                                <td class="text-muted small"><?php echo htmlspecialchars($log['detalle']); ?></td>
                                                <td class="font-monospace small"><?php echo htmlspecialchars($log['ip']); ?></td>
                                                <td class="text-muted small"><?php echo htmlspecialchars($log['creado_en']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Aún no hay logs registrados en el sistema.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- ================= MODALS ================= -->

    <!-- Modal: Crear Categoría -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-premium p-3">
                <div class="modal-header border-0">
                    <h5 class="fw-bold"><i class="bi bi-tags text-primary me-2"></i>Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php?tab=categorias" method="POST">
                    <div class="modal-body border-0">
                        <input type="hidden" name="action" value="add_category">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre de la Categoría <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control form-control-premium" placeholder="Ej. Programación Web" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control form-control-premium" rows="3" placeholder="Describe brevemente esta categoría..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-premium px-4"><i class="bi bi-plus-circle me-1"></i>Crear Categoría</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Subir Multimedia -->
    <div class="modal fade" id="uploadMediaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-premium p-3">
                <div class="modal-header border-0">
                    <h5 class="fw-bold"><i class="bi bi-upload text-primary me-2"></i>Subir Archivo a la Biblioteca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php?tab=multimedia" method="POST" enctype="multipart/form-data">
                    <div class="modal-body border-0">
                        <input type="hidden" name="action" value="upload_media">
                        <div class="mb-4 p-4 border-2 border-dashed rounded-4 text-center bg-light" style="border: 2px dashed var(--border-color);">
                            <i class="bi bi-cloud-upload display-5 text-primary mb-3 d-block"></i>
                            <p class="text-muted small mb-3">Selecciona imágenes, videos, PDFs, Word, Excel, audio o cualquier archivo multimedia.</p>
                            <input type="file" name="archivo" id="mediaFile" class="form-control form-control-premium" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Etiquetas (separadas por comas)</label>
                            <input type="text" name="etiquetas" class="form-control form-control-premium" placeholder="Ej. video, introducción, módulo1">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-premium px-4"><i class="bi bi-upload me-1"></i>Subir Archivo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Crear Artículo de Blog -->
    <div class="modal fade" id="addBlogPostModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content modal-content-premium p-3">
                <div class="modal-header border-0">
                    <h5 class="fw-bold"><i class="bi bi-file-earmark-post text-primary me-2"></i>Nuevo Artículo del Blog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php?tab=blog" method="POST" enctype="multipart/form-data">
                    <div class="modal-body border-0">
                        <input type="hidden" name="action" value="add_blog">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Título del artículo <span class="text-danger">*</span></label>
                                    <input type="text" name="titulo" class="form-control form-control-premium" placeholder="Ej. 5 estrategias para aprender más rápido" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Contenido <span class="text-danger">*</span></label>
                                    <textarea name="contenido" class="form-control form-control-premium" rows="8" placeholder="Escribe el cuerpo completo del artículo aquí..." required></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Imagen de portada</label>
                                    <input type="file" name="imagen" class="form-control form-control-premium" accept=".png,.jpg,.jpeg,.webp,.gif">
                                    <div class="form-text small text-muted">PNG, JPG, WEBP. Recomendado 1200×630.</div>
                                </div>
                                <div class="p-3 bg-light rounded-3 small text-muted border">
                                    <i class="bi bi-info-circle text-primary me-1"></i>
                                    El artículo se publicará inmediatamente. Puedes editar el estado desde la tabla de artículos.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-premium px-4"><i class="bi bi-send me-1"></i>Publicar Artículo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Programar Evento / Webinar -->
    <div class="modal fade" id="addEventModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-premium p-3">
                <div class="modal-header border-0">
                    <h5 class="fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i>Programar Evento / Webinar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php?tab=eventos" method="POST">
                    <div class="modal-body border-0">
                        <input type="hidden" name="action" value="add_event">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Título del evento <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control form-control-premium" placeholder="Ej. Webinar de Inteligencia Artificial 2026" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tipo de evento</label>
                                <select name="tipo" class="form-select form-control-premium">
                                    <option value="webinar">Webinar</option>
                                    <option value="conferencia">Conferencia</option>
                                    <option value="taller">Taller Práctico</option>
                                    <option value="seminario">Seminario</option>
                                    <option value="workshop">Workshop</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Descripción</label>
                                <textarea name="descripcion" class="form-control form-control-premium" rows="3" placeholder="Describe de qué trata el evento y a quién va dirigido..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Fecha y Hora <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="fecha_evento" class="form-control form-control-premium" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Enlace de acceso (Zoom / Meet / Teams)</label>
                                <input type="url" name="enlace" class="form-control form-control-premium" placeholder="https://zoom.us/j/...">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-premium px-4"><i class="bi bi-calendar-check me-1"></i>Programar Evento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_user_asignatura" class="form-label small fw-bold">Asignatura / Área</label>
                                <input type="text" name="asignatura" id="add_user_asignatura" class="form-control form-control-premium" placeholder="Ej. Matemáticas, Programación">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_user_foto" class="form-label small fw-bold">Foto / URL de Perfil</label>
                                <input type="text" name="foto" id="add_user_foto" class="form-control form-control-premium" placeholder="https://...">
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_user_asignatura" class="form-label small fw-bold">Asignatura / Área</label>
                                <input type="text" name="asignatura" id="edit_user_asignatura" class="form-control form-control-premium" placeholder="Ej. Matemáticas, Programación">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_user_foto" class="form-label small fw-bold">Foto / URL de Perfil</label>
                                <input type="text" name="foto" id="edit_user_foto" class="form-control form-control-premium" placeholder="https://...">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Render Chart.js graphs if we are on the dashboard (Fase 3: Analytics)
        <?php if ($tab == 'dashboard'): ?>
        const trendData = <?php echo json_encode($trendData); ?>;
        const catData = <?php echo json_encode($catData); ?>;
        
        // Enrollment Trend Chart (Line Chart)
        const trendCtx = document.getElementById('enrollmentTrendChart')?.getContext('2d');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.map(item => item.mes),
                    datasets: [{
                        label: 'Inscritos',
                        data: trendData.map(item => item.total),
                        borderColor: '#1a73e8',
                        backgroundColor: 'rgba(26, 115, 232, 0.08)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#1a73e8'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // Category Distribution Chart (Doughnut Chart)
        const catCtx = document.getElementById('categoryDistributionChart')?.getContext('2d');
        if (catCtx) {
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: catData.map(item => item.categoria),
                    datasets: [{
                        data: catData.map(item => item.total),
                        backgroundColor: ['#1a73e8', '#2e7d32', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 12, font: { size: 10 } }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

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
                editUserModal.querySelector('#edit_user_asignatura').value = button.getAttribute('data-bs-asignatura') || '';
                editUserModal.querySelector('#edit_user_foto').value = button.getAttribute('data-bs-foto') || '';
                
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
