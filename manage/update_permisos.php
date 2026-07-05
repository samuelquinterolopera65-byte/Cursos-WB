<?php
/**
 * Endpoint AJAX: Actualizar permisos de un usuario específico
 * Método: POST
 * Parámetros: user_id (int), permisos[] (array de strings)
 */
require_once '../config/db.php';
require_once '../models/Usuario.php';

session_start();

// Responder siempre en JSON
header('Content-Type: application/json');

// Verificar que sea administrador
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

$usuarioModel = new Usuario($conn);

// Solo admitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

// Verificar permiso de gestión
if (!$usuarioModel->hasPermission($_SESSION['user_id'], 'gestionar_servicios')) {
    echo json_encode(['ok' => false, 'msg' => 'Sin permiso para gestionar roles']);
    exit;
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
if ($user_id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID de usuario inválido']);
    exit;
}

// No permitir modificar los propios permisos del admin
if ($user_id === intval($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'No puedes modificar tus propios permisos']);
    exit;
}

// Obtener y limpiar la lista de permisos permitidos
$allowed_perms = [
    'crear_cursos', 'editar_cursos', 'eliminar_cursos',
    'gestionar_usuarios', 'gestionar_servicios',
    'descargar_excel', 'gestionar_ajustes'
];

$permisos_arr = isset($_POST['permisos']) && is_array($_POST['permisos'])
    ? array_filter($_POST['permisos'], fn($p) => in_array($p, $allowed_perms))
    : [];

$permisos_str = implode(',', $permisos_arr);

try {
    // Actualizar solo el campo permisos sin tocar contraseña ni otros campos
    $stmt = $conn->prepare("UPDATE usuarios SET permisos = :permisos WHERE id = :id");
    $stmt->execute(['permisos' => $permisos_str, 'id' => $user_id]);
    echo json_encode(['ok' => true, 'msg' => 'Permisos actualizados correctamente']);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error de base de datos: ' . $e->getMessage()]);
}
