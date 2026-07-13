<?php
// Inicializar configuraciones dentro del encabezado administrativo
require_once __DIR__ . '/../../models/Ajustes.php';
$ajustesModel = new Ajustes($conn);
$logoPath = $ajustesModel->get('logo');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title><?php echo isset($page_title) ? $page_title : 'Panel de Administración - Cursos-WB'; ?></title>
    <!-- Iconos de Bootstrap (v1.10.5 para soporte completo) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- CSS del tema principal (incluye Bootstrap) -->
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/custom.css" rel="stylesheet" />
</head>
<body class="bg-light">

    <!-- Barra de navegación superior -->
    <?php if (isset($is_wizard) && $is_wizard === true): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top py-2.5">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <span class="navbar-brand fw-bold d-flex align-items-center text-white mb-0">
                <i class="bi bi-journal-bookmark-fill text-primary me-2 fs-4"></i>
                <span>Northstar<span class="text-primary fw-semibold">LMS</span> <span class="text-white-50 fs-7 fw-normal ms-2">| Asistente de Creación</span></span>
            </span>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?tab=cursos" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5" onclick="return confirm('¿Seguro que deseas salir del asistente? Se perderán los cambios no guardados.');">
                    <i class="bi bi-x-circle me-1"></i>Salir del asistente
                </a>
            </div>
        </div>
    </nav>
    <?php else: ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-2">
        <div class="container px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center text-primary" href="index.php">
                <i class="bi bi-journal-bookmark-fill me-2 fs-4"></i>
                <span>Northstar<span class="text-dark fw-semibold">LMS</span></span>
                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fs-7 ms-2 align-middle px-2.5 py-1">Panel de Control</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="../index.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5"><i class="bi bi-globe2 me-1"></i>Ver sitio público</a>
                <a href="../logout.php" class="btn btn-danger btn-sm px-3 rounded-pill text-white fw-medium shadow-sm"><i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</a>
            </div>
        </div>
    </nav>
    <?php endif; ?>
