<?php
require_once '../config/db.php';
require_once '../models/Inscripcion.php';
require_once '../models/Usuario.php';
require_once '../models/Curso.php';

session_start();

// Verificar que el usuario sea administrador
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit;
}

$usuarioModel = new Usuario($conn);
$inscripcionModel = new Inscripcion($conn);
$cursoModel = new Curso($conn);

// Verificar permisos
if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'descargar_excel')) {
    header("Location: index.php?tab=inscripciones&error=" . urlencode("No tienes permiso para descargar reportes."));
    exit;
}

$curso_id_param = isset($_GET['curso_id']) ? $_GET['curso_id'] : 'all';
$curso_id = null;
$course = null;

if ($curso_id_param !== 'all') {
    $curso_id = intval($curso_id_param);
    $course = $cursoModel->getById($curso_id);
    if (!$course) {
        $curso_id = null;
    }
}

// Configurar encabezados CSV
header('Content-Type: text/csv; charset=utf-8');
if ($course) {
    // Sanitizar el título del curso para el nombre de archivo
    $safe_title = preg_replace('/[^a-zA-Z0-9_]/', '_', $course['titulo']);
    header('Content-Disposition: attachment; filename="inscritos_curso_' . $safe_title . '_' . date('Y-m-d') . '.csv"');
} else {
    header('Content-Disposition: attachment; filename="reporte_todos_inscritos_' . date('Y-m-d') . '.csv"');
}

// Crear un puntero de archivo conectado al flujo de salida
$output = fopen('php://output', 'w');

// Emitir el BOM UTF-8 para que Excel lo abra con la codificación correcta
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

try {
    if ($course) {
        $course = $cursoModel->getById($curso_id);

        // Construir el encabezado dinámico según los campos solicitados en el curso
        $campos_arr = explode(',', $course['campos_requeridos']);
        $csv_headers = array('ID Inscripción', 'Nombre del Alumno', 'Correo Electrónico');
        foreach ($campos_arr as $col) {
            if ($col == 'telefono') $csv_headers[] = 'Teléfono';
            if ($col == 'edad') $csv_headers[] = 'Edad';
            if ($col == 'empresa') $csv_headers[] = 'Empresa / Institución';
        }
        $csv_headers[] = 'Curso Registrado';
        $csv_headers[] = 'Campos Solicitados';
        $csv_headers[] = 'Fecha de Registro';

        fputcsv($output, $csv_headers, ';');

        $registros = $inscripcionModel->getAll($curso_id);

        foreach ($registros as $row) {
            $csv_data_row = array(
                $row['inscripcion_id'],
                $row['usuario_nombre'],
                $row['usuario_email']
            );

            foreach ($campos_arr as $col) {
                if ($col == 'telefono') $csv_data_row[] = $row['usuario_telefono'];
                if ($col == 'edad') $csv_data_row[] = $row['usuario_edad'];
                if ($col == 'empresa') $csv_data_row[] = $row['usuario_empresa'];
            }

            $csv_data_row[] = $course['titulo'];
            $csv_data_row[] = $course['campos_requeridos'];
            $csv_data_row[] = $row['fecha_registro'];
            fputcsv($output, $csv_data_row, ';');
        }
    } else {
        fputcsv($output, array('ID Inscripción', 'Nombre del Alumno', 'Correo Electrónico', 'Curso Registrado', 'Teléfono', 'Edad', 'Empresa / Institución', 'Fecha de Registro'), ';');

        $registros = $inscripcionModel->getAll();

        foreach ($registros as $row) {
            fputcsv($output, array(
                $row['inscripcion_id'],
                $row['usuario_nombre'],
                $row['usuario_email'],
                $row['curso_titulo'],
                !empty($row['usuario_telefono']) ? $row['usuario_telefono'] : '-',
                !empty($row['usuario_edad']) ? $row['usuario_edad'] : '-',
                !empty($row['usuario_empresa']) ? $row['usuario_empresa'] : '-',
                $row['fecha_registro']
            ), ';');
        }
    }
} catch (PDOException $e) {
    fputcsv($output, array('Error al consultar la base de datos', $e->getMessage()), ';');
}

fclose($output);
exit;
?>
