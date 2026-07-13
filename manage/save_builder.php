<?php
require_once '../config/db.php';
session_start();

header('Content-Type: application/json');

// Check access
if (!isset($_SESSION['user_role']) || !in_array((int) $_SESSION['user_role'], [1, 2], true)) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // --- MODULOS ---
        case 'add_module':
            $curso_id = intval($_POST['curso_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? 'Nuevo Módulo');
            if ($curso_id <= 0) throw new Exception('ID de curso no válido');
            
            // Get next order number
            $stmt = $conn->prepare("SELECT COALESCE(MAX(orden_num), 0) + 1 FROM lc_modulos WHERE curso_id = :curso_id");
            $stmt->execute(['curso_id' => $curso_id]);
            $order = $stmt->fetchColumn();

            $stmt = $conn->prepare("INSERT INTO lc_modulos (curso_id, nombre, orden_num) VALUES (:curso_id, :nombre, :order)");
            $stmt->execute(['curso_id' => $curso_id, 'nombre' => $nombre, 'order' => $order]);
            
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId(), 'nombre' => $nombre]);
            break;

        case 'edit_module':
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            if ($id <= 0 || empty($nombre)) throw new Exception('Parámetros no válidos');

            $stmt = $conn->prepare("UPDATE lc_modulos SET nombre = :nombre WHERE id = :id");
            $stmt->execute(['nombre' => $nombre, 'id' => $id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_module':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('ID no válido');

            $stmt = $conn->prepare("DELETE FROM lc_modulos WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true]);
            break;

        case 'sort_modules':
            $ids = $_POST['ids'] ?? [];
            if (empty($ids)) throw new Exception('No hay IDs para ordenar');

            $conn->beginTransaction();
            $stmt = $conn->prepare("UPDATE lc_modulos SET orden_num = :order WHERE id = :id");
            foreach ($ids as $index => $id) {
                $stmt->execute(['order' => $index + 1, 'id' => intval($id)]);
            }
            $conn->commit();
            echo json_encode(['success' => true]);
            break;

        // --- UNIDADES ---
        case 'add_unit':
            $modulo_id = intval($_POST['modulo_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? 'Nueva Unidad');
            if ($modulo_id <= 0) throw new Exception('ID de módulo no válido');

            $stmt = $conn->prepare("SELECT COALESCE(MAX(orden_num), 0) + 1 FROM lc_unidades WHERE modulo_id = :modulo_id");
            $stmt->execute(['modulo_id' => $modulo_id]);
            $order = $stmt->fetchColumn();

            $stmt = $conn->prepare("INSERT INTO lc_unidades (modulo_id, nombre, orden_num) VALUES (:modulo_id, :nombre, :order)");
            $stmt->execute(['modulo_id' => $modulo_id, 'nombre' => $nombre, 'order' => $order]);
            
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId(), 'nombre' => $nombre]);
            break;

        case 'edit_unit':
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            if ($id <= 0 || empty($nombre)) throw new Exception('Parámetros no válidos');

            $stmt = $conn->prepare("UPDATE lc_unidades SET nombre = :nombre WHERE id = :id");
            $stmt->execute(['nombre' => $nombre, 'id' => $id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_unit':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('ID no válido');

            $stmt = $conn->prepare("DELETE FROM lc_unidades WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true]);
            break;

        case 'sort_units':
            $ids = $_POST['ids'] ?? [];
            if (empty($ids)) throw new Exception('No hay IDs para ordenar');

            $conn->beginTransaction();
            $stmt = $conn->prepare("UPDATE lc_unidades SET orden_num = :order WHERE id = :id");
            foreach ($ids as $index => $id) {
                $stmt->execute(['order' => $index + 1, 'id' => intval($id)]);
            }
            $conn->commit();
            echo json_encode(['success' => true]);
            break;

        // --- LECCIONES ---
        case 'add_lesson':
            $unidad_id = intval($_POST['unidad_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? 'Nueva Lección');
            $tipo = trim($_POST['tipo'] ?? 'texto');
            if ($unidad_id <= 0) throw new Exception('ID de unidad no válido');

            $stmt = $conn->prepare("SELECT COALESCE(MAX(orden_num), 0) + 1 FROM lc_lecciones WHERE unidad_id = :unidad_id");
            $stmt->execute(['unidad_id' => $unidad_id]);
            $order = $stmt->fetchColumn();

            $stmt = $conn->prepare("INSERT INTO lc_lecciones (unidad_id, nombre, tipo, orden_num) VALUES (:unidad_id, :nombre, :tipo, :order)");
            $stmt->execute(['unidad_id' => $unidad_id, 'nombre' => $nombre, 'tipo' => $tipo, 'order' => $order]);
            
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId(), 'nombre' => $nombre, 'tipo' => $tipo]);
            break;

        case 'edit_lesson':
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'texto');
            $texto = $_POST['contenido_texto'] ?? null;
            $url = trim($_POST['contenido_url'] ?? '');
            $duracion = intval($_POST['duracion_segundos'] ?? 0);
            $estado = intval($_POST['estado'] ?? 1);

            if ($id <= 0 || empty($nombre)) throw new Exception('Parámetros no válidos');

            $stmt = $conn->prepare("UPDATE lc_lecciones SET nombre = :nombre, tipo = :tipo, contenido_texto = :texto, contenido_url = :url, duracion_segundos = :duracion, estado = :estado WHERE id = :id");
            $stmt->execute([
                'nombre' => $nombre,
                'tipo' => $tipo,
                'texto' => $texto,
                'url' => $url,
                'duracion' => $duracion,
                'estado' => $estado,
                'id' => $id
            ]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_lesson':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('ID no válido');

            $stmt = $conn->prepare("DELETE FROM lc_lecciones WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true]);
            break;

        case 'sort_lessons':
            $ids = $_POST['ids'] ?? [];
            if (empty($ids)) throw new Exception('No hay IDs para ordenar');

            $conn->beginTransaction();
            $stmt = $conn->prepare("UPDATE lc_lecciones SET orden_num = :order WHERE id = :id");
            foreach ($ids as $index => $id) {
                $stmt->execute(['order' => $index + 1, 'id' => intval($id)]);
            }
            $conn->commit();
            echo json_encode(['success' => true]);
            break;

        // --- RECURSOS ---
        case 'add_resource':
            $leccion_id = intval($_POST['leccion_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? 'Nuevo Recurso');
            $url = trim($_POST['url'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'link');
            if ($leccion_id <= 0 || empty($url)) throw new Exception('Parámetros no válidos');

            $stmt = $conn->prepare("INSERT INTO lc_recursos (leccion_id, nombre, url, tipo) VALUES (:leccion_id, :nombre, :url, :tipo)");
            $stmt->execute(['leccion_id' => $leccion_id, 'nombre' => $nombre, 'url' => $url, 'tipo' => $tipo]);
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
            break;

        case 'delete_resource':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('ID no válido');

            $stmt = $conn->prepare("DELETE FROM lc_recursos WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['success' => true]);
            break;

        // --- ACTIVIDADES ---
        case 'save_activity':
            $leccion_id = intval($_POST['leccion_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? 'Nueva Actividad');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'tarea');
            $es_grupal = intval($_POST['es_grupal'] ?? 0);

            if ($leccion_id <= 0) throw new Exception('ID de lección no válido');

            // Try updating first, or insert
            $stmt = $conn->prepare("SELECT id FROM lc_actividades WHERE leccion_id = :leccion_id");
            $stmt->execute(['leccion_id' => $leccion_id]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $stmt = $conn->prepare("UPDATE lc_actividades SET nombre = :nombre, descripcion = :descripcion, tipo = :tipo, es_grupal = :es_grupal WHERE leccion_id = :leccion_id");
                $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion, 'tipo' => $tipo, 'es_grupal' => $es_grupal, 'leccion_id' => $leccion_id]);
                echo json_encode(['success' => true, 'id' => $exists]);
            } else {
                $stmt = $conn->prepare("INSERT INTO lc_actividades (leccion_id, nombre, descripcion, tipo, es_grupal) VALUES (:leccion_id, :nombre, :descripcion, :tipo, :es_grupal)");
                $stmt->execute(['leccion_id' => $leccion_id, 'nombre' => $nombre, 'descripcion' => $descripcion, 'tipo' => $tipo, 'es_grupal' => $es_grupal]);
                echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
            }
            break;

        // --- EVALUACIONES ---
        case 'save_evaluation':
            $leccion_id = intval($_POST['leccion_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? 'Nueva Evaluación');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tiempo = intval($_POST['tiempo_limite_minutos'] ?? 0);
            $intentos = intval($_POST['intentos_permitidos'] ?? 1);
            $nota_minima = floatval($_POST['calificacion_minima'] ?? 60.00);

            if ($leccion_id <= 0) throw new Exception('ID de lección no válido');

            $stmt = $conn->prepare("SELECT id FROM lc_evaluaciones WHERE leccion_id = :leccion_id");
            $stmt->execute(['leccion_id' => $leccion_id]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $stmt = $conn->prepare("UPDATE lc_evaluaciones SET nombre = :nombre, descripcion = :descripcion, tiempo_limite_minutos = :tiempo, intentos_permitidos = :intentos, calificacion_minima = :nota_minima WHERE leccion_id = :leccion_id");
                $stmt->execute([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'tiempo' => $tiempo,
                    'intentos' => $intentos,
                    'nota_minima' => $nota_minima,
                    'leccion_id' => $leccion_id
                ]);
                echo json_encode(['success' => true, 'id' => $exists]);
            } else {
                $stmt = $conn->prepare("INSERT INTO lc_evaluaciones (leccion_id, nombre, descripcion, tiempo_limite_minutos, intentos_permitidos, calificacion_minima) VALUES (:leccion_id, :nombre, :descripcion, :tiempo, :intentos, :nota_minima)");
                $stmt->execute([
                    'leccion_id' => $leccion_id,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'tiempo' => $tiempo,
                    'intentos' => $intentos,
                    'nota_minima' => $nota_minima
                ]);
                echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
            }
            break;

        default:
            throw new Exception('Acción no permitida');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
