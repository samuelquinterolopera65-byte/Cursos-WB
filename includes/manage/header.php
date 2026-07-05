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
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-2" style="border-bottom: 2px solid #e4e9f0;">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="index.php">
                <?php if (!empty($logoPath) && file_exists(__DIR__ . '/../../' . $logoPath)): ?>
                    <img src="../<?php echo htmlspecialchars($logoPath); ?>" alt="Logo" class="me-2" style="height: 44px; max-width: 180px; object-fit: contain;">
                <?php else: ?>
                    <i class="bi bi-shield-lock-fill text-primary me-2"></i>Cursos-WB
                <?php endif; ?>
                <span class="badge bg-primary fs-6 ms-2 align-middle">Admin</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"><i class="bi bi-globe me-1"></i>Ver Sitio Público</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="me-3 d-none d-md-block">
                        <small class="text-muted">Conectado como:</small>
                        <strong class="d-block text-dark"><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                    </div>
                    <a href="../logout.php" class="btn btn-outline-primary btn-sm px-3 rounded-pill"><i class="bi bi-box-arrow-right me-1"></i>Salir</a>
                </div>
            </div>
        </div>
    </nav>
